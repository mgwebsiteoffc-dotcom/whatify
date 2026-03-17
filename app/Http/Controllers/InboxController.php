<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Conversation;
use App\Models\InternalNote;
use App\Models\Message;
use App\Models\MessageTemplate;
use App\Models\WhatsappAccount;
use App\Services\MessageService;
use Illuminate\Http\Request;

class InboxController extends Controller
{
    public function __construct(protected MessageService $messageService) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $owner = $user->getBusinessOwner() ?? $user;

        $conversationsQuery = Conversation::where('user_id', $owner->id)
            ->with(['contact:id,name,phone,country_code,email,status', 'assignedAgent:id,name'])
            ->when($request->status && $request->status !== 'all', fn($q) => $q->where('status', $request->status))
            ->when($request->assigned === 'me', fn($q) => $q->where('assigned_agent_id', $user->id))
            ->when($request->assigned === 'unassigned', fn($q) => $q->whereNull('assigned_agent_id'))
            ->when($request->search, function ($q, $search) {
                $q->whereHas('contact', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                       ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderBy('last_message_at', 'desc');

        $conversations = $conversationsQuery->paginate(30);

        $selectedConversation = null;
        $messages = collect();
        $notes = collect();

        if ($request->conversation_id) {
            $selectedConversation = Conversation::where('id', $request->conversation_id)
                ->where('user_id', $owner->id)
                ->with(['contact.tags', 'assignedAgent:id,name', 'whatsappAccount:id,phone_number,display_name'])
                ->first();

            if ($selectedConversation) {
                $messages = Message::where('conversation_id', $selectedConversation->id)
                    ->orderBy('created_at', 'asc')
                    ->limit(100)
                    ->get();

                $notes = InternalNote::where('conversation_id', $selectedConversation->id)
                    ->with('user:id,name')
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        }

        $agents = [];
        if ($owner->team) {
            $agents = $owner->team->members()
                ->with('member:id,name')
                ->where('status', 'active')
                ->get()
                ->map(fn($m) => $m->member);
        }

        $accounts = $owner->whatsappAccounts()->where('status', 'connected')->get();
        $templates = MessageTemplate::where('user_id', $owner->id)->where('status', 'approved')->get();

        $statusCounts = [
            'all' => Conversation::where('user_id', $owner->id)->count(),
            'open' => Conversation::where('user_id', $owner->id)->where('status', 'open')->count(),
            'pending' => Conversation::where('user_id', $owner->id)->where('status', 'pending')->count(),
            'resolved' => Conversation::where('user_id', $owner->id)->where('status', 'resolved')->count(),
        ];

        return view('inbox.index', compact(
            'conversations', 'selectedConversation', 'messages', 'notes',
            'agents', 'accounts', 'templates', 'statusCounts'
        ));
    }

    public function reply(Request $request, Conversation $conversation)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($conversation->user_id !== $owner->id, 403);

        $validated = $request->validate([
            'message' => 'required|string|max:4096',
        ]);

        $user = auth()->user();

        $message = $this->messageService->sendText(
            $owner,
            $conversation->whatsappAccount,
            $conversation->contact,
            $validated['message'],
            $user->name
        );

        if (!$message) {
            return back()->with('error', 'Failed to send. Check wallet balance.');
        }

        return redirect()->route('inbox.index', [
            'conversation_id' => $conversation->id,
            'status' => request('status'),
        ]);
    }

    public function sendTemplateMessage(Request $request, Conversation $conversation)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($conversation->user_id !== $owner->id, 403);

        $validated = $request->validate([
            'template_id' => 'required|exists:message_templates,id',
            'body_params' => 'nullable|array',
        ]);

        $template = MessageTemplate::where('id', $validated['template_id'])
            ->where('user_id', $owner->id)
            ->where('status', 'approved')
            ->firstOrFail();

        $message = $this->messageService->sendTemplate(
            $owner,
            $conversation->whatsappAccount,
            $conversation->contact,
            $template,
            $validated['body_params'] ?? []
        );

        if (!$message) {
            return back()->with('error', 'Failed to send template.');
        }

        return redirect()->route('inbox.index', ['conversation_id' => $conversation->id]);
    }

    public function sendMediaMessage(Request $request, Conversation $conversation)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($conversation->user_id !== $owner->id, 403);

        $validated = $request->validate([
            'media_file' => 'required|file|max:16384',
            'caption' => 'nullable|string|max:1024',
        ]);

        $file = $request->file('media_file');
        $extension = strtolower($file->getClientOriginalExtension());

        $mediaType = match (true) {
            in_array($extension, ['jpg', 'jpeg', 'png', 'webp']) => 'image',
            in_array($extension, ['mp4', '3gp']) => 'video',
            in_array($extension, ['mp3', 'aac', 'ogg', 'm4a']) => 'audio',
            default => 'document',
        };

        $path = $file->store('whatsapp-media', 'public');
        $mediaUrl = asset('storage/' . $path);

        $message = $this->messageService->sendMedia(
            $owner, $conversation->whatsappAccount, $conversation->contact,
            $mediaType, $mediaUrl,
            $validated['caption'] ?? null,
            $file->getClientOriginalName()
        );

        if (!$message) {
            return back()->with('error', 'Failed to send media.');
        }

        return redirect()->route('inbox.index', ['conversation_id' => $conversation->id]);
    }

    public function assign(Request $request, Conversation $conversation)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($conversation->user_id !== $owner->id, 403);

        $validated = $request->validate([
            'agent_id' => 'nullable|exists:users,id',
        ]);

        $conversation->update(['assigned_agent_id' => $validated['agent_id']]);

        if ($validated['agent_id']) {
            app(\App\Services\NotificationService::class)->send(
                \App\Models\User::find($validated['agent_id']),
                'Conversation assigned to you',
                "Contact: {$conversation->contact->name} ({$conversation->contact->phone})",
                'message'
            );
        }

        return back()->with('success', 'Conversation assigned.');
    }

    public function updateStatus(Request $request, Conversation $conversation)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($conversation->user_id !== $owner->id, 403);

        $validated = $request->validate([
            'status' => 'required|in:open,pending,resolved,closed',
        ]);

        $conversation->update(['status' => $validated['status']]);

        return back()->with('success', 'Status updated.');
    }

    public function toggleBot(Conversation $conversation)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($conversation->user_id !== $owner->id, 403);

        $conversation->update([
            'is_bot_active' => !$conversation->is_bot_active,
            'bot_paused_until' => null,
        ]);

        $status = $conversation->is_bot_active ? 'enabled' : 'disabled';
        return back()->with('success', "Bot {$status} for this conversation.");
    }

    public function addNote(Request $request, Conversation $conversation)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($conversation->user_id !== $owner->id, 403);

        $validated = $request->validate([
            'note' => 'required|string|max:2000',
        ]);

        InternalNote::create([
            'conversation_id' => $conversation->id,
            'user_id' => auth()->id(),
            'note' => $validated['note'],
        ]);

        return back()->with('success', 'Note added.');
    }
}