<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function __construct(protected SubscriptionService $subscriptionService) {}

    public function plans()
    {
        return view('billing.plans');
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $plan = Plan::findOrFail($request->plan_id);
        $user = auth()->user();

        // For trial upgrade, immediately switch
        // For paid plans, initiate payment flow
        $currentSub = $user->getActiveSubscription();

        if ($currentSub && $currentSub->isTrial()) {
            // Upgrade from trial - redirect to payment
            return redirect()->route('wallet.recharge')
                ->with('info', "Please recharge your wallet and then subscribe to {$plan->name} plan.");
        }

        // Simple plan switch for now
        $this->subscriptionService->subscribe($user, $plan);

        return redirect()->route('dashboard')
            ->with('success', "Successfully subscribed to {$plan->name} plan!");
    }

    public function invoices()
    {
        $transactions = auth()->user()->walletTransactions()
            ->where('type', 'credit')
            ->where('reference_type', 'recharge')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('billing.invoices', compact('transactions'));
    }
}