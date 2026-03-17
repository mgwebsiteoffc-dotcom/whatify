<?php

namespace App\Console\Commands;

use App\Models\Integration;
use App\Services\Integrations\IntegrationFactory;
use Illuminate\Console\Command;

class SyncIntegrations extends Command
{
    protected $signature = 'integrations:sync {--type= : Integration type} {--id= : Specific integration ID}';
    protected $description = 'Sync data from all active integrations';

    public function handle(): int
    {
        $query = Integration::where('status', 'active');

        if ($type = $this->option('type')) {
            $query->where('type', $type);
        }

        if ($id = $this->option('id')) {
            $query->where('id', $id);
        }

        $integrations = $query->get();

        $this->info("Syncing {$integrations->count()} integration(s)...");

        foreach ($integrations as $integration) {
            $this->line("  → {$integration->name} ({$integration->type})");

            try {
                $service = IntegrationFactory::fromIntegration($integration);

                if (!$service->testConnection()) {
                    $this->warn("    Connection failed. Skipping.");
                    $integration->update(['status' => 'error', 'error_message' => 'Connection test failed']);
                    continue;
                }

                if (method_exists($service, 'syncOrders') && ($integration->config['sync_orders'] ?? false)) {
                    $result = $service->syncOrders();
                    $this->line("Orders synced: " . ($result['synced'] ?? 0));
                }

                if (method_exists($service, 'syncCustomers') && ($integration->config['sync_customers'] ?? false)) {
                    $result = $service->syncCustomers();
                    $this->line("    Customers synced: " . ($result['synced'] ?? 0));
                }

                if ($integration->type === 'google_sheets' && ($integration->config['auto_sync'] ?? false)) {
                    $result = $service->importContacts();
                    $this->line("    Contacts imported: " . ($result['imported'] ?? 0));
                }

            } catch (\Exception $e) {
                $this->error("    Error: {$e->getMessage()}");
                $integration->update(['status' => 'error', 'error_message' => $e->getMessage()]);
            }
        }

        $this->info('Done!');
        return Command::SUCCESS;
    }
}