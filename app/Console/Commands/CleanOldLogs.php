<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanOldLogs extends Command
{
    protected $signature = 'logs:clean {--days=30 : Days to keep}';
    protected $description = 'Clean old automation logs and integration logs';

    public function handle(): int
    {
        $days = $this->option('days');
        $cutoff = now()->subDays($days);

        $automationLogs = \App\Models\AutomationLog::where('created_at', '<', $cutoff)->delete();
        $integrationLogs = \App\Models\IntegrationLog::where('created_at', '<', $cutoff)->delete();
        $activityLogs = \App\Models\ActivityLog::where('created_at', '<', $cutoff)->delete();

        $this->info("Cleaned: {$automationLogs} automation logs, {$integrationLogs} integration logs, {$activityLogs} activity logs.");
        return Command::SUCCESS;
    }
}