<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\PartnerPayout;
use Illuminate\Http\Request;

class AdminPartnerController extends Controller
{
    public function index(Request $request)
    {
        $partners = Partner::with('user:id,name,email,phone')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->search, function ($q, $s) {
                $q->where('company_name', 'like', "%{$s}%")
                  ->orWhereHas('user', fn($q2) => $q2->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"));
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => Partner::count(),
            'approved' => Partner::where('status', 'approved')->count(),
            'pending' => Partner::where('status', 'pending')->count(),
            'total_paid' => Partner::sum('total_paid'),
            'pending_payouts' => PartnerPayout::where('status', 'pending')->sum('amount'),
        ];

        return view('admin.partners.index', compact('partners', 'stats'));
    }

    public function approve(Partner $partner)
    {
        $partner->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        app(\App\Services\NotificationService::class)->send(
            $partner->user,
            'Partner Application Approved!',
            "Congratulations! Your partner application has been approved. Your referral code is: {$partner->referral_code}",
            'success'
        );

        return back()->with('success', "Partner {$partner->company_name} approved.");
    }

    public function reject(Partner $partner)
    {
        $partner->update(['status' => 'rejected']);

        app(\App\Services\NotificationService::class)->send(
            $partner->user,
            'Partner Application Update',
            'Your partner application has been reviewed. Please contact support for details.',
            'info'
        );

        return back()->with('success', "Partner {$partner->company_name} rejected.");
    }

    public function updateCommission(Request $request, Partner $partner)
    {
        $validated = $request->validate([
            'commission_rate' => 'required|numeric|min:1|max:50',
        ]);

        $partner->update(['commission_rate' => $validated['commission_rate']]);

        return back()->with('success', "Commission rate updated to {$validated['commission_rate']}%.");
    }

    public function payouts(Request $request)
    {
        $payouts = PartnerPayout::with('partner.user:id,name,email')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.partners.payouts', compact('payouts'));
    }

    public function processPayout(Request $request, PartnerPayout $payout)
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'transaction_reference' => 'nullable|string|max:255',
        ]);

        if ($validated['action'] === 'approve') {
            $payout->update([
                'status' => 'completed',
                'transaction_reference' => $validated['transaction_reference'],
                'processed_at' => now(),
            ]);

            $partner = $payout->partner;
            $partner->decrement('pending_payout', $payout->amount);
            $partner->increment('total_paid', $payout->amount);

            app(\App\Services\NotificationService::class)->send(
                $partner->user,
                'Payout Processed',
                "Your payout of ₹{$payout->amount} has been processed. Reference: {$validated['transaction_reference']}",
                'wallet'
            );
        } else {
            $payout->update(['status' => 'failed']);
        }

        return back()->with('success', "Payout {$validated['action']}d.");
    }
}