@extends('layouts.app')
@section('title', 'Recharge Wallet')
@section('page-title', 'Recharge Wallet')

@section('content')
<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-center mb-6">
            <p class="text-sm text-gray-500">Current Balance</p>
            <p class="text-3xl font-bold text-gray-900">₹{{ number_format($wallet->balance ?? 0, 2) }}</p>
        </div>

        <form method="POST" action="{{ route('wallet.processRecharge') }}" class="space-y-6">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">Recharge Amount (₹)</label>
                <input type="number" name="amount" value="{{ old('amount', 1000) }}"
                       min="{{ config('whatify.wallet.min_recharge') }}"
                       max="{{ config('whatify.wallet.max_recharge') }}"
                       required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-3 py-2 border text-lg">
                <p class="text-xs text-gray-500 mt-1">
                    Min: ₹{{ config('whatify.wallet.min_recharge') }} | Max: ₹{{ number_format(config('whatify.wallet.max_recharge')) }}
                </p>
            </div>

            {{-- Quick amounts --}}
            <div class="flex gap-2 flex-wrap">
                @foreach([500, 1000, 2000, 5000, 10000] as $amount)
                    <button type="button" onclick="document.querySelector('[name=amount]').value={{ $amount }}"
                            class="px-4 py-2 border rounded-lg text-sm font-medium text-gray-700 hover:bg-emerald-50 hover:border-emerald-500">
                        ₹{{ number_format($amount) }}
                    </button>
                @endforeach
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                <div class="space-y-2">
                    <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="payment_gateway" value="razorpay" checked class="text-emerald-600">
                        <span class="text-sm font-medium">Razorpay (UPI, Cards, NetBanking)</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="payment_gateway" value="cashfree" class="text-emerald-600">
                        <span class="text-sm font-medium">Cashfree Payments</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="payment_gateway" value="stripe" class="text-emerald-600">
                        <span class="text-sm font-medium">Stripe (International)</span>
                    </label>
                </div>
            </div>

            <button type="submit" class="w-full py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700">
                <i class="fas fa-lock mr-2"></i>Proceed to Payment
            </button>
        </form>
    </div>
</div>
@endsection