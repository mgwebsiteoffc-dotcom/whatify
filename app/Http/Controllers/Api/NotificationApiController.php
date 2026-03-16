<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationApiController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($notifications);
    }

    public function markAllRead(Request $request)
    {
        app(\App\Services\NotificationService::class)->markAllRead($request->user());
        return response()->json(['message' => 'All notifications marked as read']);
    }

    public function unreadCount(Request $request)
    {
        $count = app(\App\Services\NotificationService::class)->getUnreadCount($request->user());
        return response()->json(['count' => $count]);
    }
}