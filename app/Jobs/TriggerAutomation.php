<?php

namespace App\Jobs;

use App\Models\AutomationLog;
use App\Models\Contact;
use App\Models\Conversation;
use App\Services\AutomationEngine;
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
    public int $timeout = 120;

    public function __construct(
        public int $userId,
        public string $triggerType,
        public array $triggerData,
    ) {}

    public function handle(AutomationEngine $engine): void
    {
        $contactId = $this->triggerData['contact_id'] ?? null;
        $conversationId = $this->triggerData['conversation_id'] ?? null;
        $content = $this->triggerData['content'] ?? '';

        if (!$contactId) return;

        $contact = Contact::find($contactId);
        if (!$contact) return;

        $conversation = $conversationId ? Conversation::find($conversationId) : null;

        // Check if bot is active for this conversation
        if ($conversation && !$conversation->is_bot_active) {
            if ($conversation->bot_paused_until && $conversation->bot_paused_until->isPast()) {
                $conversation->update(['is_bot_active' => true, 'bot_paused_until' => null]);
            } else {
                Log::channel('whatsapp')->debug('Bot paused for conversation', ['conversation_id' => $conversationId]);
                return;
            }
        }

        // Check for active paused automation waiting for response
        $pausedLog = AutomationLog::where('contact_id', $contactId)
            ->where('status', 'paused')
            ->latest()
            ->first();

        if ($pausedLog) {
            $engine->handleResponse($pausedLog, $content);
            return;
        }

        // Check keyword trigger first
        $keywordAutomations = $engine->findMatchingAutomations($this->userId, 'keyword', $this->triggerData);

        if ($keywordAutomations->isNotEmpty()) {
            foreach ($keywordAutomations->take(1) as $automation) {
                $engine->executeAutomation(
                    $automation,
                    $contact,
                    $conversation,
                    ['trigger_message' => $content]
                );
            }
            return;
        }

        // Check general incoming message automations
        $automations = $engine->findMatchingAutomations($this->userId, $this->triggerType, $this->triggerData);

        foreach ($automations->take(1) as $automation) {
            $engine->executeAutomation(
                $automation,
                $contact,
                $conversation,
                ['trigger_message' => $content]
            );
        }
    }
}