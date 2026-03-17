@extends('layouts.app')
@section('title', 'Recharge Wallet')
@section('page-title', 'Recharge Wallet')

@section('content')
<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-lg shadow p-6" x-data="walletRecharge()">
        <div class="text-center mb-6">
            <p class="text-sm text-gray-500">Current Balance</p>
            <p class="text-3xl font-bold text-gray-900">₹{{ number_format($wallet->balance ?? 0, 2) }}</p>
        </div>

        <div class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700">Recharge Amount (₹)</label>
                <input type="number" x-model.number="amount"
                       min="{{ config('whatify.wallet.min_recharge') }}"
                       max="{{ config('whatify.wallet.max_recharge') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-3 py-2 border text-lg">
                <p class="text-xs text-gray-500 mt-1">
                    Min: ₹{{ config('whatify.wallet.min_recharge') }} | Max: ₹{{ number_format(config('whatify.wallet.max_recharge')) }}
                </p>
            </div>

            {{-- Quick amounts --}}
            <div class="flex gap-2 flex-wrap">
                @foreach([500, 1000, 2000, 5000, 10000] as $amt)
                    <button type="button" @click="amount = {{ $amt }}"
                            :class="amount === {{ $amt }} ? 'bg-emerald-100 border-emerald-500 text-emerald-700' : 'hover:bg-gray-50'"
                            class="px-4 py-2 border rounded-lg text-sm font-medium text-gray-700 transition-colors">
                        ₹{{ number_format($amt) }}
                    </button>
                @endforeach
            </div>

            {{-- Estimation --}}
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm font-medium text-gray-700 mb-2">This amount will give you approximately:</p>
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Marketing msgs:</span>
                        <span class="font-medium" x-text="Math.floor(amount / {{ config('whatify.message_cost.marketing') }})"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Utility msgs:</span>
                        <span class="font-medium" x-text="Math.floor(amount / {{ config('whatify.message_cost.utility') }})"></span>
                    </div>
                </div>
            </div>

            <button @click="payWithRazorpay()" :disabled="loading || amount < {{ config('whatify.wallet.min_recharge') }}"
                    class="w-full py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="!loading"><i class="fas fa-lock mr-2"></i>Pay ₹<span x-text="amount"></span> via Razorpay</span>
                <span x-show="loading"><i class="fas fa-spinner fa-spin mr-2"></i>Processing...</span>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
function walletRecharge() {
    return {
        amount: 1000,
        loading: false,

        async payWithRazorpay() {
            if (this.amount < {{ config('whatify.wallet.min_recharge') }}) {
                alert('Minimum recharge amount is ₹{{ config("whatify.wallet.min_recharge") }}');
                return;
            }

            this.loading = true;

            try {
                // Create order
                const res = await fetch('/payment/razorpay/create-order', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify({ amount: this.amount }),
                });

                const data = await res.json();

                if (!data.order_id) {
                    alert('Failed to create payment order');
                    this.loading = false;
                    return;
                }

                // Open Razorpay checkout
                const options = {
                    key: data.key,
                    amount: this.amount * 100,
                    currency: 'INR',
                    name: '{{ config("app.name") }}',
                    description: 'Wallet Recharge',
                    order_id: data.order_id,
                    handler: (response) => {
                        // Submit payment verification
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route("payment.razorpay.callback") }}';

                        const fields = {
                            '_token': '{{ csrf_token() }}',
                            'razorpay_order_id': response.razorpay_order_id,
                            'razorpay_payment_id': response.razorpay_payment_id,
                            'razorpay_signature': response.razorpay_signature,
                            'amount': this.amount,
                        };

                        for (const [key, value] of Object.entries(fields)) {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = key;
                            input.value = value;
                            form.appendChild(input);
                        }

                        document.body.appendChild(form);
                        form.submit();
                    },
                    prefill: {
                        name: '{{ auth()->user()->name }}',
                        email: '{{ auth()->user()->email }}',
                        contact: '{{ auth()->user()->phone }}',
                    },
                    theme: {
                        color: '#059669',
                    },
                    modal: {
                        ondismiss: () => {
                            this.loading = false;
                        }
                    }
                };

                const rzp = new Razorpay(options);
                rzp.open();

            } catch (e) {
                alert('Something went wrong. Please try again.');
                console.error(e);
                this.loading = false;
            }
        }
    }
}
</script>
@endpush
@endsection