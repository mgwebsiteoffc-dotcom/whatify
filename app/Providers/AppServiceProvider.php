<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // API rate limiting
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // WhatsApp webhook rate limiting (high throughput)
        RateLimiter::for('webhook', function (Request $request) {
            return Limit::perMinute(500)->by($request->ip());
        });

        // Message sending rate limiting per user
        RateLimiter::for('send-message', function (Request $request) {
            return Limit::perMinute(100)->by($request->user()?->id ?: $request->ip());
        });

        // Wallet recharge rate limiting
        RateLimiter::for('recharge', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });

        // Register policies
        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\WhatsappAccount::class,
            \App\Policies\WhatsappAccountPolicy::class
        );
    }
}