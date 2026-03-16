<?php

namespace App\Services;

use App\Models\WhatsappAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappApiService
{
    protected string $apiUrl;
    protected string $apiVersion;

    public function __construct()
    {
        $this->apiUrl = config('whatify.whatsapp.api_url');
        $this->apiVersion = config('whatify.whatsapp.api_version', 'v18.0');
    }

    // ──────────────────────────────────
    // SEND TEXT MESSAGE
    // ──────────────────────────────────
    public function sendTextMessage(WhatsappAccount $account, string $to, string $text): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $this->formatPhone($to),
            'type' => 'text',
            'text' => [
                'preview_url' => true,
                'body' => $text,
            ],
        ];

        return $this->sendRequest($account, $payload);
    }

    // ──────────────────────────────────
    // SEND TEMPLATE MESSAGE
    // ──────────────────────────────────
    public function sendTemplateMessage(
        WhatsappAccount $account,
        string $to,
        string $templateName,
        string $language = 'en',
        array $headerParams = [],
        array $bodyParams = [],
        array $buttonParams = [],
        ?array $headerMedia = null
    ): array {
        $components = [];

        // Header component
        if (!empty($headerParams) || $headerMedia) {
            $headerComponent = ['type' => 'header'];

            if ($headerMedia) {
                // Media header (image, video, document)
                $headerComponent['parameters'] = [
                    [
                        'type' => $headerMedia['type'], // image, video, document
                        $headerMedia['type'] => [
                            'link' => $headerMedia['url'],
                        ],
                    ],
                ];
            } else {
                $headerComponent['parameters'] = collect($headerParams)->map(function ($value) {
                    return ['type' => 'text', 'text' => $value];
                })->toArray();
            }

            $components[] = $headerComponent;
        }

        // Body component
        if (!empty($bodyParams)) {
            $components[] = [
                'type' => 'body',
                'parameters' => collect($bodyParams)->map(function ($value) {
                    return ['type' => 'text', 'text' => (string) $value];
                })->toArray(),
            ];
        }

        // Button components
        if (!empty($buttonParams)) {
            foreach ($buttonParams as $index => $param) {
                $buttonComponent = [
                    'type' => 'button',
                    'sub_type' => $param['sub_type'] ?? 'url',
                    'index' => (string) $index,
                    'parameters' => [],
                ];

                if (($param['sub_type'] ?? 'url') === 'url') {
                    $buttonComponent['parameters'][] = [
                        'type' => 'text',
                        'text' => $param['value'],
                    ];
                } elseif (($param['sub_type'] ?? '') === 'quick_reply') {
                    $buttonComponent['parameters'][] = [
                        'type' => 'payload',
                        'payload' => $param['value'],
                    ];
                }

                $components[] = $buttonComponent;
            }
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $this->formatPhone($to),
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => $language],
            ],
        ];

        if (!empty($components)) {
            $payload['template']['components'] = $components;
        }

        return $this->sendRequest($account, $payload);
    }

    // ──────────────────────────────────
    // SEND IMAGE MESSAGE
    // ──────────────────────────────────
    public function sendImageMessage(
        WhatsappAccount $account,
        string $to,
        string $imageUrl,
        ?string $caption = null
    ): array {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $this->formatPhone($to),
            'type' => 'image',
            'image' => [
                'link' => $imageUrl,
            ],
        ];

        if ($caption) {
            $payload['image']['caption'] = $caption;
        }

        return $this->sendRequest($account, $payload);
    }

    // ──────────────────────────────────
    // SEND VIDEO MESSAGE
    // ──────────────────────────────────
    public function sendVideoMessage(
        WhatsappAccount $account,
        string $to,
        string $videoUrl,
        ?string $caption = null
    ): array {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $this->formatPhone($to),
            'type' => 'video',
            'video' => [
                'link' => $videoUrl,
            ],
        ];

        if ($caption) {
            $payload['video']['caption'] = $caption;
        }

        return $this->sendRequest($account, $payload);
    }

    // ──────────────────────────────────
    // SEND DOCUMENT MESSAGE
    // ──────────────────────────────────
    public function sendDocumentMessage(
        WhatsappAccount $account,
        string $to,
        string $documentUrl,
        ?string $caption = null,
        ?string $filename = null
    ): array {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $this->formatPhone($to),
            'type' => 'document',
            'document' => [
                'link' => $documentUrl,
            ],
        ];

        if ($caption) $payload['document']['caption'] = $caption;
        if ($filename) $payload['document']['filename'] = $filename;

        return $this->sendRequest($account, $payload);
    }

    // ──────────────────────────────────
    // SEND AUDIO MESSAGE
    // ──────────────────────────────────
    public function sendAudioMessage(WhatsappAccount $account, string $to, string $audioUrl): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $this->formatPhone($to),
            'type' => 'audio',
            'audio' => ['link' => $audioUrl],
        ];

        return $this->sendRequest($account, $payload);
    }

    // ──────────────────────────────────
    // SEND LOCATION MESSAGE
    // ──────────────────────────────────
    public function sendLocationMessage(
        WhatsappAccount $account,
        string $to,
        float $latitude,
        float $longitude,
        ?string $name = null,
        ?string $address = null
    ): array {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $this->formatPhone($to),
            'type' => 'location',
            'location' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
            ],
        ];

        if ($name) $payload['location']['name'] = $name;
        if ($address) $payload['location']['address'] = $address;

        return $this->sendRequest($account, $payload);
    }

    // ──────────────────────────────────
    // SEND INTERACTIVE BUTTONS MESSAGE
    // ──────────────────────────────────
    public function sendButtonMessage(
        WhatsappAccount $account,
        string $to,
        string $bodyText,
        array $buttons,
        ?string $headerText = null,
        ?string $footerText = null
    ): array {
        $interactive = [
            'type' => 'button',
            'body' => ['text' => $bodyText],
            'action' => [
                'buttons' => collect($buttons)->take(3)->map(function ($btn, $index) {
                    return [
                        'type' => 'reply',
                        'reply' => [
                            'id' => $btn['id'] ?? 'btn_' . $index,
                            'title' => substr($btn['title'], 0, 20),
                        ],
                    ];
                })->values()->toArray(),
            ],
        ];

        if ($headerText) {
            $interactive['header'] = ['type' => 'text', 'text' => $headerText];
        }
        if ($footerText) {
            $interactive['footer'] = ['text' => $footerText];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $this->formatPhone($to),
            'type' => 'interactive',
            'interactive' => $interactive,
        ];

        return $this->sendRequest($account, $payload);
    }

    // ──────────────────────────────────
    // SEND INTERACTIVE LIST MESSAGE
    // ──────────────────────────────────
    public function sendListMessage(
        WhatsappAccount $account,
        string $to,
        string $bodyText,
        string $buttonText,
        array $sections,
        ?string $headerText = null,
        ?string $footerText = null
    ): array {
        $interactive = [
            'type' => 'list',
            'body' => ['text' => $bodyText],
            'action' => [
                'button' => substr($buttonText, 0, 20),
                'sections' => collect($sections)->map(function ($section) {
                    return [
                        'title' => substr($section['title'], 0, 24),
                        'rows' => collect($section['rows'])->take(10)->map(function ($row) {
                            return [
                                'id' => $row['id'],
                                'title' => substr($row['title'], 0, 24),
                                'description' => isset($row['description']) ? substr($row['description'], 0, 72) : null,
                            ];
                        })->toArray(),
                    ];
                })->toArray(),
            ],
        ];

        if ($headerText) {
            $interactive['header'] = ['type' => 'text', 'text' => $headerText];
        }
        if ($footerText) {
            $interactive['footer'] = ['text' => $footerText];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $this->formatPhone($to),
            'type' => 'interactive',
            'interactive' => $interactive,
        ];

        return $this->sendRequest($account, $payload);
    }

    // ──────────────────────────────────
    // SEND REACTION MESSAGE
    // ──────────────────────────────────
    public function sendReaction(WhatsappAccount $account, string $to, string $messageId, string $emoji): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $this->formatPhone($to),
            'type' => 'reaction',
            'reaction' => [
                'message_id' => $messageId,
                'emoji' => $emoji,
            ],
        ];

        return $this->sendRequest($account, $payload);
    }

    // ──────────────────────────────────
    // MARK MESSAGE AS READ
    // ──────────────────────────────────
    public function markAsRead(WhatsappAccount $account, string $messageId): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'status' => 'read',
            'message_id' => $messageId,
        ];

        return $this->sendRequest($account, $payload);
    }

    // ──────────────────────────────────
    // TEMPLATE MANAGEMENT
    // ──────────────────────────────────
    public function createTemplate(WhatsappAccount $account, array $templateData): array
    {
        $url = "{$this->apiUrl}/{$account->waba_id}/message_templates";

        try {
            $response = Http::withToken($account->access_token)
                ->timeout(30)
                ->post($url, $templateData);

            $result = $response->json();

            Log::channel('whatsapp')->info('Template created', [
                'waba_id' => $account->waba_id,
                'template' => $templateData['name'] ?? 'unknown',
                'response' => $result,
            ]);

            return [
                'success' => $response->successful(),
                'data' => $result,
                'status_code' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('Template creation failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getTemplates(WhatsappAccount $account, int $limit = 100): array
    {
        $url = "{$this->apiUrl}/{$account->waba_id}/message_templates";

        try {
            $response = Http::withToken($account->access_token)
                ->timeout(30)
                ->get($url, [
                    'limit' => $limit,
                    'fields' => 'name,status,category,language,components,quality_score,rejected_reason',
                ]);

            return [
                'success' => $response->successful(),
                'data' => $response->json('data', []),
                'paging' => $response->json('paging'),
            ];
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('Get templates failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage(), 'data' => []];
        }
    }

    public function deleteTemplate(WhatsappAccount $account, string $templateName): array
    {
        $url = "{$this->apiUrl}/{$account->waba_id}/message_templates";

        try {
            $response = Http::withToken($account->access_token)
                ->timeout(30)
                ->delete($url, ['name' => $templateName]);

            return [
                'success' => $response->successful(),
                'data' => $response->json(),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ──────────────────────────────────
    // MEDIA MANAGEMENT
    // ──────────────────────────────────
    public function uploadMedia(WhatsappAccount $account, string $filePath, string $mimeType): array
    {
        $url = "{$this->apiUrl}/{$account->phone_number_id}/media";

        try {
            $response = Http::withToken($account->access_token)
                ->timeout(60)
                ->attach('file', file_get_contents($filePath), basename($filePath))
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'type' => $mimeType,
                ]);

            return [
                'success' => $response->successful(),
                'media_id' => $response->json('id'),
                'data' => $response->json(),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getMediaUrl(WhatsappAccount $account, string $mediaId): ?string
    {
        $url = "{$this->apiUrl}/{$mediaId}";

        try {
            $response = Http::withToken($account->access_token)
                ->timeout(15)
                ->get($url);

            return $response->json('url');
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('Get media URL failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function downloadMedia(WhatsappAccount $account, string $mediaUrl): ?string
    {
        try {
            $response = Http::withToken($account->access_token)
                ->timeout(30)
                ->get($mediaUrl);

            if ($response->successful()) {
                $extension = $this->getExtensionFromMime($response->header('Content-Type'));
                $filename = 'media/' . uniqid('wa_') . '.' . $extension;
                \Storage::disk('public')->put($filename, $response->body());
                return $filename;
            }
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('Download media failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    // ──────────────────────────────────
    // BUSINESS PROFILE
    // ──────────────────────────────────
    public function getBusinessProfile(WhatsappAccount $account): array
    {
        $url = "{$this->apiUrl}/{$account->phone_number_id}/whatsapp_business_profile";

        try {
            $response = Http::withToken($account->access_token)
                ->timeout(15)
                ->get($url, [
                    'fields' => 'about,address,description,email,profile_picture_url,websites,vertical',
                ]);

            return [
                'success' => $response->successful(),
                'data' => $response->json('data.0', []),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getPhoneNumberInfo(WhatsappAccount $account): array
    {
        $url = "{$this->apiUrl}/{$account->phone_number_id}";

        try {
            $response = Http::withToken($account->access_token)
                ->timeout(15)
                ->get($url, [
                    'fields' => 'verified_name,quality_rating,display_phone_number,status,name_status',
                ]);

            return [
                'success' => $response->successful(),
                'data' => $response->json(),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ──────────────────────────────────
    // WEBHOOK REGISTRATION
    // ──────────────────────────────────
    public function registerWebhook(WhatsappAccount $account, string $callbackUrl): array
    {
        $url = "{$this->apiUrl}/{$account->waba_id}/subscribed_apps";

        try {
            $response = Http::withToken($account->access_token)
                ->timeout(15)
                ->post($url);

            return [
                'success' => $response->successful(),
                'data' => $response->json(),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ──────────────────────────────────
    // PRIVATE HELPERS
    // ──────────────────────────────────
    protected function sendRequest(WhatsappAccount $account, array $payload): array
    {
        $url = "{$this->apiUrl}/{$account->phone_number_id}/messages";

        try {
            $response = Http::withToken($account->access_token)
                ->timeout(30)
                ->retry(2, 1000)
                ->post($url, $payload);

            $result = $response->json();

            Log::channel('whatsapp')->info('Message sent', [
                'phone_number_id' => $account->phone_number_id,
                'to' => $payload['to'] ?? 'unknown',
                'type' => $payload['type'] ?? 'unknown',
                'status_code' => $response->status(),
                'wamid' => $result['messages'][0]['id'] ?? null,
            ]);

            if ($response->successful() && isset($result['messages'])) {
                return [
                    'success' => true,
                    'wamid' => $result['messages'][0]['id'] ?? null,
                    'message_status' => $result['messages'][0]['message_status'] ?? 'accepted',
                    'data' => $result,
                ];
            }

            return [
                'success' => false,
                'error_code' => $result['error']['code'] ?? $response->status(),
                'error_message' => $result['error']['message'] ?? 'Unknown error',
                'data' => $result,
            ];
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('Message send failed', [
                'phone_number_id' => $account->phone_number_id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error_code' => 'EXCEPTION',
                'error_message' => $e->getMessage(),
            ];
        }
    }

    protected function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($phone) === 10) {
            $phone = '91' . $phone;
        }

        return $phone;
    }

    protected function getExtensionFromMime(string $mimeType): string
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'video/mp4' => 'mp4',
            'video/3gpp' => '3gp',
            'audio/aac' => 'aac',
            'audio/mp4' => 'm4a',
            'audio/mpeg' => 'mp3',
            'audio/ogg' => 'ogg',
            'application/pdf' => 'pdf',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        ];

        return $map[$mimeType] ?? 'bin';
    }
}