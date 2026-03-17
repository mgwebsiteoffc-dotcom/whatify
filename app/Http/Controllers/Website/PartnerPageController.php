<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PartnerPageController extends Controller
{
    public function index()
    {
        $partnerCount = Partner::where('status', 'approved')->count();

        return view('website.partner', [
            'partnerCount' => $partnerCount,
        ]);
    }

    public function apply(Request $request)
    {
        $isGuest = !Auth::check();

        $rules = [
            'company_name' => 'required|string|max:255',
            'type' => 'required|in:agency,reseller,influencer,freelancer,technology,consultant',
            'website' => 'nullable|url|max:255',
            'description' => 'required|string|max:2000',
            'expected_referrals' => 'nullable|string|max:20',
        ];

        if ($isGuest) {
            $rules['name'] = 'required|string|max:255';
            $rules['email'] = 'required|email|max:255|unique:users,email';
            $rules['phone'] = 'required|string|max:15';
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $validated = $request->validate($rules);

        if ($isGuest) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'role' => 'partner',
                'status' => 'active',
                'is_onboarded' => true,
                'onboarding_step' => 4,
            ]);

            app(WalletService::class)->createWallet($user);
            Auth::login($user);
        } else {
            $user = Auth::user();

            if ($user->partner) {
                return redirect()->route('partner.dashboard')
                    ->with('info', 'You already have a partner account.');
            }
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

        if ($user->role !== 'partner') {
            $user->update(['role' => 'partner']);
        }

        return redirect()->route('partner.dashboard')
            ->with('success', 'Partner application submitted successfully! We will review it within 24 hours.');
    }
}