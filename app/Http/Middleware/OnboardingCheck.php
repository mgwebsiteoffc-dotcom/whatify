<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OnboardingCheck
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = auth()->user();

        if (!$user) {
            return $next($request);
        }

        // Super admins skip onboarding
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Partners skip onboarding (they don't need business profile)
        if ($user->role === 'partner' && !$user->business) {
            return $next($request);
        }

        // Allow onboarding and partner routes always
        if ($request->routeIs('onboarding.*') ||
            $request->routeIs('logout') ||
            $request->routeIs('partner.*') ||
            $request->routeIs('account.*') ||
            $request->routeIs('notifications.*')) {
            return $next($request);
        }

        // Check if onboarding is complete
        if (!$user->is_onboarded) {
            return redirect()->route('onboarding.index');
        }

        return $next($request);
    }
}