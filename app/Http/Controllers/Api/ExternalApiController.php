<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageTemplate;
use App\Models\WhatsappAccount;
use App\Services\MessageService;
use Illuminate\Http\Request;

class ExternalApiController extends Controller
{
    public function __construct(protected MessageService $messageService) {}

    public function sendMessage(Request $request)
    {
        $this->checkPermission($request, 'send_message');

        $validated = $request->validate([
            'phone' => 'required|string|min:10',
            'message' => 'required|string|max:4096',
            'whatsapp_account_id' => 'nullable|integer',
        ]);

        $user = auth()->user();
        $owner = $user->getBusinessOwner() ?? $user;

        $account = $validated['whatsapp_account_id']
            ? WhatsappAccount::where('id', $validated['whatsapp_account_id'])->where('user_id', $owner->id)->first()
            : $owner->whatsappAccounts()->where('status', 'connected')->first();

        if (!$account) {
            return response()->json(['success' => false, 'error' => 'No connected WhatsApp account found.'], 422);
        }

        $contact = $this->findOrCreateContact($owner, $validated['phone']);

        $message = $this->messageService->sendText($owner, $account, $contact, $validated['message'], 'API');

        if (!$message) {
            return response()->json(['success' => false, 'error' => 'Failed. Check wallet balance.'], 422);
        }

        return response()->json([
            'success' => true,
            'message_id' => $message->id,
            'status' => $message->status,
            'contact_id' => $contact->id,
        ]);
    }

    public function sendTemplate(Request $request)
    {
        $this->checkPermission($request, 'send_template');

        $validated = $request->validate([
            'phone' => 'required|string|min:10',
            'template_name' => 'required|string',
            'body_params' => 'nullable|array',
            'header_params' => 'nullable|array',
            'whatsapp_account_id' => 'nullable|integer',
        ]);

        $user = auth()->user();
        $owner = $user->getBusinessOwner() ?? $user;

        $account = $validated['whatsapp_account_id']
            ? WhatsappAccount::where('id', $validated['whatsapp_account_id'])->where('user_id', $owner->id)->first()
            : $owner->whatsappAccounts()->where('status', 'connected')->first();

        if (!$account) {
            return response()->json(['success' => false, 'error' => 'No connected WhatsApp account.'], 422);
        }

        $template = MessageTemplate::where('user_id', $owner->id)
            ->where('name', $validated['template_name'])
            ->where('status', 'approved')
            ->first();

        if (!$template) {
            return response()->json(['success' => false, 'error' => 'Template not found or not approved.'], 404);
        }

        $contact = $this->findOrCreateContact($owner, $validated['phone']);

        $message = $this->messageService->sendTemplate(
            $owner, $account, $contact, $template,
            $validated['body_params'] ?? [],
            $validated['header_params'] ?? []
        );

        if (!$message) {
            return response()->json(['success' => false, 'error' => 'Failed. Check wallet balance.'], 422);
        }

        return response()->json([
            'success' => true,
            'message_id' => $message->id,
            'status' => $message->status,
            'contact_id' => $contact->id,
        ]);
    }

    public function contacts(Request $request)
    {
        $this->checkPermission($request, 'read_contacts');

        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();

        $contacts = Contact::where('user_id', $owner->id)
            ->with('tags:id,name,color')
            ->when($request->phone, fn($q, $p) => $q->where('phone', 'like', "%{$p}%"))
            ->when($request->name, fn($q, $n) => $q->where('name', 'like', "%{$n}%"))
            ->when($request->tag, fn($q, $t) => $q->whereHas('tags', fn($q2) => $q2->where('name', $t)))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 50);

