<?php

namespace App\Http\Controllers;

use App\Models\PlatformNotification;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(protected NotificationService $notificationService) {}

    public function index()
    {
        $notifications = auth()->user()->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(PlatformNotification $notification)
    {
        abort_if($notification->user_id !== auth()->id(), 403);
        $notification->markAsRead();

        return back();
    }

    public function markAllRead()
    {
        $this->notificationService->markAllRead(auth()->user());
        return back()->with('success', 'All notifications marked as read.');
    }
}