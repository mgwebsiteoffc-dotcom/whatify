<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        $period = $request->period ?? '7';
        $startDate = Carbon::today()->subDays((int)$period);

        $messageStats = $this->getMessageStats($owner->id, $startDate);
        $campaignStats = $this->getCampaignStats($owner->id, $startDate);
        $contactStats = $this->getContactStats($owner->id, $startDate);
        $spendingStats = $this->getSpendingStats($owner->id, $startDate);
        $messageChart = $this->getMessageChart($owner->id, (int)$period);
        $categoryBreakdown = $this->getCategoryBreakdown($owner->id, $startDate);
        $topCampaigns = $this->getTopCampaigns($owner->id, $startDate);
        $hourlyDistribution = $this->getHourlyDistribution($owner->id, $startDate);

        return view('analytics.index', compact(
            'messageStats', 'campaignStats', 'contactStats', 'spendingStats',
            'messageChart', 'categoryBreakdown', 'topCampaigns', 'hourlyDistribution', 'period'
        ));
    }

    protected function getMessageStats(int $userId, Carbon $startDate): array
    {
        $query = Message::where('user_id', $userId)->where('created_at', '>=', $startDate);

        return [
            'total_sent' => (clone $query)->where('direction', 'outbound')->count(),
            'total_received' => (clone $query)->where('direction', 'inbound')->count(),
            'delivered' => (clone $query)->where('status', 'delivered')->count(),
            'read' => (clone $query)->where('status', 'read')->count(),
            'failed' => (clone $query)->where('status', 'failed')->count(),
            'delivery_rate' => $this->calcRate(
                (clone $query)->where('direction', 'outbound')->where('status', 'delivered')->count(),
                (clone $query)->where('direction', 'outbound')->count()
            ),
            'read_rate' => $this->calcRate(
                (clone $query)->where('status', 'read')->count(),
                (clone $query)->whereIn('status', ['delivered', 'read'])->count()
            ),
        ];
    }

    protected function getCampaignStats(int $userId, Carbon $startDate): array
    {
        $query = Campaign::where('user_id', $userId)->where('created_at', '>=', $startDate);

        return [
            'total' => (clone $query)->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'total_sent' => (clone $query)->sum('sent_count'),
            'total_delivered' => (clone $query)->sum('delivered_count'),
            'total_read' => (clone $query)->sum('read_count'),
            'total_replied' => (clone $query)->sum('replied_count'),
            'total_cost' => (clone $query)->sum('total_cost'),
            'avg_delivery_rate' => $this->calcRate(
                (clone $query)->sum('delivered_count'),
                (clone $query)->sum('sent_count')
            ),
        ];
    }

    protected function getContactStats(int $userId, Carbon $startDate): array
    {
        return [
            'total' => Contact::where('user_id', $userId)->count(),
            'new' => Contact::where('user_id', $userId)->where('created_at', '>=', $startDate)->count(),
            'active' => Contact::where('user_id', $userId)->where('status', 'active')->count(),
            'opted_out' => Contact::where('user_id', $userId)->where('status', 'opted_out')->count(),
            'by_source' => Contact::where('user_id', $userId)
                ->selectRaw('COALESCE(source, "unknown") as source, COUNT(*) as count')
                ->groupBy('source')
                ->pluck('count', 'source')
                ->toArray(),
        ];
    }

    protected function getSpendingStats(int $userId, Carbon $startDate): array
    {
        $query = WalletTransaction::where('user_id', $userId)->where('created_at', '>=', $startDate);

        return [
            'total_recharged' => (clone $query)->where('type', 'credit')->sum('amount'),
            'total_spent' => (clone $query)->where('type', 'debit')->sum('amount'),
            'total_refunded' => (clone $query)->where('type', 'refund')->sum('amount'),
            'avg_daily_spend' => round(
                (clone $query)->where('type', 'debit')->sum('amount') / max(1, now()->diffInDays($startDate)),
                2
            ),
        ];
    }

    protected function getMessageChart(int $userId, int $days): array
    {
        $labels = [];
        $sent = [];
        $received = [];
        $delivered = [];
        $read = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('M d');

            $dayQuery = Message::where('user_id', $userId)->whereDate('created_at', $date);

            $sent[] = (clone $dayQuery)->where('direction', 'outbound')->count();
            $received[] = (clone $dayQuery)->where('direction', 'inbound')->count();
            $delivered[] = (clone $dayQuery)->where('status', 'delivered')->count();
            $read[] = (clone $dayQuery)->where('status', 'read')->count();
        }

        return compact('labels', 'sent', 'received', 'delivered', 'read');
    }

    protected function getCategoryBreakdown(int $userId, Carbon $startDate): array
    {
        return Message::where('user_id', $userId)
            ->where('direction', 'outbound')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('COALESCE(message_category, "service") as category, COUNT(*) as count, SUM(cost) as total_cost')
            ->groupBy('message_category')
            ->get()
            ->toArray();
    }

    protected function getTopCampaigns(int $userId, Carbon $startDate): \Illuminate\Support\Collection
    {
        return Campaign::where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->where('status', 'completed')
            ->orderBy('sent_count', 'desc')
            ->limit(5)
            ->get();
    }

    protected function getHourlyDistribution(int $userId, Carbon $startDate): array
    {
        $data = Message::where('user_id', $userId)
            ->where('direction', 'inbound')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupByRaw('HOUR(created_at)')
            ->pluck('count', 'hour')
            ->toArray();

        $hours = [];
        for ($h = 0; $h < 24; $h++) {
            $hours[$h] = $data[$h] ?? 0;
        }

        return $hours;
    }

    protected function calcRate(int $numerator, int $denominator): float
    {
        if ($denominator === 0) return 0;
        return round(($numerator / $denominator) * 100, 1);
    }
}