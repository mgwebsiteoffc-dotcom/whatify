<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('whatsapp:sync-templates')->twiceDaily(6, 18);
Schedule::command('wallet:check-balances')->hourly();
Schedule::command('logs:clean --days=30')->daily();
Schedule::command('queue:prune-failed --hours=168')->daily();

// routes/console.php — add:
Schedule::command('queue:work redis --queue=whatsapp --stop-when-empty')->everyMinute()->withoutOverlapping();

// Or use a job:
Schedule::job(new \App\Jobs\ProcessScheduledCampaigns)->everyMinute();
Schedule::command('integrations:sync')->hourly();
