<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageTemplate;
use App\Models\User;
use App\Models\WhatsappAccount;
use App\Jobs\SendWhatsappMessage;
use App\Jobs\ProcessIncomingMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MessageService
{
    public function __construct(
        protected WhatsappApiService $whatsappApi,
        protected WalletService $walletService,
        protected NotificationService $notificationService,
    ) {}

    // ──────────────────────────────────
    // SEND MESSAGE (QUEUED)
    // ──────────────────────────────────
    public function sendMessage(
        User $user,
        WhatsappAccount $account,
        Contact $contact,
        string $type,
        array $content,
        ?int $campaignId = null,
        ?int $templateId = null,
        ?string $sentBy = null,
        bool $isBotResponse = false
    ): ?Message {
        // Get or create conversation
        $conversation = $this->getOrCreateConversation($user, $contact, $account);

        // Calculate cost
        $messageCategory = $this->determineMessageCategory($type, $templateId ? MessageTemplate::find($templateId) : null);
        $cost = $this->walletService->getMessageCost($messageCategory);

        // Check wallet balance
        if ($cost > 0 && !$user->wallet?->hasBalance($cost)) {
            Log::warning('Insufficient wallet balance for message', [
                'user_id' => $user->id,
                'balance' => $user->wallet?->balance ?? 0,
                'cost' => $cost,
            ]);
            return null;
        }

        // Create message record
        $message = Message::create([
            'user_id' => $user->id,
            'conversation_id' => $conversation->id,
            'contact_id' => $contact->id,
            'whatsapp_account_id' => $account->id,
            'campaign_id' => $campaignId,
            'template_id' => $templateId,
            'direction' => 'outbound',
            'type' => $type,
            'content' => $content['text'] ?? $content['caption'] ?? null,
            'media' => $content['media'] ?? null,
            'template_data' => $content['template_data'] ?? null,
            'interactive_data' => $content['interactive_data'] ?? null,
            'location_data' => $content['location_data'] ?? null,
            'status' => 'queued',
            'cost' => $cost,
            'message_category' => $messageCategory,
            'is_bot_response' => $isBotResponse,
            'sent_by' => $sentBy ?? auth()->user()?->name ?? 'system',
        ]);

        // Dispatch to queue
        SendWhatsappMessage::dispatch($message)->onQueue('whatsapp');

        return $message;
    }

    // ──────────────────────────────────
    // SEND TEXT (SHORTHAND)
    // ──────────────────────────────────
    public function sendText(
        User $user,
        WhatsappAccount $account,
        Contact $contact,
        string $text,
        ?string $sentBy = null
    ): ?Message {
        return $this->sendMessage($user, $account, $contact, 'text', [
            'text' => $text,
        ], sentBy: $sentBy);
    }

    // ──────────────────────────────────
    // SEND TEMPLATE (SHORTHAND)
    // ──────────────────────────────────
    public function sendTemplate(
        User $user,
        WhatsappAccount $account,
        Contact $contact,
        MessageTemplate $template,
        array $bodyParams = [],
        array $headerParams = [],
        array $buttonParams = [],
        ?array $headerMedia = null,
        ?int $campaignId = null
    ): ?Message {
        return $this->sendMessage($user, $account, $contact, 'template', [
            'text' => $template->body,
            'template_data' => [
                'name' => $template->name,
                'language' => $template->language,
                'body_params' => $bodyParams,
                'header_params' => $headerParams,
                'button_params' => $buttonParams,
                'header_media' => $headerMedia,
            ],
        ], campaignId: $campaignId, templateId: $template->id);
    }

    // ──────────────────────────────────
    // SEND MEDIA (SHORTHAND)
    // ──────────────────────────────────
    public function sendMedia(
        User $user,
        WhatsappAccount $account,
        Contact $contact,
        string $mediaType,
        string $mediaUrl,
        ?string $caption = null,
        ?string $filename = null
    ): ?Message {
        return $this->sendMessage($user, $account, $contact, $mediaType, [
            'caption' => $caption,
            'media' => [
                'type' => $mediaType,
                'url' => $mediaUrl,
                'filename' => $filename,
            ],
        ]);
    }

    // ──────────────────────────────────
    // SEND BUTTONS (SHORTHAND)
    // ──────────────────────────────────
    public function sendButtons(
        User $user,
        WhatsappAccount $account,
        Contact $contact,
        string $bodyText,
        array $buttons,
        ?string $headerText = null,
        ?string $footerText = null
    ): ?Message {
        return $this->sendMessage($user, $account, $contact, 'interactive', [
            'text' => $bodyText,
            'interactive_data' => [
                'type' => 'button',
                'header' => $headerText,
                'body' => $bodyText,
                'footer' => $footerText,
                'buttons' => $buttons,
            ],
        ]);
    }

    // ──────────────────────────────────
    // SEND LIST (SHORTHAND)
    // ──────────────────────────────────
    public function sendList(
        User $user,
        WhatsappAccount $account,
        Contact $contact,
        string $bodyText,
        string $buttonText,
        array $sections,
        ?string $headerText = null,
        ?string $footerText = null
    ): ?Message {
        return $this->sendMessage($user, $account, $contact, 'interactive', [
            'text' => $bodyText,
            'interactive_data' => [
                'type' => 'list',
                'header' => $headerText,
                'body' => $bodyText,
                'footer' => $footerText,
                'button_text' => $buttonText,
                'sections' => $sections,
            ],
        ]);
    }

    // ──────────────────────────────────
    // PROCESS INCOMING MESSAGE
    // ──────────────────────────────────
    public function processIncoming(
        WhatsappAccount $account,
        string $from,
        string $wamid,
        string $type,
        array $messageData,
        int $timestamp
    ): Message {
        $user = $account->user;

        // Find or create contact
        $contact = $this->findOrCreateContact($user, $from);

        // Get or create conversation
        $conversation = $this->getOrCreateConversation($user, $contact, $account);

        // Extract content
        $content = $this->extractIncomingContent($type, $messageData);

        // Download media if needed
        $media = null;
        if (in_array($type, ['image', 'video', 'audio', 'document', 'sticker'])) {
            $mediaData = $messageData[$type] ?? [];
            $mediaId = $mediaData['id'] ?? null;

            if ($mediaId) {
                $mediaUrl = $this->whatsappApi->getMediaUrl($account, $mediaId);
                $localPath = $mediaUrl ? $this->whatsappApi->downloadMedia($account, $mediaUrl) : null;

                $media = [
                    'media_id' => $mediaId,
                    'mime_type' => $mediaData['mime_type'] ?? null,
                    'filename' => $mediaData['filename'] ?? null,
                    'sha256' => $mediaData['sha256'] ?? null,
                    'local_path' => $localPath,
                    'url' => $localPath ? asset('storage/' . $localPath) : null,
                ];
            }
        }

        // Create message
        $message = Message::create([
            'user_id' => $user->id,
            'conversation_id' => $conversation->id,
            'contact_id' => $contact->id,
            'whatsapp_account_id' => $account->id,
            'direction' => 'inbound',
            'type' => $type,
            'content' => $content,
            'media' => $media,
            'interactive_data' => in_array($type, ['interactive', 'button']) ? $messageData : null,
            'location_data' => $type === 'location' ? ($messageData['location'] ?? null) : null,
            'wamid' => $wamid,
            'status' => 'received',
            'cost' => 0,
            'message_category' => 'service',
        ]);

        // Update conversation
        $conversation->update([
            'last_message' => substr($content ?? 'Media message', 0, 255),
            'last_message_at' => now(),
            'status' => 'open',
        ]);

        // Update contact last message time
        $contact->update(['last_message_at' => now()]);

        // Mark message as read on WhatsApp
        $this->whatsappApi->markAsRead($account, $wamid);

        // Notify agents
        $this->notifyAgents($user, $conversation, $contact, $content);

        return $message;
    }

    // ──────────────────────────────────
    // UPDATE MESSAGE STATUS
    // ──────────────────────────────────
    public function updateMessageStatus(string $wamid, string $status, ?int $timestamp = null): void
    {
        $message = Message::where('wamid', $wamid)->first();

        if (!$message) return;

        $statusMap = [
            'sent' => ['status' => 'sent', 'field' => 'sent_at'],
            'delivered' => ['status' => 'delivered', 'field' => 'delivered_at'],
            'read' => ['status' => 'read', 'field' => 'read_at'],
            'failed' => ['status' => 'failed', 'field' => 'failed_at'],
        ];

        if (!isset($statusMap[$status])) return;

        $updateData = ['status' => $statusMap[$status]['status']];
        $updateData[$statusMap[$status]['field']] = $timestamp
            ? \Carbon\Carbon::createFromTimestamp($timestamp)
            : now();

        $message->update($updateData);

        // Update campaign contact status if campaign message
        if ($message->campaign_id) {
            \App\Models\CampaignContact::where('message_id', $message->id)
                ->update(['status' => $statusMap[$status]['status']]);

            // Update campaign counters
            $campaign = $message->campaign;
            if ($campaign) {
                $field = match($status) {
                    'sent' => 'sent_count',
                    'delivered' => 'delivered_count',
                    'read' => 'read_count',
                    'failed' => 'failed_count',
                    default => null,
                };
                if ($field) {
                    $campaign->increment($field);
                }
            }
        }
    }

    // ──────────────────────────────────
    // HANDLE MESSAGE FAILURE
    // ──────────────────────────────────
    public function handleMessageFailure(Message $message, string $errorCode, string $errorMessage): void
    {
        $message->update([
            'status' => 'failed',
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
            'failed_at' => now(),
        ]);

        // Refund wallet if message was charged
        if ($message->cost > 0) {
            $this->walletService->credit(
                $message->user,
                $message->cost,
                "Refund for failed message to {$message->contact->phone}",
                'refund',
                'message',
                $message->id
            );

            $message->update(['cost' => 0]);
        }

        Log::channel('whatsapp')->warning('Message failed', [
            'message_id' => $message->id,
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
        ]);
    }

    // ──────────────────────────────────
    // PRIVATE HELPERS
    // ──────────────────────────────────
    protected function getOrCreateConversation(User $user, Contact $contact, WhatsappAccount $account): Conversation
    {
        return Conversation::firstOrCreate(
            [
                'user_id' => $user->id,
                'contact_id' => $contact->id,
                'whatsapp_account_id' => $account->id,
            ],
            [
                'status' => 'open',
                'priority' => 'medium',
                'is_bot_active' => true,
            ]
        );
    }

    protected function findOrCreateContact(User $user, string $phone): Contact
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        $countryCode = '91';
        $localPhone = $phone;

        if (strlen($phone) > 10) {
            $countryCode = substr($phone, 0, strlen($phone) - 10);
            $localPhone = substr($phone, -10);
        }

        return Contact::firstOrCreate(
            ['user_id' => $user->id, 'phone' => $localPhone],
            [
                'country_code' => $countryCode,
                'status' => 'active',
                'source' => 'whatsapp',
                'opted_in_at' => now(),
            ]
        );
    }

    protected function extractIncomingContent(string $type, array $data): ?string
    {
        return match ($type) {
            'text' => $data['text']['body'] ?? null,
            'image' => $data['image']['caption'] ?? '[Image]',
            'video' => $data['video']['caption'] ?? '[Video]',
            'audio' => '[Audio]',
            'document' => $data['document']['filename'] ?? '[Document]',
            'sticker' => '[Sticker]',
            'location' => '[Location: ' . ($data['location']['latitude'] ?? '') . ',' . ($data['location']['longitude'] ?? '') . ']',
            'contacts' => '[Contact shared]',
            'interactive' => $this->extractInteractiveContent($data),
            'button' => $data['button']['text'] ?? $data['button']['payload'] ?? '[Button reply]',
            'reaction' => $data['reaction']['emoji'] ?? '[Reaction]',
            'order' => '[Order]',
            default => null,
        };
    }

    protected function extractInteractiveContent(array $data): string
    {
        if (isset($data['interactive']['button_reply'])) {
            return $data['interactive']['button_reply']['title'] ?? '[Button reply]';
        }
        if (isset($data['interactive']['list_reply'])) {
            return $data['interactive']['list_reply']['title'] ?? '[List reply]';
        }
        return '[Interactive reply]';
    }

    protected function determineMessageCategory(string $type, ?MessageTemplate $template): string
    {
        if ($template) {
            return $template->category;
        }

        // Session messages (within 24hr window) are free service messages
        // Template messages are billed by category
        // For now default to service (will be refined with conversation window check)
        return 'service';
    }

    protected function notifyAgents(User $businessOwner, Conversation $conversation, Contact $contact, ?string $content): void
    {
        $contactName = $contact->name ?? $contact->phone;
        $preview = $content ? substr($content, 0, 100) : 'New message received';

        // Notify assigned agent
        if ($conversation->assigned_agent_id) {
            $this->notificationService->send(
                User::find($conversation->assigned_agent_id),
                "New message from {$contactName}",
                $preview,
                'message',
                null, // will be inbox URL
                [
                    'conversation_id' => $conversation->id,
                    'contact_id' => $contact->id,
                    'contact_name' => $contactName,
                ]
            );
        }

        // Also notify business owner
        $this->notificationService->send(
            $businessOwner,
            "New message from {$contactName}",
            $preview,
            'message',
            null,
            [
                'conversation_id' => $conversation->id,
                'contact_id' => $contact->id,
            ]
        );
    }
}