@extends('layouts.app')
@section('title', 'Analytics')
@section('page-title', 'Analytics Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Period Selector --}}
    <div class="flex gap-2">
        @foreach(['7' => '7 Days', '14' => '14 Days', '30' => '30 Days', '90' => '90 Days'] as $val => $label)
            <a href="{{ route('analytics.index', ['period' => $val]) }}"
               class="px-4 py-2 rounded-md text-sm font-medium {{ $period == $val ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 border hover:bg-gray-50' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Message Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            ['Messages Sent', number_format($messageStats['total_sent']), 'fas fa-paper-plane', 'blue'],
            ['Messages Received', number_format($messageStats['total_received']), 'fas fa-inbox', 'green'],
            ['Delivery Rate', $messageStats['delivery_rate'].'%', 'fas fa-check-double', 'emerald'],
            ['Read Rate', $messageStats['read_rate'].'%', 'fas fa-eye', 'purple'],
        ] as [$label, $value, $icon, $color])
            <div class="bg-white rounded-lg shadow p-5">
                <div class="flex items-center gap-3">
                    <div class="rounded-md bg-{{ $color }}-100 p-3"><i class="{{ $icon }} text-{{ $color }}-600 text-lg"></i></div>
                    <div>
                        <p class="text-xs text-gray-500">{{ $label }}</p>
                        <p class="text-xl font-bold text-gray-900">{{ $value }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Message Trends</h3>
            <canvas id="msgChart" height="250"></canvas>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Peak Hours (Inbound Messages)</h3>
            <canvas id="hourChart" height="250"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Campaign Stats --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Campaigns</h3>
            <div class="space-y-3 text-sm">
                @foreach([
                    ['Total Campaigns', $campaignStats['total']],
                    ['Completed', $campaignStats['completed']],
                    ['Total Sent', number_format($campaignStats['total_sent'])],
                    ['Total Delivered', number_format($campaignStats['total_delivered'])],
                    ['Total Read', number_format($campaignStats['total_read'])],
                    ['Avg Delivery Rate', $campaignStats['avg_delivery_rate'].'%'],
                    ['Total Cost', '₹'.number_format($campaignStats['total_cost'], 2)],
                ] as [$k, $v])
                    <div class="flex justify-between border-b py-1">
                        <span class="text-gray-500">{{ $k }}</span>
                        <span class="font-medium">{{ $v }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Contact Stats --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Contacts</h3>
            <div class="space-y-3 text-sm">
                @foreach([
                    ['Total Contacts', number_format($contactStats['total'])],
                    ['New (this period)', number_format($contactStats['new'])],
                    ['Active', number_format($contactStats['active'])],
                    ['Opted Out', number_format($contactStats['opted_out'])],
                ] as [$k, $v])
                    <div class="flex justify-between border-b py-1">
                        <span class="text-gray-500">{{ $k }}</span>
                        <span class="font-medium">{{ $v }}</span>
                    </div>
                @endforeach
            </div>

            <h4 class="text-sm font-medium text-gray-700 mt-4 mb-2">By Source</h4>
            @foreach($contactStats['by_source'] as $source => $count)
                <div class="flex justify-between text-sm py-0.5">
                    <span class="text-gray-500 capitalize">{{ $source }}</span>
                    <span>{{ number_format($count) }}</span>
                </div>
            @endforeach
        </div>

        {{-- Spending --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Spending</h3>
            <div class="space-y-3 text-sm">
                @foreach([
                    ['Total Recharged', '₹'.number_format($spendingStats['total_recharged'], 2)],
                    ['Total Spent', '₹'.number_format($spendingStats['total_spent'], 2)],
                    ['Refunded', '₹'.number_format($spendingStats['total_refunded'], 2)],
                    ['Avg Daily Spend', '₹'.number_format($spendingStats['avg_daily_spend'], 2)],
                ] as [$k, $v])
                    <div class="flex justify-between border-b py-1">
                        <span class="text-gray-500">{{ $k }}</span>
                        <span class="font-medium">{{ $v }}</span>
                    </div>
                @endforeach
            </div>

            <h4 class="text-sm font-medium text-gray-700 mt-4 mb-2">By Message Category</h4>
            @foreach($categoryBreakdown as $cat)
                <div class="flex justify-between text-sm py-0.5">
                    <span class="text-gray-500 capitalize">{{ $cat['category'] }}</span>
                    <span>{{ number_format($cat['count']) }} msgs · ₹{{ number_format($cat['total_cost'], 2) }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Top Campaigns --}}
    @if($topCampaigns->isNotEmpty())
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Top Campaigns</h3>
            <table class="min-w-full">
                <thead>
                    <tr class="text-xs text-gray-500 uppercase">
                        <th class="text-left py-2">Campaign</th>
                        <th class="text-center py-2">Sent</th>
                        <th class="text-center py-2">Delivered</th>
                        <th class="text-center py-2">Read</th>
                        <th class="text-center py-2">Delivery %</th>
                        <th class="text-center py-2">Read %</th>
                        <th class="text-right py-2">Cost</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topCampaigns as $c)
                        <tr class="border-t text-sm">
                            <td class="py-2 font-medium">{{ $c->name }}</td>
                            <td class="py-2 text-center">{{ number_format($c->sent_count) }}</td>
                            <td class="py-2 text-center">{{ number_format($c->delivered_count) }}</td>
                            <td class="py-2 text-center">{{ number_format($c->read_count) }}</td>
                            <td class="py-2 text-center text-green-600 font-medium">{{ $c->getDeliveryRate() }}%</td>
                            <td class="py-2 text-center text-blue-600 font-medium">{{ $c->getReadRate() }}%</td>
                            <td class="py-2 text-right">₹{{ number_format($c->total_cost, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('msgChart'), {
    type: 'line',
    data: {
        labels: @json($messageChart['labels']),
        datasets: [
            { label: 'Sent', data: @json($messageChart['sent']), borderColor: '#10B981', backgroundColor: 'rgba(16,185,129,0.1)', fill: true, tension: 0.4 },
            { label: 'Received', data: @json($messageChart['received']), borderColor: '#3B82F6', backgroundColor: 'rgba(59,130,246,0.1)', fill: true, tension: 0.4 },
            { label: 'Delivered', data: @json($messageChart['delivered']), borderColor: '#8B5CF6', borderDash: [5,5], fill: false, tension: 0.4 },
            { label: 'Read', data: @json($messageChart['read']), borderColor: '#F59E0B', borderDash: [5,5], fill: false, tension: 0.4 },
        ]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } }
});

new Chart(document.getElementById('hourChart'), {
    type: 'bar',
    data: {
        labels: @json(array_map(fn($h) => sprintf('%02d:00', $h), array_keys($hourlyDistribution))),
        datasets: [{ label: 'Messages', data: @json(array_values($hourlyDistribution)), backgroundColor: '#10B981' }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
</script>
@endpush
@endsection