<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\CampaignContact;
use App\Models\Message;
use App\Services\MessageService;
use App\Services\WalletService;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 7200; // 2 hours

    public function __construct(
        public Campaign $campaign
    ) {}

    public function handle(MessageService $messageService, WalletService $walletService, NotificationService $notificationService): void
    {
        $campaign = $this->campaign->fresh();

        if (!$campaign || !in_array($campaign->status, ['processing', 'scheduled'])) {
            Log::info("Campaign {$campaign->id} skipped - status: {$campaign->status}");
            return;
        }

        $campaign->update([
            'status' => 'sending',
            'started_at' => $campaign->started_at ?? now(),
        ]);

        $user = $campaign->user;
        $account = $campaign->whatsappAccount;
        $template = $campaign->template;

        if (!$account || !$account->isConnected()) {
            $this->failCampaign($campaign, 'WhatsApp account not connected');
            return;
        }

        if (!$template || !$template->isApproved()) {
            $this->failCampaign($campaign, 'Template not approved');
            return;
        }

        $messagesPerSecond = $campaign->messages_per_second;
        $delayMicroseconds = (int)(1000000 / max(1, $messagesPerSecond));
        $messageCost = config("whatify.message_cost.{$template->category}", 0.90);

        Log::info("Campaign {$campaign->id} started", [
            'total_contacts' => $campaign->total_contacts,
            'rate' => $messagesPerSecond,
        ]);

        // Process pending contacts in chunks
        CampaignContact::where('campaign_id', $campaign->id)
            ->where('status', 'pending')
            ->with('contact')
            ->chunkById(100, function ($campaignContacts) use (
                $campaign, $user, $account, $template,
                $messageService, $walletService,
                $messageCost, $delayMicroseconds
            ) {
                // Re-check campaign status (might be paused/cancelled)
                $freshCampaign = $campaign->fresh();
                if ($freshCampaign->status !== 'sending') {
                    return false; // Stop chunking
                }

                foreach ($campaignContacts as $cc) {
                    // Check if paused or cancelled
                    if ($campaign->fresh()->status !== 'sending') {
                        return false;
                    }

                    $contact = $cc->contact;

                    if (!$contact || $contact->status !== 'active') {
                        $cc->update(['status' => 'failed', 'error_message' => 'Contact inactive']);
                        $campaign->increment('failed_count');
                        continue;
                    }

                    // Check wallet balance
                    if (!$user->wallet?->hasBalance($messageCost)) {
                        $this->pauseCampaignLowBalance($campaign, $user);
                        return false; // Stop processing
                    }

                    // Resolve variables for this contact
                    $bodyParams = [];
                    $variables = $cc->variables ?? [];
                    foreach ($variables as $value) {
                        $bodyParams[] = (string)$value;
                    }

                    // Resolve header params if needed
                    $headerParams = [];
                    $headerMedia = null;
                    if ($template->header) {
                        $headerType = $template->header['type'] ?? 'text';
                        if (in_array($headerType, ['image', 'video', 'document'])) {
                            $headerMedia = [
                                'type' => $headerType,
                                'url' => $template->header['media_url'] ?? '',
                            ];
                        }
                    }

                    try {
                        $message = $messageService->sendTemplate(
                            $user,
                            $account,
                            $contact,
                            $template,
                            $bodyParams,
                            $headerParams,
                            [],
                            $headerMedia,
                            $campaign->id
                        );

                        if ($message) {
                            $cc->update([
                                'message_id' => $message->id,
                                'status' => 'sent',
                            ]);
                            $campaign->increment('sent_count');
                            $campaign->increment('total_cost', $messageCost);
                        } else {
                            $cc->update([
                                'status' => 'failed',
                                'error_message' => 'Message creation failed',
                            ]);
                            $campaign->increment('failed_count');
                        }
                    } catch (\Exception $e) {
                        $cc->update([
                            'status' => 'failed',
                            'error_message' => substr($e->getMessage(), 0, 255),
                        ]);
                        $campaign->increment('failed_count');

                        Log::error("Campaign message failed", [
                            'campaign_id' => $campaign->id,
                            'contact_id' => $contact->id,
                            'error' => $e->getMessage(),
                        ]);
                    }

                    // Rate limiting delay
                    usleep($delayMicroseconds);
                }
            });

        // Check final status
        $finalCampaign = $campaign->fresh();

        if ($finalCampaign->status === 'sending') {
            $pendingCount = CampaignContact::where('campaign_id', $campaign->id)
                ->where('status', 'pending')
                ->count();

            if ($pendingCount === 0) {
                $finalCampaign->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);

                // Notify user
                $notificationService->send(
                    $user,
                    'Campaign Completed',
                    "Campaign '{$campaign->name}' has been completed. Sent: {$finalCampaign->sent_count}, Failed: {$finalCampaign->failed_count}",
                    'campaign'
                );

                Log::info("Campaign {$campaign->id} completed", [
                    'sent' => $finalCampaign->sent_count,
                    'failed' => $finalCampaign->failed_count,
                    'cost' => $finalCampaign->total_cost,
                ]);
            }
        }
    }

    protected function failCampaign(Campaign $campaign, string $reason): void
    {
        $campaign->update([
            'status' => 'failed',
            'completed_at' => now(),
        ]);

        app(NotificationService::class)->send(
            $campaign->user,
            'Campaign Failed',
            "Campaign '{$campaign->name}' failed: {$reason}",
            'error'
        );

        Log::error("Campaign {$campaign->id} failed: {$reason}");
    }

    protected function pauseCampaignLowBalance(Campaign $campaign, $user): void
    {
        $campaign->update(['status' => 'paused']);

        app(NotificationService::class)->send(
            $user,
            'Campaign Paused - Low Balance',
            "Campaign '{$campaign->name}' has been paused due to low wallet balance. Please recharge and resume.",
            'wallet'
        );

        Log::warning("Campaign {$campaign->id} paused - low wallet balance");
    }

    public function failed(\Throwable $exception): void
    {
        $this->campaign->update([
            'status' => 'failed',
            'completed_at' => now(),
        ]);

        Log::error("Campaign job failed", [
            'campaign_id' => $this->campaign->id,
            'error' => $exception->getMessage(),
        ]);
    }
}