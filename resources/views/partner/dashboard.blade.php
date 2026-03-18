@extends('layouts.app')
@section('title', 'Partner Dashboard')
@section('page-title', 'Partner Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Pending Approval Banner --}}
    @if($partner->status === 'pending')
        <div class="bg-yellow-50 border-2 border-yellow-200 rounded-xl p-6">
            <div class="flex items-start gap-4">
                <div class="h-12 w-12 rounded-full bg-yellow-100 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-yellow-800">Application Under Review</h3>
                    <p class="text-sm text-yellow-700 mt-1">
                        Your partner application is being reviewed by our team. You'll receive an email notification once approved.
                        This usually takes less than 24 hours.
                    </p>
                    <p class="text-xs text-yellow-600 mt-2">
                        Applied on: {{ $partner->created_at->format('M d, Y h:i A') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Rejected Banner --}}
    @if($partner->status === 'rejected')
        <div class="bg-red-50 border-2 border-red-200 rounded-xl p-6">
            <div class="flex items-start gap-4">
                <div class="h-12 w-12 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-red-800">Application Not Approved</h3>
                    <p class="text-sm text-red-700 mt-1">
                        Unfortunately your partner application was not approved at this time.
                        Please contact <a href="mailto:partners@whatify.com" class="underline">partners@whatify.com</a> for details.
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Only show dashboard content for approved partners --}}
    @if($partner->status === 'approved')

        {{-- Referral Link --}}
        <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 rounded-xl p-6 text-white shadow-lg">
            <h3 class="text-lg font-semibold mb-2">Your Referral Link</h3>
            <div class="flex gap-2">
                <input type="text" id="refLink" readonly
                       value="{{ url('/register?ref=' . $partner->referral_code) }}"
                       class="flex-1 bg-emerald-800 border-emerald-500 rounded-md text-sm px-3 py-2 text-white border">
                <button onclick="copyLink()" id="copyBtn"
                        class="px-4 py-2 bg-white text-emerald-700 rounded-md font-medium text-sm hover:bg-emerald-50">
                    <i class="fas fa-copy"></i> Copy
                </button>
            </div>
            <p class="text-emerald-200 text-xs mt-2">
                Referral Code: <strong>{{ $partner->referral_code }}</strong> ·
                Commission: {{ $partner->commission_rate }}%
            </p>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach([
                ['Total Referrals', $stats['total_referrals'], 'fas fa-users', 'blue'],
                ['Active Customers', $stats['active_customers'], 'fas fa-user-check', 'green'],
                ['Total Earned', '₹' . number_format($stats['total_earned'], 2), 'fas fa-rupee-sign', 'emerald'],
                ['Pending Payout', '₹' . number_format($stats['pending_payout'], 2), 'fas fa-hourglass', 'orange'],
            ] as [$label, $value, $icon, $color])
                <div class="bg-white rounded-lg shadow p-5">
                    <div class="flex items-center gap-3">
                        <div class="rounded-md bg-{{ $color }}-100 p-3">
                            <i class="{{ $icon }} text-{{ $color }}-600"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">{{ $label }}</p>
                            <p class="text-xl font-bold">{{ $value }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Referred Users --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Referred Customers</h3>
                <div class="space-y-3 max-h-80 overflow-y-auto">
                    @forelse($referredUsers as $rUser)
                        <div class="flex items-center justify-between p-2 rounded hover:bg-gray-50 border-b">
                            <div>
                                <p class="text-sm font-medium">{{ $rUser->name }}</p>
                                <p class="text-xs text-gray-500">{{ $rUser->email }} · Joined {{ $rUser->created_at->format('M d') }}</p>
                            </div>
                            <div class="text-right text-xs">
                                <p class="font-medium">₹{{ number_format($rUser->wallet?->total_recharged ?? 0, 2) }}</p>
                                <p class="text-gray-400">recharged</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-400">
                            <i class="fas fa-users text-3xl mb-2"></i>
                            <p class="text-sm">No referrals yet</p>
                            <p class="text-xs mt-1">Share your referral link to start earning!</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Commissions --}}
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Recent Commissions</h3>
                    <a href="{{ route('partner.payouts') }}" class="text-sm text-emerald-600 hover:underline">Payouts →</a>
                </div>
                <div class="space-y-2 max-h-80 overflow-y-auto">
                    @forelse($recentCommissions as $comm)
                        <div class="flex items-center justify-between text-sm border-b pb-2">
                            <div>
                                <p class="font-medium capitalize">{{ $comm->event }}</p>
                                <p class="text-xs text-gray-500">{{ $comm->user?->name ?? 'User' }} · {{ $comm->created_at->format('M d') }}</p>
                            </div>
                            <span class="text-green-600 font-medium">+₹{{ number_format($comm->commission, 2) }}</span>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-400">
                            <i class="fas fa-coins text-3xl mb-2"></i>
                            <p class="text-sm">No commissions yet</p>
                            <p class="text-xs mt-1">Commissions appear when your referrals make payments</p>
                        </div>
                    @endforelse
                </div>

                @if($partner->pending_payout >= config('whatify.partner.min_payout', 1000))
                    <form method="POST" action="{{ route('partner.requestPayout') }}" class="mt-4">
                        @csrf
                        <button class="w-full px-4 py-2 bg-emerald-600 text-white rounded-md text-sm hover:bg-emerald-700 font-medium">
                            <i class="fas fa-money-bill mr-1"></i> Request Payout (₹{{ number_format($partner->pending_payout, 2) }})
                        </button>
                    </form>
                @endif
            </div>
        </div>

    @endif {{-- end approved check --}}
</div>

@push('scripts')
<script>
function copyLink() {
    const input = document.getElementById('refLink');
    input.select();
    navigator.clipboard.writeText(input.value);
    const btn = document.getElementById('copyBtn');
    btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
    setTimeout(() => { btn.innerHTML = '<i class="fas fa-copy"></i> Copy'; }, 2000);
}
</script>
@endpush
@endsection