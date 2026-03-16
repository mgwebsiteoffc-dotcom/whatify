<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;

class SubscriptionService
{
    public function createTrial(User $user, Plan $plan, int $trialDays = 14): Subscription
    {
        return Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'trial',
            'starts_at' => now(),
            'ends_at' => now()->addDays($trialDays),
            'trial_ends_at' => now()->addDays($trialDays),
            'plan_snapshot' => $plan->toArray(),
        ]);
    }

    public function subscribe(User $user, Plan $plan, string $paymentReference = null): Subscription
    {
        // Cancel existing subscription
        $this->cancelExisting($user);

        $cycle = $plan->billing_cycle === 'yearly' ? 365 : 30;

        return Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addDays($cycle),
            'amount_paid' => $plan->price,
            'payment_reference' => $paymentReference,
            'plan_snapshot' => $plan->toArray(),
        ]);
    }

    public function cancelExisting(User $user): void
    {
        $user->subscriptions()
            ->whereIn('status', ['active', 'trial'])
            ->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);
    }

    public function isActive(User $user): bool
    {
        return $user->subscriptions()
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->exists();
    }

    public function getCurrentPlan(User $user): ?Plan
    {
        $subscription = $user->getActiveSubscription();
        return $subscription?->plan;
    }
}