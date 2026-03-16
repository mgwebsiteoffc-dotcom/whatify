<?php

namespace App\Http\Controllers;

use App\Services\WalletService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(protected WalletService $walletService) {}

    public function index()
    {
        $user = auth()->user();
        $wallet = $user->wallet ?? $this->walletService->createWallet($user);
        $recentTransactions = $this->walletService->getTransactionHistory($user, 10);

        return view('wallet.index', compact('wallet', 'recentTransactions'));
    }

    public function recharge()
    {
        $wallet = auth()->user()->wallet;
        return view('wallet.recharge', compact('wallet'));
    }

    public function processRecharge(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:' . config('whatify.wallet.min_recharge') . '|max:' . config('whatify.wallet.max_recharge'),
            'payment_gateway' => 'required|in:razorpay,cashfree,stripe',
        ]);

        // Create payment order based on gateway
        // This will be fully implemented in Phase 3
        $orderId = 'WFY_' . time() . '_' . auth()->id();

        return view('wallet.payment', [
            'amount' => $validated['amount'],
            'gateway' => $validated['payment_gateway'],
            'order_id' => $orderId,
        ]);
    }

    public function transactions()
    {
        $transactions = $this->walletService->getTransactionHistory(auth()->user(), 25);
        return view('wallet.transactions', compact('transactions'));
    }
}