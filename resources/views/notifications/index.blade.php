@extends('layouts.app')
@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('content')
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <p class="text-sm text-gray-500">{{ $notifications->total() }} notifications</p>
        @if($notifications->where('is_read', false)->count() > 0)
            <form method="POST" action="{{ route('notifications.readAll') }}">
                @csrf
                <button type="submit" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">
                    <i class="fas fa-check-double mr-1"></i> Mark all as read
                </button>
            </form>
        @endif
    </div>

    @forelse($notifications as $notification)
        <div class="bg-white rounded-lg shadow p-4 flex items-start gap-4 {{ $notification->is_read ? 'opacity-60' : 'border-l-4 border-emerald-500' }}">
            <div class="flex-shrink-0 mt-1">
                @php
                    $iconMap = [
                        'message' => 'fas fa-comment text-blue-500',
                        'campaign' => 'fas fa-bullhorn text-purple-500',
                        'wallet' => 'fas fa-wallet text-emerald-500',
                        'warning' => 'fas fa-exclamation-triangle text-yellow-500',
                        'error' => 'fas fa-exclamation-circle text-red-500',
                        'success' => 'fas fa-check-circle text-green-500',
                        'info' => 'fas fa-info-circle text-blue-500',
                    ];
                @endphp
                <i class="{{ $iconMap[$notification->type] ?? 'fas fa-bell text-gray-400' }} text-lg"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900">{{ $notification->title }}</p>
                <p class="text-sm text-gray-500 mt-0.5">{{ $notification->message }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
            </div>
            @unless($notification->is_read)
                <form method="POST" action="{{ route('notifications.read', $notification) }}">
                    @csrf
                    <button type="submit" class="text-xs text-gray-400 hover:text-gray-600" title="Mark as read">
                        <i class="fas fa-check"></i>
                    </button>
                </form>
            @endunless
        </div>
    @empty
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <i class="fas fa-bell-slash text-4xl text-gray-300 mb-2"></i>
            <p class="text-gray-500">No notifications yet</p>
        </div>
    @endforelse

    <div>{{ $notifications->links() }}</div>
</div>
@endsection