<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return $this->adminDashboard();
        }

        $owner = $user->getBusinessOwner() ?? $user;

        // Stats
        $stats = [
            'total_contacts' => Contact::where('user_id', $owner->id)->count(),
            'total_conversations' => Conversation::where('user_id', $owner->id)
                ->where('status', 'open')->count(),
            'messages_today' => Message::where('user_id', $owner->id)
                ->whereDate('created_at', today())->count(),
            'messages_this_month' => Message::where('user_id', $owner->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'wallet_balance' => $owner->wallet?->balance ?? 0,
            'active_campaigns' => Campaign::where('user_id', $owner->id)
                ->whereIn('status', ['sending', 'processing'])->count(),
            'messages_sent' => Message::where('user_id', $owner->id)
                ->where('direction', 'outbound')
                ->whereDate('created_at', today())->count(),
            'messages_received' => Message::where('user_id', $owner->id)
                ->where('direction', 'inbound')
                ->whereDate('created_at', today())->count(),
        ];

        // Recent conversations
        $recentConversations = Conversation::where('user_id', $owner->id)
            ->with(['contact', 'assignedAgent'])
            ->orderBy('last_message_at', 'desc')
            ->limit(10)
            ->get();

        // Message chart data (last 7 days)
        $chartData = $this->getMessageChartData($owner->id, 7);

        // Subscription info
        $subscription = $owner->getActiveSubscription();

        return view('dashboard.index', compact(
            'stats', 'recentConversations', 'chartData', 'subscription'
        ));
    }

    protected function adminDashboard()
    {
        $stats = [
            'total_users' => \App\Models\User::where('role', 'business_owner')->count(),
            'active_users' => \App\Models\User::where('role', 'business_owner')
                ->where('status', 'active')->count(),
            'total_messages_today' => Message::whereDate('created_at', today())->count(),
            'total_revenue' => \App\Models\WalletTransaction::where('type', 'credit')
                ->where('status', 'completed')->sum('amount'),
            'active_subscriptions' => \App\Models\Subscription::where('status', 'active')
                ->where('ends_at', '>', now())->count(),
            'total_partners' => \App\Models\Partner::where('status', 'approved')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    protected function getMessageChartData(int $userId, int $days): array
    {
        $labels = [];
        $sent = [];
        $received = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('M d');

            $sent[] = Message::where('user_id', $userId)
                ->where('direction', 'outbound')
                ->whereDate('created_at', $date)
                ->count();

            $received[] = Message::where('user_id', $userId)
                ->where('direction', 'inbound')
                ->whereDate('created_at', $date)
                ->count();
        }

        return [
            'labels' => $labels,
            'sent' => $sent,
            'received' => $received,
        ];
    }
}