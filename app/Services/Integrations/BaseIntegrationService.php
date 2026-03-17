<?php

namespace App\Services\Integrations;

use App\Models\Integration;
use App\Models\IntegrationLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;

abstract class BaseIntegrationService
{
    protected Integration $integration;
    protected User $user;

    public function setIntegration(Integration $integration): static
    {
        $this->integration = $integration;
        $this->user = $integration->user;
        return $this;
    }

    abstract public function connect(array $config): bool;
    abstract public function disconnect(): bool;
    abstract public function testConnection(): bool;
    abstract public function getType(): string;

    protected function logEvent(string $event, ?array $payload = null, ?array $response = null, string $status = 'success', ?string $error = null): void
    {
        IntegrationLog::create([
            'integration_id' => $this->integration->id,
            'event' => $event,
            'payload' => $payload,
            'response' => $response,
            'status' => $status,
            'error_message' => $error,
        ]);
    }

    protected function updateStatus(string $status, ?string $error = null): void
    {
        $this->integration->update([
            'status' => $status,
            'error_message' => $error,
            'last_synced_at' => now(),
        ]);
    }

    protected function getConfig(string $key = null, $default = null): mixed
    {
        $config = $this->integration->config ?? [];

        if ($key === null) return $config;

        return $config[$key] ?? $default;
    }

    protected function updateConfig(array $newConfig): void
    {
        $config = $this->integration->config ?? [];
        $this->integration->update([
            'config' => array_merge($config, $newConfig),
        ]);
    }
}