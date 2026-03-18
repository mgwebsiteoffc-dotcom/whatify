<?php

namespace App\Http\Controllers;

use App\Models\WhatsappAccount;
use App\Jobs\SyncTemplates;
use App\Services\WhatsappApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class WhatsappAccountController extends Controller
{
    public function __construct(
        protected WhatsappApiService $whatsappApi
    ) {}

    public function index()
    {
        $user = auth()->user();
        $owner = $user->getBusinessOwner() ?? $user;

        $accounts = WhatsappAccount::where('user_id', $owner->id)
            ->with('business')
            ->withCount('conversations')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('whatsapp.accounts.index', compact('accounts'));
    }

    public function create()
    {
        $user = auth()->user();

        if (!$user->canUseFeature('whatsapp_numbers')) {
            return back()->with('error', 'WhatsApp number limit reached. Please upgrade your plan.');
        }

        return view('whatsapp.accounts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => ['required', 'string', 'max:20'],
            'phone_number_id' => ['required', 'string', 'max:100'],
            'waba_id' => ['required', 'string', 'max:100'],
            'business_id_meta' => ['nullable', 'string', 'max:100'],
            'access_token' => ['required', 'string'],
            'display_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = auth()->user();

        if (!$user->canUseFeature('whatsapp_numbers')) {
            return back()->with('error', 'WhatsApp number limit reached.');
        }

        $business = $user->business;
        if (!$business) {
            return back()->with('error', 'Please create a business profile first.');
        }

        $account = WhatsappAccount::create([
            'user_id' => $user->id,
            'business_id' => $business->id,
            'phone_number' => $validated['phone_number'],
            'phone_number_id' => $validated['phone_number_id'],
            'waba_id' => $validated['waba_id'],
            'business_id_meta' => $validated['business_id_meta'],
            'access_token' => $validated['access_token'],
            'display_name' => $validated['display_name'],
            'status' => 'pending',
            'webhook_secret' => Str::random(32),
        ]);

        $phoneInfo = $this->whatsappApi->getPhoneNumberInfo($account);

        if ($phoneInfo['success']) {
            $account->update([
                'status' => 'connected',
                'display_name' => $phoneInfo['data']['verified_name'] ?? $account->display_name,
                'quality_rating' => $phoneInfo['data']['quality_rating'] ?? 'UNKNOWN',
                'connected_at' => now(),
            ]);

            $this->whatsappApi->registerWebhook($account, url('/api/webhook/whatsapp'));
            SyncTemplates::dispatch($account)->onQueue('default');

            \App\Services\ActivityLogger::log('whatsapp_account_connected', 'WhatsappAccount', $account->id);

            return redirect()->route('whatsapp.accounts.index')
                ->with('success', 'WhatsApp account connected successfully!');
        }

        $account->update(['status' => 'disconnected']);

        return back()->with('error', 'Failed to verify WhatsApp account. Please check your credentials.')
            ->withInput();
    }

    public function show(WhatsappAccount $account)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($account->user_id !== $owner->id, 403, 'Unauthorized');

        $phoneInfo = [];
        $businessProfile = [];

        if ($account->isConnected()) {
            $phoneInfo = $this->whatsappApi->getPhoneNumberInfo($account);
            $businessProfile = $this->whatsappApi->getBusinessProfile($account);
        }

        $stats = [
            'total_messages' => $account->messages()->count(),
            'messages_today' => $account->messages()->whereDate('created_at', today())->count(),
            'conversations' => $account->conversations()->where('status', 'open')->count(),
            'templates' => $account->templates()->count(),
            'approved_templates' => $account->templates()->where('status', 'approved')->count(),
        ];

        return view('whatsapp.accounts.show', compact('account', 'phoneInfo', 'businessProfile', 'stats'));
    }

    public function syncTemplates(WhatsappAccount $account)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($account->user_id !== $owner->id, 403, 'Unauthorized');

        SyncTemplates::dispatch($account)->onQueue('default');

        return back()->with('success', 'Template sync started. Templates will be updated shortly.');
    }

    public function testMessage(Request $request, WhatsappAccount $account)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($account->user_id !== $owner->id, 403, 'Unauthorized');

        $validated = $request->validate([
            'phone' => 'required|string|min:10',
            'message' => 'required|string|max:4096',
        ]);

        $result = $this->whatsappApi->sendTextMessage(
            $account,
            $validated['phone'],
            $validated['message']
        );

        if ($result['success']) {
            return back()->with('success', "Test message sent! WAMID: {$result['wamid']}");
        }

        return back()->with('error', "Failed: " . ($result['error_message'] ?? 'Unknown error'));
    }

    public function disconnect(WhatsappAccount $account)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($account->user_id !== $owner->id, 403, 'Unauthorized');

        $account->update([
            'status' => 'disconnected',
            'access_token' => null,
        ]);

        \App\Services\ActivityLogger::log('whatsapp_account_disconnected', 'WhatsappAccount', $account->id);

        return redirect()->route('whatsapp.accounts.index')
            ->with('success', 'WhatsApp account disconnected.');
    }

    public function destroy(WhatsappAccount $account)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($account->user_id !== $owner->id, 403, 'Unauthorized');

        $account->delete();

        return redirect()->route('whatsapp.accounts.index')
            ->with('success', 'WhatsApp account removed.');
    }

    public function embeddedSignup()
    {
        $user = auth()->user();

        if (!$user->canUseFeature('whatsapp_numbers')) {
            return back()->with('error', 'WhatsApp number limit reached. Please upgrade your plan.');
        }

        $appId = config('whatify.whatsapp.app_id');
        $configId = config('whatify.whatsapp.config_id', '');

        return view('whatsapp.accounts.embedded-signup', compact('appId', 'configId'));
    }

    public function embeddedSignupCallback(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
        ]);

        try {
            $response = Http::get('https://graph.facebook.com/v18.0/oauth/access_token', [
                'client_id' => config('whatify.whatsapp.app_id'),
                'client_secret' => config('whatify.whatsapp.app_secret'),
                'code' => $validated['code'],
            ]);

            if (!$response->successful()) {
                return redirect()->route('whatsapp.accounts.index')
                    ->with('error', 'Failed to exchange code for token.');
            }

            $accessToken = $response->json('access_token');

            return redirect()->route('whatsapp.accounts.create')
                ->with('success', 'Facebook login successful! Complete setup below.')
                ->with('access_token', $accessToken);

        } catch (\Exception $e) {
            return redirect()->route('whatsapp.accounts.index')
                ->with('error', 'Signup failed: ' . $e->getMessage());
        }
    }
}