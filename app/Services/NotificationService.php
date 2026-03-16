<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\PlatformNotification;
use App\Models\User;

class NotificationService
{
    public function send(
        User $user,
        string $title,
        string $message,
        string $type = 'info',
        ?string $actionUrl = null,
        ?array $data = null
    ): PlatformNotification {
        $notification = PlatformNotification::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'action_url' => $actionUrl,
            'data' => $data,
        ]);

        // Send push notification to mobile devices
        $this->sendPushNotification($user, $title, $message, $data);

        return $notification;
    }

    public function sendToMultiple(array $userIds, string $title, string $message, string $type = 'info'): void
    {
        foreach ($userIds as $userId) {
            PlatformNotification::create([
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
            ]);
        }
    }

    public function markAllRead(User $user): void
    {
        PlatformNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    public function getUnreadCount(User $user): int
    {
        return PlatformNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
    }

    protected function sendPushNotification(User $user, string $title, string $body, ?array $data = null): void
    {
        $tokens = DeviceToken::where('user_id', $user->id)
            ->where('is_active', true)
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) return;

        // Firebase Cloud Messaging (FCM) implementation
        // This will be used by mobile apps for agent notifications
        try {
            // TODO: Implement FCM push notification
            // $this->sendFCM($tokens, $title, $body, $data);
        } catch (\Exception $e) {
            \Log::error('Push notification failed: ' . $e->getMessage());
        }
    }
}