<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminPlanController extends Controller
{
    public function index()
    {
        $plans = Plan::withCount('subscriptions')
            ->orderBy('sort_order')
            ->get();

        return view('admin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.plans.form', ['plan' => null]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePlan($request);
        $validated['slug'] = Str::slug($validated['name']);

        Plan::create($validated);

        return redirect()->route('admin.plans.index')->with('success', 'Plan created.');
    }

    public function edit(Plan $plan)
    {
        return view('admin.plans.form', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $this->validatePlan($request);
        $plan->update($validated);

        return redirect()->route('admin.plans.index')->with('success', 'Plan updated.');
    }

    public function destroy(Plan $plan)
    {
        if ($plan->subscriptions()->exists()) {
            return back()->with('error', 'Cannot delete plan with active subscriptions.');
        }

        $plan->delete();
        return back()->with('success', 'Plan deleted.');
    }

    protected function validatePlan(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,yearly',
            'whatsapp_numbers' => 'required|integer|min:-1',
            'automation_flows' => 'required|integer|min:-1',
            'agents' => 'required|integer|min:-1',
            'campaigns_per_month' => 'required|integer|min:-1',
            'contacts_limit' => 'required|integer|min:-1',
            'messages_per_month' => 'required|integer|min:-1',
            'shared_inbox' => 'boolean',
            'flow_builder' => 'boolean',
            'api_access' => 'boolean',
            'webhook_access' => 'boolean',
            'shopify_integration' => 'boolean',
            'woocommerce_integration' => 'boolean',
            'google_sheets_integration' => 'boolean',
            'custom_integrations' => 'boolean',
            'priority_support' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'required|integer|min:0',
        ]);
    }
}