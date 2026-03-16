@extends('layouts.app')
@section('title', 'Wallet')
@section('page-title', 'Wallet')

@section('content')
<div class="space-y-6">
    {{-- Balance Card --}}
    <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 rounded-xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-emerald-100 text-sm">Available Balance</p>
                <p class="text-4xl font-bold mt-1">₹{{ number_format($wallet->balance, 2) }}</p>
                <div class="mt-2 text-sm text-emerald-200 space-x-4">
                    <span><i class="fas fa-arrow-up mr-1"></i>Recharged: ₹{{ number_format($wallet->total_recharged, 2) }}</span>
                    <span><i class="fas fa-arrow-down mr-1"></i>Spent: ₹{{ number_format($wallet->total_spent, 2) }}</span>
                </div>
            </div>
            <a href="{{ route('wallet.recharge') }}"
               class="px-6 py-3 bg-white text-emerald-700 rounded-lg font-semibold hover:bg-emerald-50 transition-colors">
                <i class="fas fa-plus mr-2"></i>Recharge
            </a>
        </div>
    </div>

    {{-- Message Pricing --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Message Pricing</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach(config('whatify.message_cost') as $type => $cost)
                <div class="p-4 rounded-lg border text-center">
                    <p class="text-sm text-gray-500 capitalize">{{ $type }}</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">₹{{ $cost }}</p>
                    <p class="text-xs text-gray-400">per message</p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Recent Transactions --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Recent Transactions</h3>
            <a href="{{ route('wallet.transactions') }}" class="text-sm text-emerald-600 hover:text-emerald-700">View All</a>
        </div>

        @if($recentTransactions->isEmpty())
            <p class="text-center text-gray-500 py-8">No transactions yet</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($recentTransactions as $txn)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $txn->created_at->format('M d, h:i A') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $txn->description }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                        {{ $txn->type === 'credit' ? 'bg-green-100 text-green-700' : ($txn->type === 'debit' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') }}">
                                        {{ ucfirst($txn->type) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-medium {{ $txn->type === 'credit' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $txn->type === 'credit' ? '+' : '-' }}₹{{ number_format($txn->amount, 2) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-500">₹{{ number_format($txn->balance_after, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection