@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Wallet Balance --}}
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="rounded-md bg-emerald-100 p-3"><i class="fas fa-wallet text-emerald-600 text-xl"></i></div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="truncate text-sm font-medium text-gray-500">Wallet Balance</dt>
                        <dd class="text-lg font-semibold text-gray-900">₹{{ number_format($stats['wallet_balance'], 2) }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        {{-- Total Contacts --}}
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="rounded-md bg-blue-100 p-3"><i class="fas fa-address-book text-blue-600 text-xl"></i></div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="truncate text-sm font-medium text-gray-500">Total Contacts</dt>
                        <dd class="text-lg font-semibold text-gray-900">{{ number_format($stats['total_contacts']) }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        {{-- Messages Today --}}
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="rounded-md bg-purple-100 p-3"><i class="fas fa-comment-dots text-purple-600 text-xl"></i></div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="truncate text-sm font-medium text-gray-500">Messages Today</dt>
                        <dd class="text-lg font-semibold text-gray-900">{{ number_format($stats['messages_today']) }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        {{-- Open Conversations --}}
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="rounded-md bg-yellow-100 p-3"><i class="fas fa-comments text-yellow-600 text-xl"></i></div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="truncate text-sm font-medium text-gray-500">Open Conversations</dt>
                        <dd class="text-lg font-semibold text-gray-900">{{ number_format($stats['total_conversations']) }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    {{-- Subscription Banner --}}
    @if($subscription && $subscription->isTrial())
        <div class="rounded-lg bg-yellow-50 border border-yellow-200 p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-clock text-yellow-600 mr-3"></i>
                    <div>
                        <p class="text-sm font-medium text-yellow-800">
                            Free trial active — {{ $subscription->daysRemaining() }} days remaining
                        </p>
                        <p class="text-xs text-yellow-600">
                            Plan: {{ $subscription->plan->name }} • Expires: {{ $subscription->ends_at->format('M d, Y') }}
                        </p>
                    </div>
                </div>
                <a href="{{ route('billing.plans') }}" class="px-4 py-2 bg-yellow-600 text-white rounded-md text-sm font-medium hover:bg-yellow-700">
                    Upgrade Now
                </a>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Messages Chart --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Messages (Last 7 Days)</h3>
            <canvas id="messagesChart" height="200"></canvas>
        </div>

        {{-- Recent Conversations --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Conversations</h3>
            @if($recentConversations->isEmpty())
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-comments text-4xl mb-2"></i>
                    <p>No conversations yet</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($recentConversations as $conversation)
                        <div class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-full bg-emerald-100 flex items-center justify-center">
                                    <span class="text-sm font-semibold text-emerald-700">
                                        {{ strtoupper(substr($conversation->contact->name ?? $conversation->contact->phone, 0, 1)) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $conversation->contact->name ?? $conversation->contact->phone }}
                                    </p>
                                    <p class="text-xs text-gray-500 truncate max-w-48">
                                        {{ $conversation->last_message ?? 'No messages' }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                    {{ $conversation->status === 'open' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ ucfirst($conversation->status) }}
                                </span>
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $conversation->last_message_at?->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <a href="{{ route('wallet.recharge') }}" class="flex flex-col items-center gap-2 p-4 rounded-lg border border-gray-200 hover:border-emerald-500 hover:bg-emerald-50 transition-colors">
                <i class="fas fa-plus-circle text-2xl text-emerald-600"></i>
                <span class="text-sm font-medium text-gray-700">Recharge Wallet</span>
            </a>
            <div class="flex flex-col items-center gap-2 p-4 rounded-lg border border-gray-200 opacity-50">
                <i class="fas fa-paper-plane text-2xl text-gray-400"></i>
                <span class="text-sm font-medium text-gray-400">New Campaign</span>
            </div>
            <div class="flex flex-col items-center gap-2 p-4 rounded-lg border border-gray-200 opacity-50">
                <i class="fas fa-user-plus text-2xl text-gray-400"></i>
                <span class="text-sm font-medium text-gray-400">Add Contact</span>
            </div>
            <div class="flex flex-col items-center gap-2 p-4 rounded-lg border border-gray-200 opacity-50">
                <i class="fas fa-robot text-2xl text-gray-400"></i>
                <span class="text-sm font-medium text-gray-400">Create Automation</span>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('messagesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartData['labels']),
            datasets: [
                {
                    label: 'Sent',
                    data: @json($chartData['sent']),
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4,
                },
                {
                    label: 'Received',
                    data: @json($chartData['received']),
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
</script>
@endpush
@endsection