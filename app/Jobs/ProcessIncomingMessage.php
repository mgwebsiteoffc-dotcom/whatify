<?php

namespace App\Jobs;

use App\Models\WhatsappAccount;
use App\Services\MessageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessIncomingMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        public int $whatsappAccountId,
        public string $from,
        public string $wamid,
        public string $messageType,
        public array $messageData,
        public int $timestamp
    ) {}

    public function handle(MessageService $messageService): void
    {
        $account = WhatsappAccount::find($this->whatsappAccountId);

        if (!$account) {
            Log::channel('whatsapp')->error('Account not found for incoming message', [
                'account_id' => $this->whatsappAccountId,
            ]);
            return;
        }

        try {
            $message = $messageService->processIncoming(
                $account,
                $this->from,
                $this->wamid,
                $this->messageType,
                $this->messageData,
                $this->timestamp
            );

            // Dispatch automation trigger
            \App\Jobs\TriggerAutomation::dispatch(
                $account->user_id,
                'incoming_message',
                [
                    'message_id' => $message->id,
                    'contact_id' => $message->contact_id,
                    'conversation_id' => $message->conversation_id,
                    'message_type' => $this->messageType,
                    'content' => $message->content,
                ]
            )->onQueue('automations');

        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('Failed to process incoming message', [
                'error' => $e->getMessage(),
                'from' => $this->from,
                'wamid' => $this->wamid,
            ]);
            throw $e;
        }
    }
}