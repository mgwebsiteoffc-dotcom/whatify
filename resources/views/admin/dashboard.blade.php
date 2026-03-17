@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('page-title', 'Platform Admin Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Key Metrics --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            ['Total Users', $stats['total_users'], 'fas fa-users', 'blue', "+{$stats['new_users_today']} today"],
            ['Active Subscriptions', $stats['active_subscriptions'], 'fas fa-credit-card', 'green', "{$stats['trial_subscriptions']} trials"],
            ['Revenue (Month)', '₹'.number_format($stats['revenue_month'],2), 'fas fa-rupee-sign', 'emerald', '₹'.number_format($stats['revenue_today'],2).' today'],
            ['Messages Today', number_format($stats['total_messages_today']), 'fas fa-comment', 'purple', number_format($stats['total_messages_month']).' this month'],
        ] as [$label, $value, $icon, $color, $sub])
            <div class="bg-white rounded-lg shadow p-5">
                <div class="flex items-center gap-3">
                    <div class="rounded-md bg-{{ $color }}-100 p-3"><i class="{{ $icon }} text-{{ $color }}-600 text-xl"></i></div>
                    <div>
                        <p class="text-xs text-gray-500">{{ $label }}</p>
                        <p class="text-xl font-bold text-gray-900">{{ $value }}</p>
                        <p class="text-[10px] text-gray-400">{{ $sub }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            ['Partners', $stats['total_partners'], 'fas fa-handshake', 'indigo', "{$stats['pending_partners']} pending"],
            ['Active Campaigns', $stats['active_campaigns'], 'fas fa-bullhorn', 'orange', "{$stats['total_campaigns']} total"],
            ['New Users (Month)', $stats['new_users_month'], 'fas fa-user-plus', 'cyan', ''],
            ['Total Revenue', '₹'.number_format($stats['total_revenue'],2), 'fas fa-chart-line', 'rose', ''],
        ] as [$label, $value, $icon, $color, $sub])
            <div class="bg-white rounded-lg shadow p-5">
                <div class="flex items-center gap-3">
                    <div class="rounded-md bg-{{ $color }}-100 p-3"><i class="{{ $icon }} text-{{ $color }}-600"></i></div>
                    <div>
                        <p class="text-xs text-gray-500">{{ $label }}</p>
                        <p class="text-lg font-bold text-gray-900">{{ $value }}</p>
                        @if($sub)<p class="text-[10px] text-gray-400">{{ $sub }}</p>@endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Revenue (Last 30 Days)</h3>
            <canvas id="revenueChart" height="200"></canvas>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Messages (Last 7 Days)</h3>
            <canvas id="messageChart" height="200"></canvas>
        </div>
    </div>

    {{-- Recent Users --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-semibold">Recent Signups</h3>
            <a href="{{ route('admin.users.index') }}" class="text-sm text-emerald-600 hover:underline">View All →</a>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Business</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($recentUsers as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3">
                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                            <div class="text-xs text-gray-500">{{ $user->email }}</div>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-500">{{ $user->business?->company_name ?? '-' }}</td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $user->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                {{ ucfirst($user->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-xs text-gray-500">{{ $user->created_at->diffForHumans() }}</td>
                        <td class="px-6 py-3 text-right">
                            <a href="{{ route('admin.users.show', $user) }}" class="text-emerald-600 hover:underline text-xs">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
        labels: @json($revenueChart['labels']),
        datasets: [{
            label: 'Revenue (₹)',
            data: @json($revenueChart['data']),
            borderColor: '#10B981',
            backgroundColor: 'rgba(16,185,129,0.1)',
            fill: true, tension: 0.4,
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});

new Chart(document.getElementById('messageChart'), {
    type: 'bar',
    data: {
        labels: @json($messageChart['labels']),
        datasets: [
            { label: 'Sent', data: @json($messageChart['sent']), backgroundColor: '#10B981' },
            { label: 'Received', data: @json($messageChart['received']), backgroundColor: '#3B82F6' },
        ]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } }
});
</script>
@endpush
@endsection