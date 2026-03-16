@extends('layouts.app')
@section('title', 'Plans & Billing')
@section('page-title', 'Plans & Billing')

@section('content')
@php
    $currentSub = auth()->user()->getActiveSubscription();
    $currentPlanId = $currentSub?->plan_id;
@endphp

<div class="space-y-6">
    {{-- Current Plan Info --}}
    @if($currentSub)
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold">Current Plan: {{ $currentSub->plan->name }}</h3>
                    <p class="text-sm text-gray-500">
                        {{ $currentSub->isTrial() ? 'Trial' : 'Active' }} •
                        Expires: {{ $currentSub->ends_at->format('M d, Y') }} •
                        {{ $currentSub->daysRemaining() }} days remaining
                    </p>
                </div>
                <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium {{ $currentSub->isTrial() ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' }}">
                    {{ $currentSub->isTrial() ? 'Trial' : 'Active' }}
                </span>
            </div>
        </div>
    @endif

    {{-- Plans Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach(\App\Models\Plan::where('is_active', true)->orderBy('sort_order')->get() as $plan)
            <div class="bg-white rounded-lg shadow-lg overflow-hidden {{ $plan->id === $currentPlanId ? 'ring-2 ring-emerald-500' : '' }}">
                @if($plan->slug === 'growth')
                    <div class="bg-emerald-600 text-white text-center py-1 text-sm font-medium">Most Popular</div>
                @endif
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900">{{ $plan->name }}</h3>
                    <div class="mt-4">
                        <span class="text-4xl font-bold text-gray-900">₹{{ number_format($plan->price) }}</span>
                        <span class="text-gray-500">/month</span>
                    </div>
                    <ul class="mt-6 space-y-3">
                        <li class="flex items-center text-sm">
                            <i class="fas fa-check text-emerald-500 mr-3 w-4"></i>
                            {{ $plan->whatsapp_numbers == -1 ? 'Unlimited' : $plan->whatsapp_numbers }} WhatsApp Number(s)
                        </li>
                        <li class="flex items-center text-sm">
                            <i class="fas fa-check text-emerald-500 mr-3 w-4"></i>
                            {{ $plan->automation_flows == -1 ? 'Unlimited' : $plan->automation_flows }} Automation Flows
                        </li>
                        <li class="flex items-center text-sm">
                            <i class="fas fa-check text-emerald-500 mr-3 w-4"></i>
                            {{ $plan->agents == -1 ? 'Unlimited' : $plan->agents }} Agent(s)
                        </li>
                        <li class="flex items-center text-sm">
                            <i class="fas fa-check text-emerald-500 mr-3 w-4"></i>
                            {{ $plan->campaigns_per_month == -1 ? 'Unlimited' : $plan->campaigns_per_month }} Campaigns/Month
                        </li>
                        <li class="flex items-center text-sm">
                            <i class="fas fa-check text-emerald-500 mr-3 w-4"></i>
                            {{ $plan->contacts_limit == -1 ? 'Unlimited' : number_format($plan->contacts_limit) }} Contacts
                        </li>
                        <li class="flex items-center text-sm {{ $plan->shared_inbox ? '' : 'text-gray-400' }}">
                            <i class="fas {{ $plan->shared_inbox ? 'fa-check text-emerald-500' : 'fa-times text-gray-300' }} mr-3 w-4"></i>
                            Shared Inbox
                        </li>
                        <li class="flex items-center text-sm {{ $plan->flow_builder ? '' : 'text-gray-400' }}">
                            <i class="fas {{ $plan->flow_builder ? 'fa-check text-emerald-500' : 'fa-times text-gray-300' }} mr-3 w-4"></i>
                            Flow Builder
                        </li>
                        <li class="flex items-center text-sm {{ $plan->shopify_integration ? '' : 'text-gray-400' }}">
                            <i class="fas {{ $plan->shopify_integration ? 'fa-check text-emerald-500' : 'fa-times text-gray-300' }} mr-3 w-4"></i>
                            Shopify Integration
                        </li>
                        <li class="flex items-center text-sm {{ $plan->api_access ? '' : 'text-gray-400' }}">
                            <i class="fas {{ $plan->api_access ? 'fa-check text-emerald-500' : 'fa-times text-gray-300' }} mr-3 w-4"></i>
                            API Access
                        </li>
                        <li class="flex items-center text-sm {{ $plan->priority_support ? '' : 'text-gray-400' }}">
                            <i class="fas {{ $plan->priority_support ? 'fa-check text-emerald-500' : 'fa-times text-gray-300' }} mr-3 w-4"></i>
                            Priority Support
                        </li>
                    </ul>

                    <div class="mt-8">
                        @if($plan->id === $currentPlanId)
                            <button disabled class="w-full py-2 px-4 rounded-md text-sm font-medium bg-gray-100 text-gray-500 cursor-not-allowed">
                                Current Plan
                            </button>
                        @else
                            <form method="POST" action="{{ route('billing.subscribe') }}">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <button type="submit" class="w-full py-2 px-4 rounded-md text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700">
                                    {{ $plan->price > ($currentSub?->plan?->price ?? 0) ? 'Upgrade' : 'Switch' }} to {{ $plan->name }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection