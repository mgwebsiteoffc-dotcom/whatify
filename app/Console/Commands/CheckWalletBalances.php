<?php

namespace App\Console\Commands;

use App\Models\Wallet;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class CheckWalletBalances extends Command
{
    protected $signature = 'wallet:check-balances';
    protected $description = 'Send low balance alerts to users';

    public function handle(NotificationService $notificationService): int
    {
        $threshold = config('whatify.wallet.low_balance_alert');

        $lowBalanceWallets = Wallet::where('balance', '<=', $threshold)
            ->where('balance', '>', 0)
            ->with('user')
            ->get();

        foreach ($lowBalanceWallets as $wallet) {
            $notificationService->send(
                $wallet->user,
                'Low Wallet Balance',
                "Your wallet balance is ₹{$wallet->balance}. Recharge now to avoid message delivery issues.",
                'wallet',
                route('wallet.recharge')
            );
        }

        $this->info("Sent {$lowBalanceWallets->count()} low balance alerts.");
        return Command::SUCCESS;
    }
}