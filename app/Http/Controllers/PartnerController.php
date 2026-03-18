<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerCommission;
use App\Models\PartnerPayout;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PartnerController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        $partner = Partner::where('user_id', $user->id)->first();

        if (!$partner) {
            return redirect()->route('partner.apply');
        }

        $stats = [
            'total_referrals' => $partner->total_referrals,
            'active_customers' => User::where('partner_id', $user->id)->where('status', 'active')->count(),
            'total_earned' => $partner->total_earned,
            'total_paid' => $partner->total_paid,
            'pending_payout' => $partner->pending_payout,
            'commission_rate' => $partner->commission_rate,
        ];

        $recentCommissions = PartnerCommission::where('partner_id', $partner->id)
            ->with('user:id,name,email')
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        $referredUsers = User::where('partner_id', $user->id)
            ->with(['wallet:id,user_id,balance,total_recharged'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $monthlyEarnings = PartnerCommission::where('partner_id', $partner->id)
            ->selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, SUM(commission) as total')
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('YEAR(created_at) DESC, MONTH(created_at) DESC')
            ->limit(6)
            ->get();

        return view('partner.dashboard', compact('partner', 'stats', 'recentCommissions', 'referredUsers', 'monthlyEarnings'));
    }

    public function apply()
    {
        $user = auth()->user();

        // Already a partner - go to dashboard
        if ($user->partner) {
            return redirect()->route('partner.dashboard');
        }

        return view('partner.apply');
    }

    public function submitApplication(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'type' => 'required|in:agency,reseller,influencer,technology,freelancer,consultant',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string|max:2000',
        ]);

        $user = auth()->user();

        if ($user->partner) {
            return redirect()->route('partner.dashboard')
                ->with('info', 'You already have a partner account.');
        }

        Partner::create([
            'user_id' => $user->id,
            'company_name' => $validated['company_name'],
            'type' => $validated['type'],
            'referral_code' => strtoupper(Str::random(8)),
            'commission_rate' => config('whatify.partner.default_commission', 20),
            'status' => 'pending',
            'payout_details' => [],
        ]);

        // Update role if not already partner
        if ($user->role === 'business_owner') {
            // Keep as business_owner but they can access partner features via relationship
            // OR switch to partner role if they prefer
        }

        return redirect()->route('partner.dashboard')
            ->with('success', 'Partner application submitted! We will review it within 24 hours.');
    }

    public function payouts()
    {
        $user = auth()->user();
        $partner = Partner::where('user_id', $user->id)->first();

        if (!$partner) {
            return redirect()->route('partner.apply');
        }

        $payouts = PartnerPayout::where('partner_id', $partner->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('partner.payouts', compact('partner', 'payouts'));
    }

    public function requestPayout(Request $request)
    {
        $user = auth()->user();
        $partner = Partner::where('user_id', $user->id)->firstOrFail();

        $minPayout = config('whatify.partner.min_payout', 1000);

        if ($partner->pending_payout < $minPayout) {
            return back()->with('error', "Minimum payout amount is ₹{$minPayout}. Current balance: ₹{$partner->pending_payout}");
        }

        $existingPending = PartnerPayout::where('partner_id', $partner->id)
            ->where('status', 'pending')
            ->exists();

        if ($existingPending) {
            return back()->with('error', 'You already have a pending payout request.');
        }

        PartnerPayout::create([
            'partner_id' => $partner->id,
            'amount' => $partner->pending_payout,
            'status' => 'pending',
            'payout_details' => $partner->payout_details,
        ]);

        return back()->with('success', "Payout request of ₹{$partner->pending_payout} submitted successfully.");
    }

    public function settings()
    {
        $user = auth()->user();
        $partner = Partner::where('user_id', $user->id)->first();

        if (!$partner) {
            return redirect()->route('partner.apply');
        }

        return view('partner.settings', compact('partner'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'ifsc_code' => 'nullable|string|max:20',
            'account_holder' => 'nullable|string|max:255',
            'upi_id' => 'nullable|string|max:100',
        ]);

        $user = auth()->user();
        $partner = Partner::where('user_id', $user->id)->firstOrFail();

        $partner->update([
            'company_name' => $validated['company_name'],
            'payout_details' => [
                'bank_name' => $validated['bank_name'],
                'account_number' => $validated['account_number'],
                'ifsc_code' => $validated['ifsc_code'],
                'account_holder' => $validated['account_holder'],
                'upi_id' => $validated['upi_id'],
            ],
        ]);

        return back()->with('success', 'Settings updated successfully.');
    }
}