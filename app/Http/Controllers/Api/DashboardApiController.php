<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;

class DashboardApiController extends Controller
{
    public function stats(Request $request)
    {
        $user = $request->user();
        $owner = $user->getBusinessOwner() ?? $user;

        return response()->json([
            'wallet_balance' => $owner->wallet?->balance ?? 0,
            'open_conversations' => Conversation::where('user_id', $owner->id)
                ->where('status', 'open')->count(),
            'unassigned_chats' => Conversation::where('user_id', $owner->id)
                ->where('status', 'open')
                ->whereNull('assigned_agent_id')
                ->count(),
            'my_assigned' => Conversation::where('assigned_agent_id', $user->id)
                ->where('status', 'open')->count(),
            'messages_today' => Message::where('user_id', $owner->id)
                ->whereDate('created_at', today())->count(),
        ]);
    }
}