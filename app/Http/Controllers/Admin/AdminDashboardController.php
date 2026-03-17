<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Message;
use App\Models\Partner;
use App\Models\Subscription;
use App\Models\User;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::where('role', 'business_owner')->count(),
            'active_users' => User::where('role', 'business_owner')->where('status', 'active')->count(),
            'new_users_today' => User::where('role', 'business_owner')->whereDate('created_at', today())->count(),
            'new_users_month' => User::where('role', 'business_owner')->whereMonth('created_at', now()->month)->count(),
            'total_messages_today' => Message::whereDate('created_at', today())->count(),
            'total_messages_month' => Message::whereMonth('created_at', now()->month)->count(),
            'total_revenue' => WalletTransaction::where('type', 'credit')->where('status', 'completed')->sum('amount'),
            'revenue_today' => WalletTransaction::where('type', 'credit')->where('status', 'completed')->whereDate('created_at', today())->sum('amount'),
            'revenue_month' => WalletTransaction::where('type', 'credit')->where('status', 'completed')->whereMonth('created_at', now()->month)->sum('amount'),
            'active_subscriptions' => Subscription::where('status', 'active')->where('ends_at', '>', now())->count(),
            'trial_subscriptions' => Subscription::where('status', 'trial')->where('ends_at', '>', now())->count(),
            'total_partners' => Partner::where('status', 'approved')->count(),
            'pending_partners' => Partner::where('status', 'pending')->count(),
            'total_campaigns' => Campaign::count(),
            'active_campaigns' => Campaign::whereIn('status', ['sending', 'processing'])->count(),
        ];

        $revenueChart = $this->getRevenueChart(30);
        $userChart = $this->getUserChart(30);
        $messageChart = $this->getMessageChart(7);

        $recentUsers = User::where('role', 'business_owner')
            ->with('business:id,user_id,company_name')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'revenueChart', 'userChart', 'messageChart', 'recentUsers'));
    }

    protected function getRevenueChart(int $days): array
    {
        $labels = [];
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('M d');
            $data[] = WalletTransaction::where('type', 'credit')
                ->where('status', 'completed')
                ->whereDate('created_at', $date)
                ->sum('amount');
        }

        return ['labels' => $labels, 'data' => $data];
    }

    protected function getUserChart(int $days): array
    {
        $labels = [];
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('M d');
            $data[] = User::where('role', 'business_owner')->whereDate('created_at', $date)->count();
        }

        return ['labels' => $labels, 'data' => $data];
    }

    protected function getMessageChart(int $days): array
    {
        $labels = [];
        $sent = [];
        $received = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('M d');
            $sent[] = Message::where('direction', 'outbound')->whereDate('created_at', $date)->count();
            $received[] = Message::where('direction', 'inbound')->whereDate('created_at', $date)->count();
        }

        return ['labels' => $labels, 'sent' => $sent, 'received' => $received];
    }
}