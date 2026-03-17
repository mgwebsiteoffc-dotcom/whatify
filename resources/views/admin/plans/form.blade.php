@extends('layouts.app')
@section('title', $plan ? 'Edit Plan' : 'Create Plan')
@section('page-title', $plan ? 'Edit Plan: '.$plan->name : 'Create Plan')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ $plan ? route('admin.plans.update', $plan) : route('admin.plans.store') }}" class="space-y-5">
            @csrf
            @if($plan) @method('PUT') @endif

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Plan Name *</label>
                    <input type="text" name="name" value="{{ old('name', $plan?->name) }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Price (₹) *</label>
                    <input type="number" name="price" value="{{ old('price', $plan?->price) }}" required step="0.01" class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Billing Cycle</label>
                    <select name="billing_cycle" class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                        <option value="monthly" {{ ($plan?->billing_cycle ?? 'monthly') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="yearly" {{ ($plan?->billing_cycle) === 'yearly' ? 'selected' : '' }}>Yearly</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $plan?->sort_order ?? 0) }}" class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                </div>
            </div>

            <div class="border-t pt-4">
                <p class="text-sm font-medium text-gray-700 mb-3">Limits (-1 = unlimited)</p>
                <div class="grid grid-cols-3 gap-4">
                    @foreach([
                        ['whatsapp_numbers', 'WA Numbers', 1],
                        ['automation_flows', 'Automations', 5],
                        ['agents', 'Agents', 1],
                        ['campaigns_per_month', 'Campaigns/Month', 10],
                        ['contacts_limit', 'Contacts', 5000],
                        ['messages_per_month', 'Messages/Month', -1],
                    ] as [$field, $label, $default])
                        <div>
                            <label class="block text-xs text-gray-500">{{ $label }}</label>
                            <input type="number" name="{{ $field }}" value="{{ old($field, $plan?->$field ?? $default) }}" min="-1"
                                   class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="border-t pt-4">
                <p class="text-sm font-medium text-gray-700 mb-3">Features</p>
                <div class="grid grid-cols-2 gap-3">
                    @foreach([
                        ['shared_inbox', 'Shared Inbox', true],
                        ['flow_builder', 'Flow Builder', false],
                        ['api_access', 'API Access', false],
                        ['webhook_access', 'Webhook Access', false],
                        ['shopify_integration', 'Shopify Integration', false],
                        ['woocommerce_integration', 'WooCommerce Integration', false],
                        ['google_sheets_integration', 'Google Sheets Integration', true],
                        ['custom_integrations', 'Custom Integrations', false],
                        ['priority_support', 'Priority Support', false],
                        ['is_active', 'Plan Active', true],
                    ] as [$field, $label, $default])
                        <label class="flex items-center gap-2">
                            <input type="hidden" name="{{ $field }}" value="0">
                            <input type="checkbox" name="{{ $field }}" value="1"
                                   {{ old($field, $plan?->$field ?? $default) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-emerald-600">
                            <span class="text-sm">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex gap-3 pt-4 border-t">
                <button type="submit" class="px-6 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700">
                    {{ $plan ? 'Update Plan' : 'Create Plan' }}
                </button>
                <a href="{{ route('admin.plans.index') }}" class="px-6 py-2 border rounded-md text-gray-700 hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection