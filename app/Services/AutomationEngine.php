<?php

namespace App\Services;

use App\Models\Automation;
use App\Models\AutomationLog;
use App\Models\AutomationStep;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\WhatsappAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AutomationEngine
{
    public function __construct(
        protected MessageService $messageService,
        protected NotificationService $notificationService,
    ) {}

    public function findMatchingAutomations(int $userId, string $triggerType, array $data): \Illuminate\Support\Collection
    {
        return Automation::where('user_id', $userId)
            ->where('status', 'active')
            ->where('trigger_type', $triggerType)
            ->orderBy('sort_order')
            ->get()
            ->filter(fn($auto) => $this->matchesTriggerCondition($auto, $data));
    }

    public function matchesTriggerCondition(Automation $automation, array $data): bool
    {
        $config = $automation->trigger_config ?? [];

        return match ($automation->trigger_type) {
            'keyword' => $this->matchesKeyword($config, $data['content'] ?? ''),
            'incoming_message' => true,
            'contact_created' => true,
            'tag_added' => $this->matchesTag($config, $data['tag_id'] ?? null),
            'shopify_order' => true,
            'shopify_abandoned_cart' => true,
            'woocommerce_order' => true,
            'campaign_reply' => $this->matchesCampaignReply($config, $data),
            'form_submission' => true,
            'api_trigger' => $this->matchesApiTrigger($config, $data),
            default => false,
        };
    }

    protected function matchesKeyword(array $config, string $content): bool
    {
        $keywords = $config['keywords'] ?? [];
        $matchType = $config['match_type'] ?? 'contains';
        $content = strtolower(trim($content));

        if (empty($keywords) || empty($content)) return false;

        foreach ($keywords as $keyword) {
            $keyword = strtolower(trim($keyword));
            if (empty($keyword)) continue;

            $matched = match ($matchType) {
                'exact' => $content === $keyword,
                'starts_with' => str_starts_with($content, $keyword),
                'ends_with' => str_ends_with($content, $keyword),
                'contains' => str_contains($content, $keyword),
                'regex' => (bool) preg_match("/{$keyword}/i", $content),
                default => str_contains($content, $keyword),
            };

            if ($matched) return true;
        }

        return false;
    }

    protected function matchesTag(array $config, ?int $tagId): bool
    {
        $targetTags = $config['tag_ids'] ?? [];
        if (empty($targetTags)) return true;
        return in_array($tagId, $targetTags);
    }

    protected function matchesCampaignReply(array $config, array $data): bool
    {
        $campaignIds = $config['campaign_ids'] ?? [];
        if (empty($campaignIds)) return true;
        return in_array($data['campaign_id'] ?? null, $campaignIds);
    }

    protected function matchesApiTrigger(array $config, array $data): bool
    {
        $triggerKey = $config['trigger_key'] ?? null;
        if (!$triggerKey) return true;
        return ($data['trigger_key'] ?? null) === $triggerKey;
    }

    public function executeAutomation(
        Automation $automation,
        Contact $contact,
        ?Conversation $conversation = null,
        array $initialVariables = []
    ): AutomationLog {
        $log = AutomationLog::create([
            'automation_id' => $automation->id,
            'contact_id' => $contact->id,
            'conversation_id' => $conversation?->id,
            'status' => 'running',
            'variables' => $initialVariables,
            'execution_path' => [],
            'started_at' => now(),
        ]);

        $automation->increment('execution_count');

        try {
            $firstStep = $automation->steps()->orderBy('sort_order')->first();

            if (!$firstStep) {
                $log->update(['status' => 'completed', 'completed_at' => now()]);
                return $log;
            }

            $this->executeStep($automation, $firstStep, $contact, $conversation, $log);

        } catch (\Exception $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            Log::channel('whatsapp')->error('Automation execution failed', [
                'automation_id' => $automation->id,
                'contact_id' => $contact->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $log;
    }

    public function executeStep(
        Automation $automation,
        AutomationStep $step,
        Contact $contact,
        ?Conversation $conversation,
        AutomationLog $log
    ): void {
        $path = $log->execution_path ?? [];
        $path[] = [
            'step_id' => $step->step_id,
            'type' => $step->type,
            'executed_at' => now()->toIso8601String(),
        ];

        $log->update([
            'current_step_id' => $step->step_id,
            'execution_path' => $path,
        ]);

        $config = $step->config ?? [];
        $variables = $log->variables ?? [];
        $user = $automation->user;
        $account = $automation->whatsappAccount ?? $user->whatsappAccounts()->where('status', 'connected')->first();

        if (!$account && in_array($step->type, ['send_message', 'send_template', 'send_media', 'ask_question', 'buttons', 'list_menu'])) {
            $log->update(['status' => 'failed', 'error_message' => 'No connected WhatsApp account', 'completed_at' => now()]);
            return;
        }

        $nextStepId = null;

        switch ($step->type) {
            case 'send_message':
                $text = $this->replaceVariables($config['message'] ?? '', $variables, $contact);
                $this->messageService->sendText($user, $account, $contact, $text, 'Bot');
                $nextStepId = $step->next_step_id;
                break;

            case 'send_template':
                $template = \App\Models\MessageTemplate::where('id', $config['template_id'] ?? 0)
                    ->where('user_id', $user->id)
                    ->where('status', 'approved')
                    ->first();
                if ($template) {
                    $bodyParams = $this->resolveTemplateParams($config['body_params'] ?? [], $variables, $contact);
                    $this->messageService->sendTemplate($user, $account, $contact, $template, $bodyParams);
                }
                $nextStepId = $step->next_step_id;
                break;

            case 'send_media':
                $mediaType = $config['media_type'] ?? 'image';
                $mediaUrl = $this->replaceVariables($config['media_url'] ?? '', $variables, $contact);
                $caption = $this->replaceVariables($config['caption'] ?? '', $variables, $contact);
                $this->messageService->sendMedia($user, $account, $contact, $mediaType, $mediaUrl, $caption);
                $nextStepId = $step->next_step_id;
                break;

            case 'ask_question':
                $question = $this->replaceVariables($config['question'] ?? '', $variables, $contact);
                $this->messageService->sendText($user, $account, $contact, $question, 'Bot');
                $variableName = $config['variable_name'] ?? 'response_' . $step->step_id;
                $log->update([
                    'status' => 'paused',
                    'current_step_id' => $step->step_id,
                    'variables' => array_merge($variables, ['_awaiting_response' => $variableName]),
                ]);
                return;

            case 'buttons':
                $bodyText = $this->replaceVariables($config['body'] ?? '', $variables, $contact);
                $buttons = collect($config['buttons'] ?? [])->map(fn($b) => [
                    'id' => $b['id'] ?? uniqid('btn_'),
                    'title' => substr($this->replaceVariables($b['title'] ?? '', $variables, $contact), 0, 20),
                ])->toArray();
                $this->messageService->sendButtons(
                    $user, $account, $contact, $bodyText, $buttons,
                    $config['header'] ?? null, $config['footer'] ?? null
                );
                $log->update([
                    'status' => 'paused',
                    'current_step_id' => $step->step_id,
                    'variables' => array_merge($variables, [
                        '_awaiting_button' => true,
                        '_button_step_id' => $step->step_id,
                    ]),
                ]);
                return;

            case 'list_menu':
                $bodyText = $this->replaceVariables($config['body'] ?? '', $variables, $contact);
                $this->messageService->sendList(
                    $user, $account, $contact, $bodyText,
                    $config['button_text'] ?? 'Menu',
                    $config['sections'] ?? [],
                    $config['header'] ?? null, $config['footer'] ?? null
                );
                $log->update([
                    'status' => 'paused',
                    'current_step_id' => $step->step_id,
                    'variables' => array_merge($variables, ['_awaiting_list' => true]),
                ]);
                return;
                // google sheet

                case 'google_sheets_append':
    $integrationId = $config['integration_id'] ?? null;
    $integration = \App\Models\Integration::where('id', $integrationId)
        ->where('user_id', $user->id)
        ->where('type', 'google_sheets')
        ->where('status', 'active')
        ->first();

    if ($integration) {
        $sheetsService = \App\Services\Integrations\IntegrationFactory::fromIntegration($integration);
        $rowData = [];
        foreach ($config['columns'] ?? [] as $col) {
            $rowData[] = $this->replaceVariables($col['value'] ?? '', $variables, $contact);
        }
        $sheetsService->appendRow($config['sheet'] ?? null, $rowData);
    }
    $nextStepId = $step->next_step_id;
    break;
    
            case 'condition':
                $nextStepId = $this->evaluateCondition($step, $variables, $contact);
                break;

            case 'delay':
                $seconds = ($config['value'] ?? 1) * match ($config['unit'] ?? 'seconds') {
                    'minutes' => 60,
                    'hours' => 3600,
                    'days' => 86400,
                    default => 1,
                };
                $nextStep = $step->next_step_id ? $automation->steps()->where('step_id', $step->next_step_id)->first() : null;
                if ($nextStep && $seconds > 0) {
                    \App\Jobs\ContinueAutomation::dispatch($automation->id, $nextStep->id, $contact->id, $conversation?->id, $log->id)
                        ->delay(now()->addSeconds($seconds))
                        ->onQueue('automations');
                    return;
                }
                $nextStepId = $step->next_step_id;
                break;

            case 'set_variable':
                $varName = $config['variable_name'] ?? 'custom_var';
                $varValue = $this->replaceVariables($config['value'] ?? '', $variables, $contact);
                $variables[$varName] = $varValue;
                $log->update(['variables' => $variables]);
                $nextStepId = $step->next_step_id;
                break;

            case 'add_tag':
                $tagId = $config['tag_id'] ?? null;
                if ($tagId) {
                    $contact->tags()->syncWithoutDetaching([$tagId]);
                }
                $nextStepId = $step->next_step_id;
                break;

            case 'remove_tag':
                $tagId = $config['tag_id'] ?? null;
                if ($tagId) {
                    $contact->tags()->detach($tagId);
                }
                $nextStepId = $step->next_step_id;
                break;

            case 'assign_agent':
                $agentId = $config['agent_id'] ?? null;
                if ($conversation && $agentId) {
                    $conversation->update(['assigned_agent_id' => $agentId]);
                }
                $nextStepId = $step->next_step_id;
                break;

            case 'transfer_to_agent':
                if ($conversation) {
                    $conversation->update([
                        'is_bot_active' => false,
                        'bot_paused_until' => $config['pause_hours'] ? now()->addHours($config['pause_hours']) : null,
                    ]);
                    $agentId = $config['agent_id'] ?? null;
                    if ($agentId) {
                        $conversation->update(['assigned_agent_id' => $agentId]);
                        $agent = User::find($agentId);
                        if ($agent) {
                            $this->notificationService->send(
                                $agent,
                                'Chat transferred to you',
                                "Contact: {$contact->name} ({$contact->phone})",
                                'message',
                                null,
                                ['conversation_id' => $conversation->id]
                            );
                        }
                    }
                }
                $log->update(['status' => 'completed', 'completed_at' => now()]);
                return;

            case 'api_call':
                $response = $this->executeApiCall($config, $variables, $contact);
                if ($response && !empty($config['response_variable'])) {
                    $variables[$config['response_variable']] = $response;
                    $log->update(['variables' => $variables]);
                }
                $nextStepId = $step->next_step_id;
                break;

            case 'webhook':
                $this->sendWebhook($config, $variables, $contact, $automation);
                $nextStepId = $step->next_step_id;
                break;

            case 'update_contact':
                $field = $config['field'] ?? null;
                $value = $this->replaceVariables($config['value'] ?? '', $variables, $contact);
                if ($field && $value) {
                    if (in_array($field, ['name', 'email'])) {
                        $contact->update([$field => $value]);
                    } else {
                        $attrs = $contact->custom_attributes ?? [];
                        $attrs[$field] = $value;
                        $contact->update(['custom_attributes' => $attrs]);
                    }
                }
                $nextStepId = $step->next_step_id;
                break;

            case 'goto_step':
                $nextStepId = $config['target_step_id'] ?? null;
                break;

            case 'end_flow':
                $log->update(['status' => 'completed', 'completed_at' => now()]);
                return;

            default:
                $nextStepId = $step->next_step_id;
                break;
        }

        if ($nextStepId) {
            $nextStep = $automation->steps()->where('step_id', $nextStepId)->first();
            if ($nextStep) {
                $this->executeStep($automation, $nextStep, $contact, $conversation, $log);
                return;
            }
        }

        $log->update(['status' => 'completed', 'completed_at' => now()]);
    }

    public function handleResponse(AutomationLog $log, string $response): void
    {
        if ($log->status !== 'paused') return;

        $variables = $log->variables ?? [];
        $automation = $log->automation;
        $contact = $log->contact;
        $conversation = $log->conversation;
        $currentStepId = $log->current_step_id;

        $currentStep = $automation->steps()->where('step_id', $currentStepId)->first();
        if (!$currentStep) {
            $log->update(['status' => 'failed', 'error_message' => 'Step not found', 'completed_at' => now()]);
            return;
        }

        if (!empty($variables['_awaiting_response'])) {
            $varName = $variables['_awaiting_response'];
            unset($variables['_awaiting_response']);
            $variables[$varName] = $response;
            $log->update(['variables' => $variables, 'status' => 'running']);

            $validation = $currentStep->config['validation'] ?? null;
            if ($validation && !$this->validateResponse($response, $validation)) {
                $errorMsg = $currentStep->config['validation_error'] ?? 'Invalid input. Please try again.';
                $account = $automation->whatsappAccount ?? $automation->user->whatsappAccounts()->where('status', 'connected')->first();
                if ($account) {
                    $this->messageService->sendText($automation->user, $account, $contact, $errorMsg, 'Bot');
                }
                $variables['_awaiting_response'] = $varName;
                $log->update(['variables' => $variables, 'status' => 'paused']);
                return;
            }

            $nextStepId = $currentStep->next_step_id;

        } elseif (!empty($variables['_awaiting_button']) || !empty($variables['_awaiting_list'])) {
            unset($variables['_awaiting_button'], $variables['_awaiting_list'], $variables['_button_step_id']);
            $variables['button_response'] = $response;
            $log->update(['variables' => $variables, 'status' => 'running']);

            $nextStepId = null;
            $branches = $currentStep->branches ?? [];
            foreach ($branches as $branch) {
                $branchValue = strtolower($branch['value'] ?? '');
                if ($branchValue === strtolower($response) || $branchValue === '*') {
                    $nextStepId = $branch['next_step_id'] ?? null;
                    break;
                }
            }

            if (!$nextStepId) {
                $defaultBranch = collect($branches)->firstWhere('value', '*');
                $nextStepId = $defaultBranch['next_step_id'] ?? $currentStep->next_step_id;
            }
        } else {
            return;
        }

        if ($nextStepId) {
            $nextStep = $automation->steps()->where('step_id', $nextStepId)->first();
            if ($nextStep) {
                $this->executeStep($automation, $nextStep, $contact, $conversation, $log);
                return;
            }
        }

        $log->update(['status' => 'completed', 'completed_at' => now()]);
    }

    protected function evaluateCondition(AutomationStep $step, array $variables, Contact $contact): ?string
    {
        $branches = $step->branches ?? [];

        foreach ($branches as $branch) {
            $field = $branch['field'] ?? '';
            $operator = $branch['operator'] ?? 'equals';
            $value = $branch['value'] ?? '';

            $actualValue = $this->getFieldValue($field, $variables, $contact);

            $matched = match ($operator) {
                'equals' => strtolower((string)$actualValue) === strtolower((string)$value),
                'not_equals' => strtolower((string)$actualValue) !== strtolower((string)$value),
                'contains' => str_contains(strtolower((string)$actualValue), strtolower((string)$value)),
                'not_contains' => !str_contains(strtolower((string)$actualValue), strtolower((string)$value)),
                'greater_than' => (float)$actualValue > (float)$value,
                'less_than' => (float)$actualValue < (float)$value,
                'is_empty' => empty($actualValue),
                'is_not_empty' => !empty($actualValue),
                'starts_with' => str_starts_with(strtolower((string)$actualValue), strtolower((string)$value)),
                'ends_with' => str_ends_with(strtolower((string)$actualValue), strtolower((string)$value)),
                'has_tag' => $contact->tags()->where('name', $value)->exists(),
                default => false,
            };

            if ($matched) {
                return $branch['next_step_id'] ?? null;
            }
        }

        $defaultBranch = collect($branches)->firstWhere('is_default', true);
        return $defaultBranch['next_step_id'] ?? $step->next_step_id;
    }

    protected function getFieldValue(string $field, array $variables, Contact $contact): mixed
    {
        if (str_starts_with($field, 'var.')) {
            return $variables[substr($field, 4)] ?? '';
        }

        return match ($field) {
            'contact.name' => $contact->name,
            'contact.phone' => $contact->phone,
            'contact.email' => $contact->email,
            'contact.status' => $contact->status,
            'contact.source' => $contact->source,
            default => $contact->custom_attributes[$field] ?? ($variables[$field] ?? ''),
        };
    }

    protected function replaceVariables(string $text, array $variables, Contact $contact): string
    {
        $replacements = [
            '{{contact_name}}' => $contact->name ?? 'Customer',
            '{{contact_phone}}' => $contact->phone,
            '{{contact_email}}' => $contact->email ?? '',
            '{{name}}' => $contact->name ?? 'Customer',
            '{{phone}}' => $contact->phone,
            '{{email}}' => $contact->email ?? '',
        ];

        foreach ($variables as $key => $value) {
            if (!str_starts_with($key, '_')) {
                $replacements["{{{$key}}}"] = is_string($value) ? $value : json_encode($value);
            }
        }

        if ($contact->custom_attributes) {
            foreach ($contact->custom_attributes as $key => $value) {
                $replacements["{{custom.{$key}}}"] = $value;
            }
        }

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }

    protected function resolveTemplateParams(array $params, array $variables, Contact $contact): array
    {
        return collect($params)->map(fn($p) => $this->replaceVariables((string)$p, $variables, $contact))->toArray();
    }

    protected function validateResponse(string $response, array $validation): bool
    {
        $type = $validation['type'] ?? 'any';

        return match ($type) {
            'email' => filter_var($response, FILTER_VALIDATE_EMAIL) !== false,
            'phone' => preg_match('/^\d{10,15}$/', preg_replace('/[^0-9]/', '', $response)),
            'number' => is_numeric($response),
            'min_length' => strlen($response) >= ($validation['value'] ?? 1),
            'max_length' => strlen($response) <= ($validation['value'] ?? 255),
            'regex' => (bool) preg_match($validation['pattern'] ?? '/.*/', $response),
            'options' => in_array(strtolower($response), array_map('strtolower', $validation['options'] ?? [])),
            default => true,
        };
    }

    protected function executeApiCall(array $config, array $variables, Contact $contact): ?string
    {
        $url = $this->replaceVariables($config['url'] ?? '', $variables, $contact);
        $method = strtolower($config['method'] ?? 'get');
        $headers = $config['headers'] ?? [];
        $body = $config['body'] ?? [];

        foreach ($body as $key => $value) {
            $body[$key] = $this->replaceVariables((string)$value, $variables, $contact);
        }

        try {
            $response = match ($method) {
                'post' => Http::withHeaders($headers)->timeout(15)->post($url, $body),
                'put' => Http::withHeaders($headers)->timeout(15)->put($url, $body),
                default => Http::withHeaders($headers)->timeout(15)->get($url, $body),
            };

            return $response->body();
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('Automation API call failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    protected function sendWebhook(array $config, array $variables, Contact $contact, Automation $automation): void
    {
        $url = $config['url'] ?? '';
        if (empty($url)) return;

        try {
            Http::timeout(10)->post($url, [
                'automation_id' => $automation->id,
                'automation_name' => $automation->name,
                'contact' => [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'phone' => $contact->phone,
                    'email' => $contact->email,
                ],
                'variables' => $variables,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('Automation webhook failed', ['url' => $url, 'error' => $e->getMessage()]);
        }
    }
}