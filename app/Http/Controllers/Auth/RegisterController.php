<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use App\Services\SubscriptionService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function __construct(
        protected WalletService $walletService,
        protected SubscriptionService $subscriptionService,
    ) {}

    public function showForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:15'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'referral_code' => ['nullable', 'string', 'exists:partners,referral_code'],
        ]);

        // Find partner if referral code provided
        $partnerId = null;
        if (!empty($validated['referral_code'])) {
            $partner = \App\Models\Partner::where('referral_code', $validated['referral_code'])
                ->where('status', 'approved')
                ->first();
            $partnerId = $partner?->user_id;
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => 'business_owner',
            'status' => 'active',
            'partner_id' => $partnerId,
        ]);

        // Create wallet
        $this->walletService->createWallet($user);

        // Start trial with starter plan
        $starterPlan = Plan::where('slug', 'starter')->first();
        if ($starterPlan) {
            $this->subscriptionService->createTrial($user, $starterPlan);
        }

        // Track partner referral
        if ($partnerId && isset($partner)) {
            $partner->increment('total_referrals');
        }

        Auth::login($user);

        \App\Services\ActivityLogger::log('user_registered');

        return redirect()->route('onboarding.index');
    }
}