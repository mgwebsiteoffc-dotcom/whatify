@extends('layouts.app')
@section('title', 'WhatsApp Account')
@section('page-title')
    <a href="{{ route('whatsapp.accounts.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left mr-2"></i></a>
    {{ $account->display_name ?? $account->phone_number }}
@endsection

@section('content')
<div class="space-y-6">
    {{-- Account Info --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Account Details</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-500">Phone Number:</span>
                    <span class="ml-2 font-medium">+{{ $account->phone_number }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Status:</span>
                    <span class="ml-2 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                        {{ $account->status === 'connected' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ ucfirst($account->status) }}
                    </span>
                </div>
                <div>
                    <span class="text-gray-500">Display Name:</span>
                    <span class="ml-2 font-medium">{{ $account->display_name ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Quality Rating:</span>
                    <span class="ml-2 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                        {{ $account->quality_rating === 'GREEN' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ $account->quality_rating }}
                    </span>
                </div>
                <div>
                    <span class="text-gray-500">Phone Number ID:</span>
                    <span class="ml-2 font-mono text-xs">{{ $account->phone_number_id }}</span>
                </div>
                <div>
                    <span class="text-gray-500">WABA ID:</span>
                    <span class="ml-2 font-mono text-xs">{{ $account->waba_id }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Connected:</span>
                    <span class="ml-2 font-medium">{{ $account->connected_at?->format('M d, Y h:i A') ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Stats</h3>
            <div class="space-y-3">
                @foreach([
                    ['Total Messages', $stats['total_messages'], 'fas fa-comments', 'blue'],
                    ['Messages Today', $stats['messages_today'], 'fas fa-comment', 'green'],
                    ['Open Conversations', $stats['conversations'], 'fas fa-inbox', 'yellow'],
                    ['Approved Templates', $stats['approved_templates'] . '/' . $stats['templates'], 'fas fa-file-alt', 'purple'],
                ] as [$label, $value, $icon, $color])
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                        <div class="flex items-center gap-2">
                            <i class="{{ $icon }} text-{{ $color }}-500"></i>
                            <span class="text-sm text-gray-600">{{ $label }}</span>
                        </div>
                        <span class="font-semibold">{{ $value }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Test Message --}}
    @if($account->status === 'connected')
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Send Test Message</h3>
            <form method="POST" action="{{ route('whatsapp.accounts.testMessage', $account) }}" class="flex gap-4">
                @csrf
                <input type="text" name="phone" placeholder="Phone (e.g. 919876543210)" required
                       class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-3 py-2 border">
                <input type="text" name="message" placeholder="Test message..." required
                       class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-3 py-2 border">
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700">
                    <i class="fas fa-paper-plane mr-1"></i> Send
                </button>
            </form>
        </div>
    @endif

    {{-- Actions --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Actions</h3>
        <div class="flex gap-3 flex-wrap">
            <form method="POST" action="{{ route('whatsapp.accounts.syncTemplates', $account) }}">
                @csrf
                <button type="submit" class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">
                    <i class="fas fa-sync mr-1"></i> Sync Templates
                </button>
            </form>

            <a href="{{ route('whatsapp.templates.create') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">
                <i class="fas fa-plus mr-1"></i> Create Template
            </a>

            @if($account->status === 'connected')
                <form method="POST" action="{{ route('whatsapp.accounts.disconnect', $account) }}"
                      onsubmit="return confirm('Are you sure you want to disconnect this number?')">
                    @csrf
                    <button type="submit" class="px-4 py-2 border border-red-300 text-red-600 rounded-md text-sm hover:bg-red-50">
                        <i class="fas fa-unlink mr-1"></i> Disconnect
                    </button>
                </form>
            @endif

            <form method="POST" action="{{ route('whatsapp.accounts.destroy', $account) }}"
                  onsubmit="return confirm('Delete this account permanently? This cannot be undone.')">
                @csrf @method('DELETE')
                <button type="submit" class="px-4 py-2 border border-red-300 text-red-600 rounded-md text-sm hover:bg-red-50">
                    <i class="fas fa-trash mr-1"></i> Delete
                </button>
            </form>
        </div>
    </div>
</div>
@endsection