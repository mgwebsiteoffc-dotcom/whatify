<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Conversation;
use App\Models\MessageTemplate;
use App\Models\WhatsappAccount;
use App\Services\MessageService;
use Illuminate\Http\Request;

class QuickMessageController extends Controller
{
    public function __construct(
        protected MessageService $messageService
    ) {}

    /**
     * Send quick text message to a contact
     */
    public function sendText(Request $request)
    {
        $validated = $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'whatsapp_account_id' => 'required|exists:whatsapp_accounts,id',
            'message' => 'required|string|max:4096',
        ]);

        $user = auth()->user();
        $owner = $user->getBusinessOwner() ?? $user;
        $contact = Contact::where('id', $validated['contact_id'])
            ->where('user_id', $owner->id)->firstOrFail();
        $account = WhatsappAccount::where('id', $validated['whatsapp_account_id'])
            ->where('user_id', $owner->id)->firstOrFail();

        $message = $this->messageService->sendText(
            $owner, $account, $contact,
            $validated['message'],
            $user->name
        );

        if (!$message) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to send. Check wallet balance.'], 422);
            }
            return back()->with('error', 'Failed to send message. Check wallet balance.');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message->fresh(),
            ]);
        }

        return back()->with('success', 'Message sent!');
    }

    /**
     * Send template message to a contact
     */
    public function sendTemplate(Request $request)
    {
        $validated = $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'whatsapp_account_id' => 'required|exists:whatsapp_accounts,id',
            'template_id' => 'required|exists:message_templates,id',
            'body_params' => 'nullable|array',
            'header_params' => 'nullable|array',
        ]);

        $user = auth()->user();
        $owner = $user->getBusinessOwner() ?? $user;

        $contact = Contact::where('id', $validated['contact_id'])
            ->where('user_id', $owner->id)->firstOrFail();
        $account = WhatsappAccount::where('id', $validated['whatsapp_account_id'])
            ->where('user_id', $owner->id)->firstOrFail();
        $template = MessageTemplate::where('id', $validated['template_id'])
            ->where('user_id', $owner->id)
            ->where('status', 'approved')
            ->firstOrFail();

        $message = $this->messageService->sendTemplate(
            $owner, $account, $contact, $template,
            $validated['body_params'] ?? [],
            $validated['header_params'] ?? []
        );

        if (!$message) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to send template message.'], 422);
            }
            return back()->with('error', 'Failed to send. Check wallet balance.');
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message->fresh()]);
        }

        return back()->with('success', 'Template message sent!');
    }

    /**
     * Send media message
     */
    public function sendMedia(Request $request)
    {
        $validated = $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'whatsapp_account_id' => 'required|exists:whatsapp_accounts,id',
            'media_type' => 'required|in:image,video,document,audio',
            'media_file' => 'required|file|max:16384',
            'caption' => 'nullable|string|max:1024',
        ]);

        $user = auth()->user();
        $owner = $user->getBusinessOwner() ?? $user;

        $contact = Contact::where('id', $validated['contact_id'])
            ->where('user_id', $owner->id)->firstOrFail();
        $account = WhatsappAccount::where('id', $validated['whatsapp_account_id'])
            ->where('user_id', $owner->id)->firstOrFail();

        // Upload file
        $path = $request->file('media_file')->store('whatsapp-media', 'public');
        $mediaUrl = asset('storage/' . $path);

        $message = $this->messageService->sendMedia(
            $owner, $account, $contact,
            $validated['media_type'],
            $mediaUrl,
            $validated['caption'] ?? null,
            $request->file('media_file')->getClientOriginalName()
        );

        if (!$message) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to send media.'], 422);
            }
            return back()->with('error', 'Failed to send media.');
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message->fresh()]);
        }

        return back()->with('success', 'Media sent!');
    }
}