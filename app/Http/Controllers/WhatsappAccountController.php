<?php

namespace App\Http\Controllers;

use App\Models\WhatsappAccount;
use App\Jobs\SyncTemplates;
use App\Services\WhatsappApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WhatsappAccountController extends Controller
{
    public function __construct(
        protected WhatsappApiService $whatsappApi
    ) {}

    public function index()
    {
        $accounts = auth()->user()->whatsappAccounts()
            ->with('business')
            ->withCount('conversations')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('whatsapp.accounts.index', compact('accounts'));
    }

    public function create()
    {
        $user = auth()->user();

        // Check plan limit
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

        // Check limit
        if (!$user->canUseFeature('whatsapp_numbers')) {
            return back()->with('error', 'WhatsApp number limit reached.');
        }

        $account = WhatsappAccount::create([
            'user_id' => $user->id,
            'business_id' => $user->business->id,
            'phone_number' => $validated['phone_number'],
            'phone_number_id' => $validated['phone_number_id'],
            'waba_id' => $validated['waba_id'],
            'business_id_meta' => $validated['business_id_meta'],
            'access_token' => $validated['access_token'],
            'display_name' => $validated['display_name'],
            'status' => 'pending',
            'webhook_secret' => Str::random(32),
        ]);

        // Verify connection
        $phoneInfo = $this->whatsappApi->getPhoneNumberInfo($account);

        if ($phoneInfo['success']) {
            $account->update([
                'status' => 'connected',
                'display_name' => $phoneInfo['data']['verified_name'] ?? $account->display_name,
                'quality_rating' => $phoneInfo['data']['quality_rating'] ?? 'UNKNOWN',
                'connected_at' => now(),
            ]);

            // Register webhook subscription
            $this->whatsappApi->registerWebhook(
                $account,
                route('webhook.whatsapp')
            );

            // Sync templates
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
        $this->authorize('view', $account);

        // Get phone info
        $phoneInfo = $this->whatsappApi->getPhoneNumberInfo($account);
        $businessProfile = $this->whatsappApi->getBusinessProfile($account);

        // Stats
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
        $this->authorize('update', $account);

        SyncTemplates::dispatch($account)->onQueue('default');

        return back()->with('success', 'Template sync started. Templates will be updated shortly.');
    }

    public function testMessage(Request $request, WhatsappAccount $account)
    {
        $this->authorize('update', $account);

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

        return back()->with('error', "Failed: {$result['error_message']}");
    }

    public function disconnect(WhatsappAccount $account)
    {
        $this->authorize('delete', $account);

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
        $this->authorize('delete', $account);

        $account->delete();

        return redirect()->route('whatsapp.accounts.index')
            ->with('success', 'WhatsApp account removed.');
    }
}