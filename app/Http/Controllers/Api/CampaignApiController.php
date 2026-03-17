<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;

class CampaignApiController extends Controller
{
    public function index(Request $request)
    {
        $owner = $request->user()->getBusinessOwner() ?? $request->user();

        $campaigns = Campaign::where('user_id', $owner->id)
            ->with('template:id,name,category')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json($campaigns);
    }

    public function show(Request $request, Campaign $campaign)
    {
        $owner = $request->user()->getBusinessOwner() ?? $request->user();
        if ($campaign->user_id !== $owner->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $campaign->load(['template', 'whatsappAccount:id,phone_number,display_name']);

        return response()->json([
            'campaign' => $campaign,
            'delivery_rate' => $campaign->getDeliveryRate(),
            'read_rate' => $campaign->getReadRate(),
        ]);
    }
}