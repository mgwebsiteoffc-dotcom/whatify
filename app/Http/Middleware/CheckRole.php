<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): mixed
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $userRole = auth()->user()->role;

        // Super admin can access everything
        if ($userRole === 'super_admin') {
            return $next($request);
        }

        if (!in_array($userRole, $roles)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Redirect to appropriate dashboard based on role
            return match ($userRole) {
                'super_admin' => redirect()->route('admin.dashboard'),
                'partner' => redirect()->route('partner.dashboard'),
                'team_agent' => redirect()->route('inbox.index'),
                default => redirect()->route('dashboard'),
            };
        }

        return $next($request);
    }
}