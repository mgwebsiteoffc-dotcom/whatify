<?php

namespace App\Jobs;

use App\Models\Automation;
use App\Models\AutomationLog;
use App\Models\AutomationStep;
use App\Models\Contact;
use App\Models\Conversation;
use App\Services\AutomationEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ContinueAutomation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 120;

    public function __construct(
        public int $automationId,
        public int $stepId,
        public int $contactId,
        public ?int $conversationId,
        public int $logId,
    ) {}

    public function handle(AutomationEngine $engine): void
    {
        $automation = Automation::find($this->automationId);
        $step = AutomationStep::find($this->stepId);
        $contact = Contact::find($this->contactId);
        $conversation = $this->conversationId ? Conversation::find($this->conversationId) : null;
        $log = AutomationLog::find($this->logId);

        if (!$automation || !$step || !$contact || !$log) return;
        if ($log->status === 'completed' || $log->status === 'failed') return;

        $log->update(['status' => 'running']);
        $engine->executeStep($automation, $step, $contact, $conversation, $log);
    }
}