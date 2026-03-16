<?php

namespace App\Providers;
use App\Models\WhatsappAccount;
use App\Policies\WhatsappAccountPolicy;
use Illuminate\Support\Facades\Gate;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    // In AppServiceProvider boot():
Gate::policy(WhatsappAccount::class, WhatsappAccountPolicy::class);    
    //
    }
}
