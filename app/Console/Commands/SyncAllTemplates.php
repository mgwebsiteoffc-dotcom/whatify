<?php

namespace App\Console\Commands;

use App\Jobs\SyncTemplates;
use App\Models\WhatsappAccount;
use Illuminate\Console\Command;

class SyncAllTemplates extends Command
{
    protected $signature = 'whatsapp:sync-templates {--account= : Specific account ID}';
    protected $description = 'Sync message templates from WhatsApp for all connected accounts';

    public function handle(): int
    {
        $query = WhatsappAccount::where('status', 'connected');

        if ($accountId = $this->option('account')) {
            $query->where('id', $accountId);
        }

        $accounts = $query->get();

        $this->info("Syncing templates for {$accounts->count()} account(s)...");

        foreach ($accounts as $account) {
            SyncTemplates::dispatch($account);
            $this->line("  → Queued sync for: {$account->phone_number}");
        }

        $this->info('Done! Templates will sync in the background.');
        return Command::SUCCESS;
    }
}