<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessIncomingMessage;
use App\Models\WhatsappAccount;
use App\Services\MessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsappWebhookController extends Controller
{
    public function __construct(
        protected MessageService $messageService
    ) {}

    // ──────────────────────────────────
    // WEBHOOK VERIFICATION (GET)
    // ──────────────────────────────────
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        Log::channel('whatsapp')->info('Webhook verification attempt', [
            'mode' => $mode,
            'token' => $token,
        ]);

        if ($mode === 'subscribe' && $token === config('whatify.whatsapp.verify_token')) {
            Log::channel('whatsapp')->info('Webhook verified successfully');
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        Log::channel('whatsapp')->warning('Webhook verification failed', [
            'expected_token' => config('whatify.whatsapp.verify_token'),
            'received_token' => $token,
        ]);

        return response('Forbidden', 403);
    }

    // ──────────────────────────────────
    // WEBHOOK HANDLER (POST)
    // ──────────────────────────────────
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::channel('whatsapp')->debug('Webhook received', [
            'payload' => $payload,
        ]);

        // Validate payload structure
        if (!isset($payload['entry'])) {
            return response()->json(['status' => 'ok']);
        }

        try {
            foreach ($payload['entry'] as $entry) {
                $changes = $entry['changes'] ?? [];

                foreach ($changes as $change) {
                    $field = $change['field'] ?? '';

                    if ($field !== 'messages') {
                        continue;
                    }

                    $value = $change['value'] ?? [];
                    $this->processWebhookValue($value);
                }
            }
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('Webhook processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        // Always return 200 to acknowledge receipt
        return response()->json(['status' => 'ok']);
    }

    // ──────────────────────────────────
    // PROCESS WEBHOOK VALUE
    // ──────────────────────────────────
    protected function processWebhookValue(array $value): void
    {
        $metadata = $value['metadata'] ?? [];
        $phoneNumberId = $metadata['phone_number_id'] ?? null;

        if (!$phoneNumberId) {
            Log::channel('whatsapp')->warning('No phone_number_id in webhook');
            return;
        }

        // Find account by phone_number_id
        $account = WhatsappAccount::where('phone_number_id', $phoneNumberId)
            ->where('status', 'connected')
            ->first();

        if (!$account) {
            Log::channel('whatsapp')->warning('Account not found for phone_number_id', [
                'phone_number_id' => $phoneNumberId,
            ]);
            return;
        }

        // Process incoming messages
        $messages = $value['messages'] ?? [];
        foreach ($messages as $messageData) {
            $this->processMessage($account, $messageData);
        }

        // Process status updates
        $statuses = $value['statuses'] ?? [];
        foreach ($statuses as $statusData) {
            $this->processStatus($statusData);
        }

        // Process errors
        $errors = $value['errors'] ?? [];
        foreach ($errors as $error) {
            $this->processError($account, $error);
        }
    }

    // ──────────────────────────────────
    // PROCESS INCOMING MESSAGE
    // ──────────────────────────────────
    protected function processMessage(WhatsappAccount $account, array $messageData): void
    {
        $from = $messageData['from'] ?? null;
        $wamid = $messageData['id'] ?? null;
        $type = $messageData['type'] ?? 'text';
        $timestamp = (int) ($messageData['timestamp'] ?? time());

        if (!$from || !$wamid) {
            Log::channel('whatsapp')->warning('Invalid message data', $messageData);
            return;
        }

        // Check for duplicate
        if (\App\Models\Message::where('wamid', $wamid)->exists()) {
            Log::channel('whatsapp')->debug('Duplicate message ignored', ['wamid' => $wamid]);
            return;
        }

        Log::channel('whatsapp')->info('Incoming message', [
            'from' => $from,
            'type' => $type,
            'wamid' => $wamid,
            'account_id' => $account->id,
        ]);

        // Dispatch to queue for processing
        ProcessIncomingMessage::dispatch(
            $account->id,
            $from,
            $wamid,
            $type,
            $messageData,
            $timestamp
        )->onQueue('webhooks');
    }

    // ──────────────────────────────────
    // PROCESS STATUS UPDATE
    // ──────────────────────────────────
    protected function processStatus(array $statusData): void
    {
        $wamid = $statusData['id'] ?? null;
        $status = $statusData['status'] ?? null;
        $timestamp = (int) ($statusData['timestamp'] ?? time());
        $recipientId = $statusData['recipient_id'] ?? null;

        if (!$wamid || !$status) return;

        Log::channel('whatsapp')->debug('Status update', [
            'wamid' => $wamid,
            'status' => $status,
            'recipient' => $recipientId,
        ]);

        // Handle errors in status
        if ($status === 'failed') {
            $errors = $statusData['errors'] ?? [];
            $errorCode = $errors[0]['code'] ?? 'UNKNOWN';
            $errorTitle = $errors[0]['title'] ?? 'Message failed';

            $message = \App\Models\Message::where('wamid', $wamid)->first();
            if ($message) {
                app(MessageService::class)->handleMessageFailure($message, $errorCode, $errorTitle);
            }
            return;
        }

        $this->messageService->updateMessageStatus($wamid, $status, $timestamp);
    }

    // ──────────────────────────────────
    // PROCESS ERROR
    // ──────────────────────────────────
    protected function processError(WhatsappAccount $account, array $error): void
    {
        $errorCode = $error['code'] ?? 'UNKNOWN';
        $errorTitle = $error['title'] ?? 'Unknown error';
        $errorMessage = $error['message'] ?? '';

        Log::channel('whatsapp')->error('Webhook error', [
            'account_id' => $account->id,
            'code' => $errorCode,
            'title' => $errorTitle,
            'message' => $errorMessage,
        ]);

        // Notify business owner of critical errors
        if (in_array($errorCode, [131031, 131047, 131051])) {
            app(NotificationService::class)->send(
                $account->user,
                'WhatsApp Error',
                "Error {$errorCode}: {$errorTitle}",
                'error'
            );
        }
    }
}