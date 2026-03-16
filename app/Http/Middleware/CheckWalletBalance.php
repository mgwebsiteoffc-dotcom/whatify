<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckWalletBalance
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $owner = $user->getBusinessOwner() ?? $user;
        $wallet = $owner->wallet;

        if (!$wallet || $wallet->balance <= 0) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Insufficient wallet balance. Please recharge.',
                    'balance' => $wallet?->balance ?? 0,
                ], 402);
            }
            return redirect()->route('wallet.recharge')
                ->with('error', 'Insufficient wallet balance. Please recharge.');
        }

        return $next($request);
    }
}