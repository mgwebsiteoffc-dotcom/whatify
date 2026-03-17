<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RazorpayController extends Controller
{
    public function __construct(
        protected WalletService $walletService
    ) {}

    /**
     * Create Razorpay order for wallet recharge
     */
    public function createOrder(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:100|max:100000',
        ]);

        $amountInPaise = $validated['amount'] * 100;

        try {
            $response = Http::withBasicAuth(config('services.razorpay.key'), config('services.razorpay.secret'))
                ->post('https://api.razorpay.com/v1/orders', [
                    'amount' => $amountInPaise,
                    'currency' => 'INR',
                    'receipt' => 'wfy_' . auth()->id() . '_' . time(),
                    'notes' => [
                        'user_id' => auth()->id(),
                        'purpose' => 'wallet_recharge',
                    ],
                ]);

            if ($response->successful()) {
                return response()->json([
                    'order_id' => $response->json('id'),
                    'amount' => $validated['amount'],
                    'key' => config('services.razorpay.key'),
                ]);
            }

            return response()->json(['error' => 'Failed to create order'], 500);
        } catch (\Exception $e) {
            Log::error('Razorpay order creation failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Payment service unavailable'], 500);
        }
    }

    /**
     * Verify payment and credit wallet
     */
    public function callback(Request $request)
    {
        $validated = $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
            'amount' => 'required|numeric',
        ]);

        // Verify signature
        $generatedSignature = hash_hmac(
            'sha256',
            $validated['razorpay_order_id'] . '|' . $validated['razorpay_payment_id'],
            config('services.razorpay.secret')
        );

        if ($generatedSignature !== $validated['razorpay_signature']) {
            Log::warning('Razorpay signature mismatch', $validated);
            return redirect()->route('wallet.index')->with('error', 'Payment verification failed.');
        }

        // Credit wallet
        $user = auth()->user();
        $amount = $validated['amount'];

        $this->walletService->credit(
            $user,
            $amount,
            "Wallet recharge via Razorpay",
            'credit',
            'recharge',
            null,
            'razorpay',
            $validated['razorpay_payment_id'],
            [
                'order_id' => $validated['razorpay_order_id'],
                'payment_id' => $validated['razorpay_payment_id'],
            ]
        );

        // Track partner commission
        if ($user->partner_id) {
            $this->trackPartnerCommission($user, $amount);
        }

        \App\Services\ActivityLogger::log('wallet_recharged', 'Wallet', $user->wallet?->id, null, [
            'amount' => $amount,
            'gateway' => 'razorpay',
        ]);

        return redirect()->route('wallet.index')->with('success', "₹{$amount} added to your wallet!");
    }

    /**
     * Razorpay webhook for payment confirmation
     */
    public function webhook(Request $request)
    {
        $payload = $request->all();
        $webhookSignature = $request->header('X-Razorpay-Signature');

        // Verify webhook signature
        $expectedSignature = hash_hmac(
            'sha256',
            $request->getContent(),
            config('services.razorpay.secret') // Use webhook secret in production
        );

        if (!hash_equals($expectedSignature, $webhookSignature ?? '')) {
            Log::warning('Razorpay webhook signature mismatch');
            return response()->json(['status' => 'invalid signature'], 400);
        }

        $event = $payload['event'] ?? '';

        Log::info('Razorpay webhook', ['event' => $event]);

        if ($event === 'payment.captured') {
            // Payment confirmed
            $payment = $payload['payload']['payment']['entity'] ?? [];
            $userId = $payment['notes']['user_id'] ?? null;

            // Already handled in callback, but this is a fallback
            Log::info('Razorpay payment captured via webhook', [
                'payment_id' => $payment['id'] ?? null,
                'user_id' => $userId,
            ]);
        }

        return response()->json(['status' => 'ok']);
    }

    protected function trackPartnerCommission($user, float $amount): void
    {
        $partner = \App\Models\Partner::where('user_id', $user->partner_id)
            ->where('status', 'approved')
            ->first();

        if (!$partner) return;

        $commission = ($amount * $partner->commission_rate) / 100;

        \App\Models\PartnerCommission::create([
            'partner_id' => $partner->id,
            'user_id' => $user->id,
            'event' => 'recharge',
            'amount' => $amount,
            'commission' => $commission,
            'status' => 'pending',
        ]);

        $partner->increment('pending_payout', $commission);
        $partner->increment('total_earned', $commission);
    }
}