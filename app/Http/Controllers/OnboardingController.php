<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Plan;
use App\Models\Team;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->is_onboarded) {
            return redirect()->route('dashboard');
        }

        $step = $user->onboarding_step;

        return match($step) {
            0 => $this->stepBusinessProfile(),
            1 => $this->stepIndustry(),
            2 => $this->stepPlan(),
            3 => $this->stepWhatsApp(),
            default => redirect()->route('dashboard'),
        };
    }

    // Step 0: Business Profile
    protected function stepBusinessProfile()
    {
        return view('onboarding.business-profile');
    }

    public function saveBusinessProfile(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'display_name' => 'nullable|string|max:255',
            'website' => 'nullable|url',
            'business_size' => 'required|in:1-10,11-50,51-200,201-500,500+',
            'logo' => 'nullable|image|max:2048',
        ]);

        $user = auth()->user();

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        Business::updateOrCreate(
            ['user_id' => $user->id],
            array_merge($validated, ['industry' => 'other'])
        );

        // Create default team
        Team::firstOrCreate(
            ['user_id' => $user->id],
            ['name' => $validated['company_name'] . ' Team']
        );

        $user->update(['onboarding_step' => 1]);

        return redirect()->route('onboarding.index');
    }

    // Step 1: Industry Selection
    protected function stepIndustry()
    {
        $industries = config('whatify.industries');
        return view('onboarding.industry', compact('industries'));
    }

    public function saveIndustry(Request $request)
    {
        $validated = $request->validate([
            'industry' => 'required|string|in:' . implode(',', array_keys(config('whatify.industries'))),
        ]);

        $user = auth()->user();
        $user->business->update(['industry' => $validated['industry']]);
        $user->update(['onboarding_step' => 2]);

        return redirect()->route('onboarding.index');
    }

    // Step 2: Plan Selection
    protected function stepPlan()
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        return view('onboarding.plan', compact('plans'));
    }

    public function savePlan(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $user = auth()->user();
        $plan = Plan::findOrFail($validated['plan_id']);

        // For now, start with trial. Payment handled later.
        app(\App\Services\SubscriptionService::class)->createTrial($user, $plan);

        $user->update(['onboarding_step' => 3]);

        return redirect()->route('onboarding.index');
    }

    // Step 3: WhatsApp Connection (optional, can skip)
    protected function stepWhatsApp()
    {
        return view('onboarding.whatsapp');
    }

    public function skipWhatsApp()
    {
        $user = auth()->user();
        $user->update([
            'is_onboarded' => true,
            'onboarding_step' => 4,
            'status' => 'active',
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Welcome to Whatify! Your account is ready.');
    }

    public function completeOnboarding()
    {
        $user = auth()->user();
        $user->update([
            'is_onboarded' => true,
            'onboarding_step' => 4,
            'status' => 'active',
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Welcome to Whatify! Your account is ready.');
    }
}