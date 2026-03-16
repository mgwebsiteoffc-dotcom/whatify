<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckFeatureLimit
{
    public function handle(Request $request, Closure $next, string $feature): mixed
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $owner = $user->getBusinessOwner() ?? $user;

        if (!$owner->canUseFeature($feature)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => "You've reached the limit for {$feature}. Please upgrade your plan.",
                    'feature' => $feature,
                    'upgrade_required' => true,
                ], 403);
            }
            return back()->with('error', "You've reached the limit for {$feature}. Please upgrade your plan.");
        }

        return $next($request);
    }
}