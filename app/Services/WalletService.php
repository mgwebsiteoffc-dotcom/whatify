<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function createWallet(User $user): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0, 'currency' => 'INR']
        );
    }

    public function credit(
        User $user,
        float $amount,
        string $description,
        string $type = 'credit',
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $paymentGateway = null,
        ?string $paymentId = null,
        ?array $gatewayResponse = null
    ): WalletTransaction {
        return DB::transaction(function () use (
            $user, $amount, $description, $type,
            $referenceType, $referenceId, $paymentGateway,
            $paymentId, $gatewayResponse
        ) {
            $wallet = $user->wallet()->lockForUpdate()->first();

            if (!$wallet) {
                $wallet = $this->createWallet($user);
                $wallet = $wallet->fresh()->lockForUpdate()->first()
                    ?: Wallet::where('user_id', $user->id)->lockForUpdate()->first();
            }

            $balanceBefore = $wallet->balance;
            $wallet->balance += $amount;
            $wallet->total_recharged += ($type === 'credit') ? $amount : 0;
            $wallet->save();

            return WalletTransaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'type' => $type,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $wallet->balance,
                'description' => $description,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'status' => 'completed',
                'payment_gateway' => $paymentGateway,
                'payment_id' => $paymentId,
                'gateway_response' => $gatewayResponse,
            ]);
        });
    }

    public function debit(
        User $user,
        float $amount,
        string $description,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): ?WalletTransaction {
        return DB::transaction(function () use (
            $user, $amount, $description, $referenceType, $referenceId
        ) {
            $wallet = $user->wallet()->lockForUpdate()->first();

            if (!$wallet || $wallet->balance < $amount) {
                return null; // insufficient balance
            }

            $balanceBefore = $wallet->balance;
            $wallet->balance -= $amount;
            $wallet->total_spent += $amount;
            $wallet->save();

            $transaction = WalletTransaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'type' => 'debit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $wallet->balance,
                'description' => $description,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'status' => 'completed',
            ]);

            // Check low balance
            if ($wallet->isLowBalance()) {
                $this->sendLowBalanceAlert($user, $wallet);
            }

            return $transaction;
        });
    }

    public function getBalance(User $user): float
    {
        return $user->wallet?->balance ?? 0;
    }

    public function getTransactionHistory(User $user, int $perPage = 20)
    {
        return WalletTransaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    protected function sendLowBalanceAlert(User $user, Wallet $wallet): void
    {
        \App\Models\PlatformNotification::create([
            'user_id' => $user->id,
            'title' => 'Low Wallet Balance',
            'message' => "Your wallet balance is ₹{$wallet->balance}. Please recharge to continue sending messages.",
            'type' => 'wallet',
            'action_url' => route('wallet.recharge'),
        ]);

        // TODO: Send email notification
        // TODO: Send push notification to mobile app
    }

    public function getMessageCost(string $category): float
    {
        return config("whatify.message_cost.{$category}", 0.90);
    }
}