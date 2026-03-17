@extends('layouts.app')
@section('title', 'Partner Payouts')
@section('page-title', 'Payout Management')

@section('content')
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Partner</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Requested</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($payouts as $payout)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3">
                        <div class="text-sm font-medium">{{ $payout->partner?->company_name }}</div>
                        <div class="text-xs text-gray-500">{{ $payout->partner?->user?->email }}</div>
                    </td>
                    <td class="px-6 py-3 text-right text-sm font-bold">₹{{ number_format($payout->amount, 2) }}</td>
                    <td class="px-6 py-3">
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                            {{ $payout->status === 'completed' ? 'bg-green-100 text-green-700' :
                               ($payout->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                            {{ ucfirst($payout->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $payout->created_at->format('M d, Y') }}</td>
                    <td class="px-6 py-3 text-right">
                        @if($payout->status === 'pending')
                            <form method="POST" action="{{ route('admin.payouts.process', $payout) }}" class="flex gap-1 justify-end">
                                @csrf
                                <input type="text" name="transaction_reference" placeholder="Txn ref" class="rounded border-gray-300 text-xs px-2 py-1 border w-32">
                                <button type="submit" name="action" value="approve" class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">
                                    <i class="fas fa-check mr-1"></i>Pay
                                </button>
                                <button type="submit" name="action" value="reject" class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        @else
                            <span class="text-xs text-gray-400">{{ $payout->transaction_reference ?? '-' }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No payout requests</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $payouts->links() }}</div>
@endsection