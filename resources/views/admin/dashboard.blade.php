@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach([
            ['label' => 'Total Users', 'value' => $stats['total_users'], 'icon' => 'fas fa-users', 'color' => 'blue'],
            ['label' => 'Active Users', 'value' => $stats['active_users'], 'icon' => 'fas fa-user-check', 'color' => 'green'],
            ['label' => 'Messages Today', 'value' => number_format($stats['total_messages_today']), 'icon' => 'fas fa-comment', 'color' => 'purple'],
            ['label' => 'Total Revenue', 'value' => '₹' . number_format($stats['total_revenue'], 2), 'icon' => 'fas fa-rupee-sign', 'color' => 'emerald'],
            ['label' => 'Active Subscriptions', 'value' => $stats['active_subscriptions'], 'icon' => 'fas fa-credit-card', 'color' => 'yellow'],
            ['label' => 'Partners', 'value' => $stats['total_partners'], 'icon' => 'fas fa-handshake', 'color' => 'indigo'],
        ] as $stat)
            <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="rounded-md bg-{{ $stat['color'] }}-100 p-3">
                            <i class="{{ $stat['icon'] }} text-{{ $stat['color'] }}-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-5">
                        <dt class="truncate text-sm font-medium text-gray-500">{{ $stat['label'] }}</dt>
                        <dd class="text-lg font-semibold text-gray-900">{{ $stat['value'] }}</dd>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection