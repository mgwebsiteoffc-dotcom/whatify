<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSubscription
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $owner = $user->getBusinessOwner() ?? $user;
        $subscription = $owner->getActiveSubscription();

        if (!$subscription) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'No active subscription'], 403);
            }
            return redirect()->route('billing.plans')
                ->with('error', 'Please subscribe to a plan to continue.');
        }

        return $next($request);
    }
}