<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TriggerAutomation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(
        public int $userId,
        public string $triggerType,
        public array $triggerData
    ) {}

    public function handle(): void
    {
        // This will be fully implemented in Phase 4
        // For now, log the trigger
        Log::channel('whatsapp')->info('Automation trigger received', [
            'user_id' => $this->userId,
            'trigger_type' => $this->triggerType,
            'data' => $this->triggerData,
        ]);
    }
}