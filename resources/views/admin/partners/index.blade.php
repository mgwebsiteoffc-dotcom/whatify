@extends('layouts.app')
@section('title', 'Partners')
@section('page-title', 'Partner Management')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        @foreach([
            ['Total', $stats['total'], 'fas fa-handshake', 'blue'],
            ['Approved', $stats['approved'], 'fas fa-check', 'green'],
            ['Pending', $stats['pending'], 'fas fa-clock', 'yellow'],
            ['Total Paid', '₹'.number_format($stats['total_paid'],2), 'fas fa-money-bill', 'emerald'],
            ['Pending Payouts', '₹'.number_format($stats['pending_payouts'],2), 'fas fa-hourglass', 'orange'],
        ] as [$label, $value, $icon, $color])
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <i class="{{ $icon }} text-{{ $color }}-500 mb-1"></i>
                <p class="text-lg font-bold">{{ $value }}</p>
                <p class="text-xs text-gray-500">{{ $label }}</p>
            </div>
        @endforeach
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Partner</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Rate</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Referrals</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Earned</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($partners as $partner)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3">
                            <div class="text-sm font-medium">{{ $partner->company_name }}</div>
                            <div class="text-xs text-gray-500">{{ $partner->user?->name }} · {{ $partner->user?->email }}</div>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-500 capitalize">{{ $partner->type }}</td>
                        <td class="px-6 py-3 text-sm font-mono text-gray-600">{{ $partner->referral_code }}</td>
                        <td class="px-6 py-3 text-center">
                            <form method="POST" action="{{ route('admin.partners.updateCommission', $partner) }}" class="flex gap-1 justify-center">
                                @csrf @method('PUT')
                                <input type="number" name="commission_rate" value="{{ $partner->commission_rate }}" min="1" max="50" step="0.5"
                                       class="w-16 rounded border-gray-300 text-xs text-center px-1 py-1 border">
                                <button class="text-xs text-emerald-600"><i class="fas fa-save"></i></button>
                            </form>
                        </td>
                        <td class="px-6 py-3 text-center text-sm font-medium">{{ $partner->total_referrals }}</td>
                        <td class="px-6 py-3 text-right text-sm font-medium">₹{{ number_format($partner->total_earned, 2) }}</td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                {{ $partner->status === 'approved' ? 'bg-green-100 text-green-700' :
                                   ($partner->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                {{ ucfirst($partner->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-right">
                            @if($partner->status === 'pending')
                                <div class="flex justify-end gap-1">
                                    <form method="POST" action="{{ route('admin.partners.approve', $partner) }}">@csrf
                                        <button class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs"><i class="fas fa-check mr-1"></i>Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.partners.reject', $partner) }}">@csrf
                                        <button class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs"><i class="fas fa-times mr-1"></i>Reject</button>
                                    </form>
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div>{{ $partners->links() }}</div>
</div>
@endsection