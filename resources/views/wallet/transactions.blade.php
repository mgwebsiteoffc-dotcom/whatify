@extends('layouts.app')
@section('title', 'Transactions')
@section('page-title', 'Wallet Transactions')

@section('content')
<div class="bg-white rounded-lg shadow overflow-hidden">
    @if($transactions->isEmpty())
        <div class="p-12 text-center">
            <i class="fas fa-receipt text-5xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">No transactions found</p>
        </div>
    @else
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Balance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($transactions as $txn)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-sm text-gray-500">{{ $txn->created_at->format('M d, Y h:i A') }}</td>
                        <td class="px-6 py-3 text-sm text-gray-900">{{ $txn->description }}</td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                {{ $txn->type === 'credit' ? 'bg-green-100 text-green-700' :
                                   ($txn->type === 'debit' ? 'bg-red-100 text-red-700' :
                                   ($txn->type === 'refund' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700')) }}">
                                {{ ucfirst($txn->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-xs text-gray-500">
                            {{ $txn->reference_type }}{{ $txn->reference_id ? " #{$txn->reference_id}" : '' }}
                            @if($txn->payment_id)
                                <br><span class="font-mono">{{ $txn->payment_id }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-sm text-right font-medium
                            {{ in_array($txn->type, ['credit', 'refund', 'bonus']) ? 'text-green-600' : 'text-red-600' }}">
                            {{ in_array($txn->type, ['credit', 'refund', 'bonus']) ? '+' : '-' }}₹{{ number_format($txn->amount, 2) }}
                        </td>
                        <td class="px-6 py-3 text-sm text-right text-gray-500">₹{{ number_format($txn->balance_after, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
<div class="mt-4">{{ $transactions->links() }}</div>
@endsection