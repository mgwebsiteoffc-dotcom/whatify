@extends('layouts.app')
@section('title', 'Payouts')
@section('page-title', 'Payout History')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6 flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">Pending Payout</p>
            <p class="text-2xl font-bold text-gray-900">₹{{ number_format($partner->pending_payout, 2) }}</p>
        </div>
        <div class="flex gap-3">
            @if($partner->pending_payout >= config('whatify.partner.min_payout', 1000))
                <form method="POST" action="{{ route('partner.requestPayout') }}">
                    @csrf
                    <button class="px-4 py-2 bg-emerald-600 text-white rounded-md text-sm hover:bg-emerald-700">
                        <i class="fas fa-money-bill mr-1"></i> Request Payout
                    </button>
                </form>
            @endif
            <a href="{{ route('partner.settings') }}" class="px-4 py-2 border rounded-md text-sm hover:bg-gray-50">
                <i class="fas fa-cog mr-1"></i> Bank Details
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Processed</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($payouts as $payout)
                    <tr>
                        <td class="px-6 py-3 text-sm">{{ $payout->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-3 text-sm text-right font-medium">₹{{ number_format($payout->amount, 2) }}</td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                {{ $payout->status === 'completed' ? 'bg-green-100 text-green-700' :
                                   ($payout->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                {{ ucfirst($payout->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-500">{{ $payout->transaction_reference ?? '-' }}</td>
                        <td class="px-6 py-3 text-sm text-gray-500">{{ $payout->processed_at?->format('M d, Y') ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No payouts yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $payouts->links() }}</div>
</div>
@endsection