<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\MessageService;
use Illuminate\Http\Request;

class ConversationApiController extends Controller
{
    public function __construct(protected MessageService $messageService) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $owner = $user->getBusinessOwner() ?? $user;

        $conversations = Conversation::where('user_id', $owner->id)
            ->when($user->isAgent(), function ($q) use ($user) {
                // Agents see assigned + unassigned
                $q->where(function ($q2) use ($user) {
                    $q2->where('assigned_agent_id', $user->id)
                       ->orWhereNull('assigned_agent_id');
                });
            })
            ->with(['contact', 'assignedAgent:id,name', 'latestMessage'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->orderBy('last_message_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json($conversations);
    }

    public function show(Request $request, Conversation $conversation)
    {
        $user = $request->user();
        $owner = $user->getBusinessOwner() ?? $user;

        if ($conversation->user_id !== $owner->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $conversation->load(['contact', 'assignedAgent:id,name', 'whatsappAccount:id,phone_number,display_name']);

        return response()->json($conversation);
    }

    public function messages(Request $request, Conversation $conversation)
    {
        $user = $request->user();
        $owner = $user->getBusinessOwner() ?? $user;

        if ($conversation->user_id !== $owner->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $messages = Message::where('conversation_id', $conversation->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 50);

        return response()->json($messages);
    }

    public function reply(Request $request, Conversation $conversation)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:4096',
        ]);

        $user = $request->user();
        $owner = $user->getBusinessOwner() ?? $user;

        if ($conversation->user_id !== $owner->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $message = $this->messageService->sendText(
            $owner,
            $conversation->whatsappAccount,
            $conversation->contact,
            $validated['message'],
            $user->name
        );

        if (!$message) {
            return response()->json(['error' => 'Failed to send. Check wallet balance.'], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $message->fresh(),
        ]);
    }

    public function assign(Request $request, Conversation $conversation)
    {
        $validated = $request->validate([
            'agent_id' => 'nullable|exists:users,id',
        ]);

        $user = $request->user();
        $owner = $user->getBusinessOwner() ?? $user;

        if ($conversation->user_id !== $owner->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $conversation->update(['assigned_agent_id' => $validated['agent_id']]);

        return response()->json([
            'success' => true,
            'message' => 'Conversation assigned.',
        ]);
    }

    public function updateStatus(Request $request, Conversation $conversation)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,pending,resolved,closed',
        ]);

        $user = $request->user();
        $owner = $user->getBusinessOwner() ?? $user;

        if ($conversation->user_id !== $owner->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $conversation->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated.',
        ]);
    }

    public function sendTemplate(Request $request, Conversation $conversation)
{
    $validated = $request->validate([
        'template_id' => 'required|exists:message_templates,id',
        'body_params' => 'nullable|array',
    ]);

    $user = $request->user();
    $owner = $user->getBusinessOwner() ?? $user;

    if ($conversation->user_id !== $owner->id) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $template = \App\Models\MessageTemplate::where('id', $validated['template_id'])
        ->where('user_id', $owner->id)
        ->where('status', 'approved')
        ->firstOrFail();

    $message = $this->messageService->sendTemplate(
        $owner, $conversation->whatsappAccount, $conversation->contact,
        $template, $validated['body_params'] ?? []
    );

    if (!$message) {
        return response()->json(['error' => 'Failed to send.'], 422);
    }

    return response()->json(['success' => true, 'message' => $message->fresh()]);
}

public function toggleBot(Request $request, Conversation $conversation)
{
    $user = $request->user();
    $owner = $user->getBusinessOwner() ?? $user;

    if ($conversation->user_id !== $owner->id) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $conversation->update([
        'is_bot_active' => !$conversation->is_bot_active,
        'bot_paused_until' => null,
    ]);

    return response()->json([
        'success' => true,
        'is_bot_active' => $conversation->fresh()->is_bot_active,
    ]);
}

public function addNote(Request $request, Conversation $conversation)
{
    $validated = $request->validate(['note' => 'required|string|max:2000']);

    $user = $request->user();
    $owner = $user->getBusinessOwner() ?? $user;

    if ($conversation->user_id !== $owner->id) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $note = \App\Models\InternalNote::create([
        'conversation_id' => $conversation->id,
        'user_id' => $user->id,
        'note' => $validated['note'],
    ]);

    return response()->json(['success' => true, 'note' => $note->load('user:id,name')]);
}
}