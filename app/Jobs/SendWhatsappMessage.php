<?php

namespace App\Jobs;

use App\Models\Message;
use App\Services\MessageService;
use App\Services\WalletService;
use App\Services\WhatsappApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsappMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 5;
    public int $timeout = 60;

    public function __construct(
        public Message $message
    ) {}

    public function handle(WhatsappApiService $whatsappApi, WalletService $walletService): void
    {
        $message = $this->message->fresh();

        if (!$message || $message->status !== 'queued') {
            return;
        }

        $account = $message->whatsappAccount;
        $contact = $message->contact;

        if (!$account || !$account->isConnected()) {
            $this->failMessage($message, 'ACCOUNT_ERROR', 'WhatsApp account not connected');
            return;
        }

        $phone = $contact->country_code . $contact->phone;

        // Debit wallet before sending
        if ($message->cost > 0) {
            $transaction = $walletService->debit(
                $message->user,
                $message->cost,
                "Message to {$contact->phone}",
                'message',
                $message->id
            );

            if (!$transaction) {
                $this->failMessage($message, 'INSUFFICIENT_BALANCE', 'Wallet balance too low');
                return;
            }
        }

        // Send based on message type
        $result = match ($message->type) {
            'text' => $whatsappApi->sendTextMessage($account, $phone, $message->content),

            'template' => $this->sendTemplateMessage($whatsappApi, $account, $phone, $message),

            'image' => $whatsappApi->sendImageMessage(
                $account, $phone,
                $message->media['url'] ?? '',
                $message->content
            ),

            'video' => $whatsappApi->sendVideoMessage(
                $account, $phone,
                $message->media['url'] ?? '',
                $message->content
            ),

            'document' => $whatsappApi->sendDocumentMessage(
                $account, $phone,
                $message->media['url'] ?? '',
                $message->content,
                $message->media['filename'] ?? null
            ),

            'audio' => $whatsappApi->sendAudioMessage(
                $account, $phone,
                $message->media['url'] ?? ''
            ),

            'location' => $whatsappApi->sendLocationMessage(
                $account, $phone,
                $message->location_data['latitude'] ?? 0,
                $message->location_data['longitude'] ?? 0,
                $message->location_data['name'] ?? null,
                $message->location_data['address'] ?? null
            ),

            'interactive' => $this->sendInteractiveMessage($whatsappApi, $account, $phone, $message),

            default => ['success' => false, 'error_message' => "Unsupported message type: {$message->type}"],
        };

        if ($result['success']) {
            $message->update([
                'wamid' => $result['wamid'] ?? null,
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            // Update conversation
            $message->conversation?->update([
                'last_message' => substr($message->content ?? 'Media', 0, 255),
                'last_message_at' => now(),
            ]);
        } else {
            $this->failMessage(
                $message,
                $result['error_code'] ?? 'SEND_FAILED',
                $result['error_message'] ?? 'Failed to send message'
            );

            // Refund wallet
            if ($message->cost > 0) {
                $walletService->credit(
                    $message->user,
                    $message->cost,
                    "Refund: Failed message to {$contact->phone}",
                    'refund',
                    'message',
                    $message->id
                );
            }
        }
    }

    protected function sendTemplateMessage(WhatsappApiService $api, $account, string $phone, Message $message): array
    {
        $templateData = $message->template_data ?? [];

        return $api->sendTemplateMessage(
            $account,
            $phone,
            $templateData['name'] ?? '',
            $templateData['language'] ?? 'en',
            $templateData['header_params'] ?? [],
            $templateData['body_params'] ?? [],
            $templateData['button_params'] ?? [],
            $templateData['header_media'] ?? null
        );
    }

    protected function sendInteractiveMessage(WhatsappApiService $api, $account, string $phone, Message $message): array
    {
        $data = $message->interactive_data ?? [];
        $interactiveType = $data['type'] ?? 'button';

        if ($interactiveType === 'button') {
            return $api->sendButtonMessage(
                $account, $phone,
                $data['body'] ?? '',
                $data['buttons'] ?? [],
                $data['header'] ?? null,
                $data['footer'] ?? null
            );
        }

        if ($interactiveType === 'list') {
            return $api->sendListMessage(
                $account, $phone,
                $data['body'] ?? '',
                $data['button_text'] ?? 'Menu',
                $data['sections'] ?? [],
                $data['header'] ?? null,
                $data['footer'] ?? null
            );
        }

        return ['success' => false, 'error_message' => 'Unknown interactive type'];
    }

    protected function failMessage(Message $message, string $errorCode, string $errorMessage): void
    {
        $message->update([
            'status' => 'failed',
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
            'failed_at' => now(),
        ]);

        Log::channel('whatsapp')->error('Message send failed', [
            'message_id' => $message->id,
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        $this->message->update([
            'status' => 'failed',
            'error_code' => 'JOB_FAILED',
            'error_message' => substr($exception->getMessage(), 0, 255),
            'failed_at' => now(),
        ]);
    }
}