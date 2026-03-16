<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TrackLastLogin
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (auth()->check()) {
            $user = auth()->user();
            if (!$user->last_login_at || $user->last_login_at->diffInMinutes(now()) > 30) {
                $user->update([
                    'last_login_at' => now(),
                    'last_login_ip' => $request->ip(),
                ]);
            }
        }

        return $next($request);
    }
}