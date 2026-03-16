<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OnboardingCheck
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = auth()->user();

        if ($user && !$user->is_onboarded && !$user->isSuperAdmin()) {
            // Allow onboarding routes
            if ($request->routeIs('onboarding.*') || $request->routeIs('logout')) {
                return $next($request);
            }

            return redirect()->route('onboarding.index');
        }

        return $next($request);
    }
}