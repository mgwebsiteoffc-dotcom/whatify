@extends('layouts.app')
@section('title', 'WhatsApp Accounts')
@section('page-title', 'WhatsApp Accounts')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <p class="text-sm text-gray-500">Manage your connected WhatsApp Business API numbers.</p>
        <a href="{{ route('whatsapp.accounts.create') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 text-sm font-medium">
            <i class="fas fa-plus mr-2"></i>Connect Number
        </a>
    </div>

    @if($accounts->isEmpty())
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <div class="text-6xl text-emerald-500 mb-4"><i class="fab fa-whatsapp"></i></div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No WhatsApp Accounts Connected</h3>
            <p class="text-gray-500 mb-6">Connect your first WhatsApp Business API number to start sending messages.</p>
            <a href="{{ route('whatsapp.accounts.create') }}" class="px-6 py-3 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 font-medium">
                <i class="fas fa-plus mr-2"></i>Connect WhatsApp Number
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($accounts as $account)
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="h-12 w-12 rounded-full bg-emerald-100 flex items-center justify-center">
                                    <i class="fab fa-whatsapp text-2xl text-emerald-600"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        {{ $account->display_name ?? $account->phone_number }}
                                    </h3>
                                    <p class="text-sm text-gray-500">+{{ $account->phone_number }}</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                {{ $account->status === 'connected' ? 'bg-green-100 text-green-700' :
                                   ($account->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                <span class="mr-1 h-1.5 w-1.5 rounded-full
                                    {{ $account->status === 'connected' ? 'bg-green-500' :
                                       ($account->status === 'pending' ? 'bg-yellow-500' : 'bg-red-500') }}"></span>
                                {{ ucfirst($account->status) }}
                            </span>
                        </div>

                        <div class="mt-4 grid grid-cols-3 gap-4 text-center">
                            <div class="p-2 bg-gray-50 rounded">
                                <p class="text-lg font-semibold text-gray-900">{{ $account->conversations_count ?? 0 }}</p>
                                <p class="text-xs text-gray-500">Conversations</p>
                            </div>
                            <div class="p-2 bg-gray-50 rounded">
                                <p class="text-lg font-semibold text-gray-900">
                                    {{ $account->templates()->where('status', 'approved')->count() }}
                                </p>
                                <p class="text-xs text-gray-500">Templates</p>
                            </div>
                            <div class="p-2 bg-gray-50 rounded">
                                <p class="text-lg font-semibold">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                        {{ $account->quality_rating === 'GREEN' ? 'bg-green-100 text-green-700' :
                                           ($account->quality_rating === 'YELLOW' ? 'bg-yellow-100 text-yellow-700' :
                                           ($account->quality_rating === 'RED' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700')) }}">
                                        {{ $account->quality_rating }}
                                    </span>
                                </p>
                                <p class="text-xs text-gray-500">Quality</p>
                            </div>
                        </div>

                        <div class="mt-4 flex gap-2">
                            <a href="{{ route('whatsapp.accounts.show', $account) }}"
                               class="flex-1 text-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                <i class="fas fa-eye mr-1"></i> View
                            </a>
                            <form method="POST" action="{{ route('whatsapp.accounts.syncTemplates', $account) }}" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-sync mr-1"></i> Sync
                                </button>
                            </form>
                            @if($account->status === 'connected')
                                <form method="POST" action="{{ route('whatsapp.accounts.disconnect', $account) }}"
                                      onsubmit="return confirm('Disconnect this WhatsApp number?')">
                                    @csrf
                                    <button type="submit" class="px-3 py-2 border border-red-300 rounded-md text-sm font-medium text-red-600 hover:bg-red-50">
                                        <i class="fas fa-unlink"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection