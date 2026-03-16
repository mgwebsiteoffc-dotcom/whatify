<?php

namespace App\Jobs;

use App\Models\MessageTemplate;
use App\Models\WhatsappAccount;
use App\Services\WhatsappApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncTemplates implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        public WhatsappAccount $account
    ) {}

    public function handle(WhatsappApiService $whatsappApi): void
    {
        $result = $whatsappApi->getTemplates($this->account);

        if (!$result['success']) {
            Log::channel('whatsapp')->error('Template sync failed', [
                'account_id' => $this->account->id,
                'error' => $result['error'] ?? 'Unknown error',
            ]);
            return;
        }

        $remoteTemplates = $result['data'];
        $syncedIds = [];

        foreach ($remoteTemplates as $remote) {
            $template = MessageTemplate::updateOrCreate(
                [
                    'user_id' => $this->account->user_id,
                    'whatsapp_account_id' => $this->account->id,
                    'name' => $remote['name'],
                    'language' => $remote['language'] ?? 'en',
                ],
                [
                    'template_id_meta' => $remote['id'] ?? null,
                    'category' => strtolower($remote['category'] ?? 'utility'),
                    'status' => strtolower($remote['status'] ?? 'pending'),
                    'header' => $this->extractComponent($remote, 'HEADER'),
                    'body' => $this->extractBodyText($remote),
                    'footer' => $this->extractComponentText($remote, 'FOOTER'),
                    'buttons' => $this->extractButtons($remote),
                    'rejection_reason' => $remote['rejected_reason'] ?? null,
                ]
            );

            $syncedIds[] = $template->id;
        }

        Log::channel('whatsapp')->info('Templates synced', [
            'account_id' => $this->account->id,
            'count' => count($syncedIds),
        ]);
    }

    protected function extractComponent(array $template, string $type): ?array
    {
        $components = $template['components'] ?? [];
        foreach ($components as $component) {
            if (($component['type'] ?? '') === $type) {
                return $component;
            }
        }
        return null;
    }

    protected function extractBodyText(array $template): string
    {
        $components = $template['components'] ?? [];
        foreach ($components as $component) {
            if (($component['type'] ?? '') === 'BODY') {
                return $component['text'] ?? '';
            }
        }
        return '';
    }

    protected function extractComponentText(array $template, string $type): ?string
    {
        $component = $this->extractComponent($template, $type);
        return $component['text'] ?? null;
    }

    protected function extractButtons(array $template): ?array
    {
        $components = $template['components'] ?? [];
        foreach ($components as $component) {
            if (($component['type'] ?? '') === 'BUTTONS') {
                return $component['buttons'] ?? null;
            }
        }
        return null;
    }
}