        return response()->json($contacts);
    }

    public function showContact(Request $request, Contact $contact)
    {
        $this->checkPermission($request, 'read_contacts');

        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        if ($contact->user_id !== $owner->id) {
            return response()->json(['error' => 'Contact not found.'], 404);
        }

        $contact->load('tags:id,name,color');

        return response()->json(['contact' => $contact]);
    }

    public function createContact(Request $request)
    {
        $this->checkPermission($request, 'write_contacts');

        $validated = $request->validate([
            'phone' => 'required|string|min:10',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'country_code' => 'nullable|string|max:5',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'custom_attributes' => 'nullable|array',
        ]);

        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();

        $phone = preg_replace('/[^0-9]/', '', $validated['phone']);
        $localPhone = substr($phone, -10);
        $countryCode = strlen($phone) > 10 ? substr($phone, 0, strlen($phone) - 10) : ($validated['country_code'] ?? '91');

        $contact = Contact::firstOrCreate(
            ['user_id' => $owner->id, 'phone' => $localPhone],
            [
                'country_code' => $countryCode,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'source' => 'api',
                'status' => 'active',
                'custom_attributes' => $validated['custom_attributes'],
                'opted_in_at' => now(),
            ]
        );

        if (!$contact->wasRecentlyCreated && $validated['name']) {
            $contact->update(array_filter([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'custom_attributes' => $validated['custom_attributes']
                    ? array_merge($contact->custom_attributes ?? [], $validated['custom_attributes'])
                    : $contact->custom_attributes,
            ]));
        }

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
            'contact' => $contact->fresh()->load('tags:id,name,color'),
            'created' => $contact->wasRecentlyCreated,
        ], $contact->wasRecentlyCreated ? 201 : 200);
    }

    public function updateContact(Request $request, Contact $contact)
    {
        $this->checkPermission($request, 'write_contacts');

        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        if ($contact->user_id !== $owner->id) {
            return response()->json(['error' => 'Contact not found.'], 404);
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'status' => 'nullable|in:active,inactive,blocked,opted_out',
            'tags' => 'nullable|array',
            'custom_attributes' => 'nullable|array',
        ]);

        $contact->update(array_filter([
            'name' => $validated['name'] ?? $contact->name,
            'email' => $validated['email'] ?? $contact->email,
            'status' => $validated['status'] ?? $contact->status,
            'custom_attributes' => isset($validated['custom_attributes'])
                ? array_merge($contact->custom_attributes ?? [], $validated['custom_attributes'])
                : $contact->custom_attributes,
        ]));

        if (isset($validated['tags'])) {
            $tagIds = [];
            foreach ($validated['tags'] as $tagName) {
                $tag = \App\Models\Tag::firstOrCreate(
                    ['user_id' => $owner->id, 'name' => $tagName],
                    ['color' => '#3B82F6']
                );
                $tagIds[] = $tag->id;
            }
            $contact->tags()->sync($tagIds);
        }

        return response()->json([
            'success' => true,
            'contact' => $contact->fresh()->load('tags:id,name,color'),
        ]);
    }

    public function conversations(Request $request)
    {
        $this->checkPermission($request, 'read_conversations');

        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();

        $conversations = Conversation::where('user_id', $owner->id)
            ->with(['contact:id,name,phone,country_code', 'assignedAgent:id,name'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->orderBy('last_message_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json($conversations);
    }

    public function conversationMessages(Request $request, Conversation $conversation)
    {
        $this->checkPermission($request, 'read_conversations');

        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        if ($conversation->user_id !== $owner->id) {
            return response()->json(['error' => 'Conversation not found.'], 404);
        }

        $messages = Message::where('conversation_id', $conversation->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 50);

        return response()->json($messages);
    }

    public function campaigns(Request $request)
    {
        $this->checkPermission($request, 'read_campaigns');

        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();

        $campaigns = Campaign::where('user_id', $owner->id)
            ->with('template:id,name,category')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json($campaigns);
    }

    public function campaignShow(Request $request, Campaign $campaign)
    {
        $this->checkPermission($request, 'read_campaigns');

        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        if ($campaign->user_id !== $owner->id) {
            return response()->json(['error' => 'Campaign not found.'], 404);
        }

        return response()->json([
            'campaign' => $campaign->load('template'),
            'delivery_rate' => $campaign->getDeliveryRate(),
            'read_rate' => $campaign->getReadRate(),
        ]);
    }

    public function templates(Request $request)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();

        $templates = MessageTemplate::where('user_id', $owner->id)
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->category, fn($q, $c) => $q->where('category', $c))
            ->orderBy('name')
            ->get(['id', 'name', 'category', 'language', 'status', 'body', 'header', 'footer', 'buttons']);

        return response()->json(['templates' => $templates]);
    }

    public function walletBalance(Request $request)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();

        return response()->json([
            'balance' => $owner->wallet?->balance ?? 0,
            'currency' => $owner->wallet?->currency ?? 'INR',
            'total_recharged' => $owner->wallet?->total_recharged ?? 0,
            'total_spent' => $owner->wallet?->total_spent ?? 0,
        ]);
    }

    protected function checkPermission(Request $request, string $permission): void
    {
        $permissions = $request->get('api_key_permissions', []);

        if (!empty($permissions) && !in_array($permission, $permissions)) {
            abort(response()->json([
                'error' => 'Permission denied.',
                'required_permission' => $permission,
                'your_permissions' => $permissions,
            ], 403));
        }
    }

    protected function findOrCreateContact($owner, string $phone): Contact
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        $localPhone = substr($phone, -10);
        $countryCode = strlen($phone) > 10 ? substr($phone, 0, strlen($phone) - 10) : '91';

        return Contact::firstOrCreate(
            ['user_id' => $owner->id, 'phone' => $localPhone],
            [
                'country_code' => $countryCode,
                'source' => 'api',
                'status' => 'active',
                'opted_in_at' => now(),
            ]
        );
    }
}