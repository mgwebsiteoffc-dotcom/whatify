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
    // public function index()
    // {
    //     $user = auth()->user();

    //     // Role-based redirect
    //     if ($user->isSuperAdmin()) {
    //         return redirect()->route('admin.dashboard');
    //     }

    //     if ($user->isPartner() && !$user->business) {
    //         return redirect()->route('partner.dashboard');
    //     }

    //     // Business owner / Agent dashboard
    //     $owner = $user->getBusinessOwner() ?? $user;

    //     $stats = [
    //         'total_contacts' => Contact::where('user_id', $owner->id)->count(),
    //         'total_conversations' => Conversation::where('user_id', $owner->id)
    //             ->where('status', 'open')->count(),
    //         'messages_today' => Message::where('user_id', $owner->id)
    //             ->whereDate('created_at', today())->count(),
    //         'messages_this_month' => Message::where('user_id', $owner->id)
    //             ->whereMonth('created_at', now()->month)
    //             ->whereYear('created_at', now()->year)
    //             ->count(),
    //         'wallet_balance' => $owner->wallet?->balance ?? 0,
    //         'active_campaigns' => Campaign::where('user_id', $owner->id)
    //             ->whereIn('status', ['sending', 'processing'])->count(),
    //         'messages_sent' => Message::where('user_id', $owner->id)
    //             ->where('direction', 'outbound')
    //             ->whereDate('created_at', today())->count(),
    //         'messages_received' => Message::where('user_id', $owner->id)
    //             ->where('direction', 'inbound')
    //             ->whereDate('created_at', today())->count(),
    //     ];

    //     $recentConversations = Conversation::where('user_id', $owner->id)
    //         ->with(['contact', 'assignedAgent'])
    //         ->orderBy('last_message_at', 'desc')
    //         ->limit(10)
    //         ->get();

    //     $chartData = $this->getMessageChartData($owner->id, 7);
    //     $subscription = $owner->getActiveSubscription();

    //     return view('dashboard.index', compact(
    //         'stats', 'recentConversations', 'chartData', 'subscription'
    //     ));
    // }


    public function index()
{
    $user = auth()->user();

    // If admin is "logged in as" a user, show business dashboard
    if (session('admin_original_id')) {
        return $this->businessDashboard($user);
    }

    // Role-based routing
    if ($user->isSuperAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    if ($user->role === 'partner' && !$user->business) {
        return redirect()->route('partner.dashboard');
    }

    if ($user->isAgent()) {
        return redirect()->route('inbox.index');
    }

    return $this->businessDashboard($user);
}

protected function businessDashboard($user)
{
    $owner = $user->getBusinessOwner() ?? $user;

    $stats = [
        'total_contacts' => \App\Models\Contact::where('user_id', $owner->id)->count(),
        'total_conversations' => \App\Models\Conversation::where('user_id', $owner->id)
            ->where('status', 'open')->count(),
        'messages_today' => \App\Models\Message::where('user_id', $owner->id)
            ->whereDate('created_at', today())->count(),
        'messages_this_month' => \App\Models\Message::where('user_id', $owner->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)->count(),
        'wallet_balance' => $owner->wallet?->balance ?? 0,
        'active_campaigns' => \App\Models\Campaign::where('user_id', $owner->id)
            ->whereIn('status', ['sending', 'processing'])->count(),
        'messages_sent' => \App\Models\Message::where('user_id', $owner->id)
            ->where('direction', 'outbound')
            ->whereDate('created_at', today())->count(),
        'messages_received' => \App\Models\Message::where('user_id', $owner->id)
            ->where('direction', 'inbound')
            ->whereDate('created_at', today())->count(),
    ];

    $recentConversations = \App\Models\Conversation::where('user_id', $owner->id)
        ->with(['contact', 'assignedAgent'])
        ->orderBy('last_message_at', 'desc')
        ->limit(10)
        ->get();

    $chartData = $this->getMessageChartData($owner->id, 7);
    $subscription = $owner->getActiveSubscription();

    return view('dashboard.index', compact('stats', 'recentConversations', 'chartData', 'subscription'));
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

        return compact('labels', 'sent', 'received');
    }
}