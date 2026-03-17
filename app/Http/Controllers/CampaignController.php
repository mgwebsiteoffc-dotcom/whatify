<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignContact;
use App\Models\Contact;
use App\Models\MessageTemplate;
use App\Models\Tag;
use App\Models\WhatsappAccount;
use App\Jobs\ProcessCampaign;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();

        $campaigns = Campaign::where('user_id', $owner->id)
            ->with(['whatsappAccount:id,phone_number,display_name', 'template:id,name,category'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => Campaign::where('user_id', $owner->id)->count(),
            'active' => Campaign::where('user_id', $owner->id)->whereIn('status', ['sending', 'processing'])->count(),
            'completed' => Campaign::where('user_id', $owner->id)->where('status', 'completed')->count(),
            'draft' => Campaign::where('user_id', $owner->id)->where('status', 'draft')->count(),
        ];

        return view('campaigns.index', compact('campaigns', 'stats'));
    }

    public function create()
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();

        if (!$owner->canUseFeature('campaigns_per_month')) {
            return back()->with('error', 'Campaign limit reached this month. Please upgrade.');
        }

        $accounts = $owner->whatsappAccounts()->where('status', 'connected')->get();
        $templates = MessageTemplate::where('user_id', $owner->id)
            ->where('status', 'approved')
            ->orderBy('name')
            ->get();
        $tags = Tag::where('user_id', $owner->id)->withCount('contacts')->orderBy('name')->get();

        $totalContacts = Contact::where('user_id', $owner->id)->where('status', 'active')->count();

        return view('campaigns.create', compact('accounts', 'templates', 'tags', 'totalContacts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'whatsapp_account_id' => 'required|exists:whatsapp_accounts,id',
            'template_id' => 'required|exists:message_templates,id',
            'audience_type' => 'required|in:all,tags,custom',
            'tag_ids' => 'required_if:audience_type,tags|nullable|array',
            'tag_ids.*' => 'exists:tags,id',
            'contact_ids' => 'required_if:audience_type,custom|nullable|array',
            'contact_ids.*' => 'exists:contacts,id',
            'template_variables' => 'nullable|array',
            'template_variables.*.source' => 'nullable|in:static,contact_name,contact_phone,contact_email,custom_attribute',
            'template_variables.*.value' => 'nullable|string',
            'scheduled_at' => 'nullable|date|after:now',
            'messages_per_second' => 'nullable|integer|min:1|max:80',
            'action' => 'required|in:draft,schedule,send_now',
        ]);

        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();

        if (!$owner->canUseFeature('campaigns_per_month')) {
            return back()->with('error', 'Campaign limit reached this month.');
        }

        // Verify ownership
        $account = WhatsappAccount::where('id', $validated['whatsapp_account_id'])
            ->where('user_id', $owner->id)->firstOrFail();
        $template = MessageTemplate::where('id', $validated['template_id'])
            ->where('user_id', $owner->id)
            ->where('status', 'approved')->firstOrFail();

        // Build audience filter
        $audienceFilter = [
            'type' => $validated['audience_type'],
            'tag_ids' => $validated['tag_ids'] ?? [],
            'contact_ids' => $validated['contact_ids'] ?? [],
        ];

        // Count contacts
        $contactQuery = Contact::where('user_id', $owner->id)->where('status', 'active');

        if ($validated['audience_type'] === 'tags' && !empty($validated['tag_ids'])) {
            $contactQuery->whereHas('tags', fn($q) => $q->whereIn('tags.id', $validated['tag_ids']));
        } elseif ($validated['audience_type'] === 'custom' && !empty($validated['contact_ids'])) {
            $contactQuery->whereIn('id', $validated['contact_ids']);
        }

        $totalContacts = $contactQuery->count();

        if ($totalContacts === 0) {
            return back()->with('error', 'No contacts found for the selected audience.');
        }

        // Estimate cost
        $messageCost = config("whatify.message_cost.{$template->category}", 0.90);
        $estimatedCost = $totalContacts * $messageCost;

        // Check wallet balance
        if ($validated['action'] !== 'draft' && !$owner->wallet?->hasBalance($estimatedCost)) {
            return back()->with('error', "Insufficient wallet balance. Estimated cost: ₹" . number_format($estimatedCost, 2) . ". Please recharge.");
        }

        // Determine status
        $status = match ($validated['action']) {
            'draft' => 'draft',
            'schedule' => 'scheduled',
            'send_now' => 'processing',
        };

        $campaign = Campaign::create([
            'user_id' => $owner->id,
            'whatsapp_account_id' => $account->id,
            'template_id' => $template->id,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'status' => $status,
            'audience_filter' => $audienceFilter,
            'template_variables' => $validated['template_variables'] ?? null,
            'scheduled_at' => $validated['scheduled_at'] ?? ($validated['action'] === 'send_now' ? now() : null),
            'total_contacts' => $totalContacts,
            'messages_per_second' => $validated['messages_per_second'] ?? 30,
        ]);

        // Add contacts to campaign
        $contactQuery->chunkById(500, function ($contacts) use ($campaign, $validated) {
            $rows = [];
            foreach ($contacts as $contact) {
                $rows[] = [
                    'campaign_id' => $campaign->id,
                    'contact_id' => $contact->id,
                    'status' => 'pending',
                    'variables' => json_encode($this->resolveVariables(
                        $contact,
                        $validated['template_variables'] ?? []
                    )),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            CampaignContact::insert($rows);
        });

        // Send now or schedule
        if ($validated['action'] === 'send_now') {
            ProcessCampaign::dispatch($campaign)->onQueue('whatsapp');
        } elseif ($validated['action'] === 'schedule' && $validated['scheduled_at']) {
            ProcessCampaign::dispatch($campaign)
                ->delay(\Carbon\Carbon::parse($validated['scheduled_at']))
                ->onQueue('whatsapp');
        }

        \App\Services\ActivityLogger::log('campaign_created', 'Campaign', $campaign->id);

        return redirect()->route('campaigns.show', $campaign)
            ->with('success', $validated['action'] === 'draft'
                ? 'Campaign saved as draft.'
                : ($validated['action'] === 'schedule'
                    ? 'Campaign scheduled successfully!'
                    : 'Campaign started! Messages are being sent.'));
    }

    public function show(Campaign $campaign)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($campaign->user_id !== $owner->id, 403);

        $campaign->load(['whatsappAccount:id,phone_number,display_name', 'template']);

        // Contact statuses breakdown
        $statusBreakdown = CampaignContact::where('campaign_id', $campaign->id)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Recent campaign contacts with status
        $campaignContacts = CampaignContact::where('campaign_id', $campaign->id)
            ->with('contact:id,name,phone')
            ->orderBy('updated_at', 'desc')
            ->paginate(25);

        return view('campaigns.show', compact('campaign', 'statusBreakdown', 'campaignContacts'));
    }

    public function pause(Campaign $campaign)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($campaign->user_id !== $owner->id, 403);

        if (!in_array($campaign->status, ['sending', 'processing'])) {
            return back()->with('error', 'Campaign cannot be paused in current state.');
        }

        $campaign->update(['status' => 'paused']);

        return back()->with('success', 'Campaign paused.');
    }

    public function resume(Campaign $campaign)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($campaign->user_id !== $owner->id, 403);

        if ($campaign->status !== 'paused') {
            return back()->with('error', 'Campaign is not paused.');
        }

        $campaign->update(['status' => 'processing']);
        ProcessCampaign::dispatch($campaign)->onQueue('whatsapp');

        return back()->with('success', 'Campaign resumed.');
    }

    public function cancel(Campaign $campaign)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($campaign->user_id !== $owner->id, 403);

        if (in_array($campaign->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'Campaign cannot be cancelled.');
        }

        $campaign->update(['status' => 'cancelled']);

        // Mark pending contacts as cancelled
        CampaignContact::where('campaign_id', $campaign->id)
            ->where('status', 'pending')
            ->update(['status' => 'failed', 'error_message' => 'Campaign cancelled']);

        return back()->with('success', 'Campaign cancelled.');
    }

    public function duplicate(Campaign $campaign)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($campaign->user_id !== $owner->id, 403);

        $newCampaign = $campaign->replicate();
        $newCampaign->name = $campaign->name . ' (Copy)';
        $newCampaign->status = 'draft';
        $newCampaign->sent_count = 0;
        $newCampaign->delivered_count = 0;
        $newCampaign->read_count = 0;
        $newCampaign->replied_count = 0;
        $newCampaign->failed_count = 0;
        $newCampaign->total_cost = 0;
        $newCampaign->scheduled_at = null;
        $newCampaign->started_at = null;
        $newCampaign->completed_at = null;
        $newCampaign->save();

        return redirect()->route('campaigns.show', $newCampaign)
            ->with('success', 'Campaign duplicated as draft.');
    }

    public function destroy(Campaign $campaign)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($campaign->user_id !== $owner->id, 403);

        if (in_array($campaign->status, ['sending', 'processing'])) {
            return back()->with('error', 'Cannot delete an active campaign. Pause it first.');
        }

        $campaign->delete();

        return redirect()->route('campaigns.index')->with('success', 'Campaign deleted.');
    }

    // ──── Helpers ────
    protected function resolveVariables(Contact $contact, array $variableConfig): array
    {
        $resolved = [];

        foreach ($variableConfig as $index => $config) {
            $source = $config['source'] ?? 'static';
            $value = $config['value'] ?? '';

            $resolved[$index] = match ($source) {
                'contact_name' => $contact->name ?? 'Customer',
                'contact_phone' => $contact->phone,
                'contact_email' => $contact->email ?? '',
                'custom_attribute' => $contact->custom_attributes[$value] ?? '',
                default => $value,
            };
        }

        return $resolved;
    }
}