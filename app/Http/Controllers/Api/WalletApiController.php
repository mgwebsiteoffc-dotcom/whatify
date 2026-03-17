<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WalletApiController extends Controller
{
    public function index(Request $request)
    {
        $owner = $request->user()->getBusinessOwner() ?? $request->user();

        $wallet = $owner->wallet;
        $recentTransactions = $owner->walletTransactions()
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'wallet' => $wallet,
            'recent_transactions' => $recentTransactions,
            'message_costs' => config('whatify.message_cost'),
        ]);
    }
}