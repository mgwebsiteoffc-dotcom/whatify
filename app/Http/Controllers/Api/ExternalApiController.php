<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\MessageTemplate;
use App\Models\WhatsappAccount;
use App\Services\MessageService;
use Illuminate\Http\Request;

class ExternalApiController extends Controller
{
    public function __construct(protected MessageService $messageService) {}

    /**
     * POST /api/v1/external/send-message
     * Send a text message via API
     */
    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string|min:10',
            'message' => 'required|string|max:4096',
            'whatsapp_account_id' => 'nullable|integer',
        ]);

        $user = $request->user();
        $owner = $user->getBusinessOwner() ?? $user;

        $account = $validated['whatsapp_account_id']
            ? WhatsappAccount::where('id', $validated['whatsapp_account_id'])->where('user_id', $owner->id)->firstOrFail()
            : $owner->whatsappAccounts()->where('status', 'connected')->firstOrFail();

        $phone = preg_replace('/[^0-9]/', '', $validated['phone']);
        $localPhone = substr($phone, -10);
        $countryCode = strlen($phone) > 10 ? substr($phone, 0, strlen($phone) - 10) : '91';

        $contact = Contact::firstOrCreate(
            ['user_id' => $owner->id, 'phone' => $localPhone],
            [
                'country_code' => $countryCode,
                'source' => 'api',
                'status' => 'active',
                'opted_in_at' => now(),
            ]
        );

        $message = $this->messageService->sendText($owner, $account, $contact, $validated['message'], 'API');

        if (!$message) {
            return response()->json(['success' => false, 'error' => 'Failed. Check wallet balance.'], 422);
        }

        return response()->json([
            'success' => true,
            'message_id' => $message->id,
            'status' => $message->status,
        ]);
    }

    /**
     * POST /api/v1/external/send-template
     */
    public function sendTemplate(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string|min:10',
            'template_name' => 'required|string',
            'body_params' => 'nullable|array',
            'whatsapp_account_id' => 'nullable|integer',
        ]);

        $user = $request->user();
        $owner = $user->getBusinessOwner() ?? $user;

        $account = $validated['whatsapp_account_id']
            ? WhatsappAccount::where('id', $validated['whatsapp_account_id'])->where('user_id', $owner->id)->firstOrFail()
            : $owner->whatsappAccounts()->where('status', 'connected')->firstOrFail();

        $template = MessageTemplate::where('user_id', $owner->id)
            ->where('name', $validated['template_name'])
            ->where('status', 'approved')
            ->firstOrFail();

        $phone = preg_replace('/[^0-9]/', '', $validated['phone']);
        $localPhone = substr($phone, -10);
        $countryCode = strlen($phone) > 10 ? substr($phone, 0, strlen($phone) - 10) : '91';

        $contact = Contact::firstOrCreate(
            ['user_id' => $owner->id, 'phone' => $localPhone],
            ['country_code' => $countryCode, 'source' => 'api', 'status' => 'active', 'opted_in_at' => now()]
        );

        $message = $this->messageService->sendTemplate(
            $owner, $account, $contact, $template,
            $validated['body_params'] ?? []
        );

        if (!$message) {
            return response()->json(['success' => false, 'error' => 'Failed. Check wallet balance.'], 422);
        }

        return response()->json([
            'success' => true,
            'message_id' => $message->id,
            'status' => $message->status,
        ]);
    }

    /**
     * GET /api/v1/external/contacts
     */
    public function contacts(Request $request)
    {
        $owner = $request->user()->getBusinessOwner() ?? $request->user();

        $contacts = Contact::where('user_id', $owner->id)
            ->when($request->phone, fn($q, $p) => $q->where('phone', 'like', "%{$p}%"))
            ->when($request->tag, fn($q, $t) => $q->whereHas('tags', fn($q2) => $q2->where('name', $t)))
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 50);

        return response()->json($contacts);
    }

    /**
     * POST /api/v1/external/contacts
     */
    public function createContact(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string|min:10',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'country_code' => 'nullable|string|max:5',
            'tags' => 'nullable|array',
            'custom_attributes' => 'nullable|array',
        ]);

        $owner = $request->user()->getBusinessOwner() ?? $request->user();

        $phone = preg_replace('/[^0-9]/', '', $validated['phone']);
        $localPhone = substr($phone, -10);

        $contact = Contact::firstOrCreate(
            ['user_id' => $owner->id, 'phone' => $localPhone],
            [
                'country_code' => $validated['country_code'] ?? '91',
                'name' => $validated['name'],
                'email' => $validated['email'],
                'source' => 'api',
                'status' => 'active',
                'custom_attributes' => $validated['custom_attributes'],
                'opted_in_at' => now(),
            ]
        );

        // Sync tags by name
        if (!empty($validated['tags'])) {
            $tagIds = [];
            foreach ($validated['tags'] as $tagName) {
                $tag = \App\Models\Tag::firstOrCreate(
                    ['user_id' => $owner->id, 'name' => $tagName],
                    ['color' => '#3B82F6']
                );
                $tagIds[] = $tag->id;
            }
            $contact->tags()->syncWithoutDetaching($tagIds);
        }

        return response()->json([
            'success' => true,
            'contact' => $contact->fresh()->load('tags'),
            'created' => $contact->wasRecentlyCreated,
        ], $contact->wasRecentlyCreated ? 201 : 200);
    }
}