@extends('website.layouts.app')

@section('title', 'Pricing Plans - Affordable WhatsApp Business API')
@section('meta_description', 'Whatify pricing starts at ₹999/month. Pay-per-message wallet billing. Compare Starter, Growth and Pro plans. 14-day free trial included.')
@section('meta_keywords', 'whatify pricing, whatsapp api pricing india, whatsapp marketing cost, whatsapp business api price')

@section('schema')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebPage",
    "name": "Whatify Pricing",
    "url": "{{ route('website.pricing') }}",
    "breadcrumb": {
        "@type": "BreadcrumbList",
        "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "Home", "item": "{{ route('website.home') }}"},
            {"@type": "ListItem", "position": 2, "name": "Pricing", "item": "{{ route('website.pricing') }}"}
        ]
    }
}
</script>
@endsection

@section('content')

<section class="bg-gradient-to-br from-emerald-50 to-white py-20">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <h1 class="text-4xl lg:text-5xl font-extrabold text-gray-900">Simple, Transparent <span class="text-emerald-600">Pricing</span></h1>
        <p class="mt-6 text-lg text-gray-600 max-w-2xl mx-auto">Start with a 14-day free trial. No credit card required. Upgrade or downgrade anytime.</p>
    </div>
</section>

{{-- Plans --}}
<section class="py-20 -mt-10">
    <div class="max-w-6xl mx-auto px-4">
        <div class="grid md:grid-cols-3 gap-8">
            @foreach($plans as $plan)
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden {{ $plan->slug === 'growth' ? 'ring-2 ring-emerald-500 relative scale-105' : '' }}">
                    @if($plan->slug === 'growth')
                        <div class="bg-emerald-600 text-white text-center py-2 text-sm font-semibold">⭐ Most Popular</div>
                    @endif
                    <div class="p-8">
                        <h3 class="text-2xl font-bold text-gray-900">{{ $plan->name }}</h3>
                        <div class="mt-4">
                            <span class="text-5xl font-extrabold text-gray-900">₹{{ number_format($plan->price) }}</span>
                            <span class="text-gray-500 text-lg">/month</span>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">+ message costs via wallet</p>

                        <ul class="mt-8 space-y-4">
                            @php
                                $features = [
                                    ['WhatsApp Numbers', $plan->whatsapp_numbers],
                                    ['Automation Flows', $plan->automation_flows],
                                    ['Team Agents', $plan->agents],
                                    ['Campaigns/Month', $plan->campaigns_per_month],
                                    ['Contacts', $plan->contacts_limit],
                                ];
                                $boolFeatures = [
                                    ['Shared Inbox', $plan->shared_inbox],
                                    ['Flow Builder', $plan->flow_builder],
                                    ['API Access', $plan->api_access],
                                    ['Shopify Integration', $plan->shopify_integration],
                                    ['WooCommerce', $plan->woocommerce_integration],
                                    ['Google Sheets', $plan->google_sheets_integration],
                                    ['Priority Support', $plan->priority_support],
                                ];
                            @endphp

                            @foreach($features as [$label, $value])
                                <li class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">{{ $label }}</span>
                                    <span class="font-semibold text-gray-900">{{ $value == -1 ? 'Unlimited' : number_format($value) }}</span>
                                </li>
                            @endforeach

                            <li class="border-t pt-4"></li>

                            @foreach($boolFeatures as [$label, $enabled])
                                <li class="flex items-center gap-2 text-sm {{ $enabled ? 'text-gray-700' : 'text-gray-400' }}">
                                    <i class="fas {{ $enabled ? 'fa-check-circle text-emerald-500' : 'fa-times-circle text-gray-300' }} w-4"></i>
                                    {{ $label }}
                                </li>
                            @endforeach
                        </ul>

                        <a href="{{ route('register') }}" class="mt-8 block text-center px-6 py-3.5 rounded-xl font-semibold text-lg transition-colors
                            {{ $plan->slug === 'growth' ? 'bg-emerald-600 text-white hover:bg-emerald-700 shadow-lg' : 'border-2 border-emerald-600 text-emerald-600 hover:bg-emerald-50' }}">
                            Start Free Trial
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Message Pricing --}}
<section class="py-20 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900">Message Pricing (Pay-Per-Message)</h2>
            <p class="mt-4 text-gray-600">Recharge your wallet and pay only for messages sent. No hidden charges.</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            @foreach([
                ['Marketing', '₹0.90', 'Promotions, offers, newsletters', 'fas fa-bullhorn', 'purple'],
                ['Utility', '₹0.50', 'Order updates, reminders', 'fas fa-bell', 'blue'],
                ['Authentication', '₹0.30', 'OTP, login verification', 'fas fa-shield-alt', 'orange'],
                ['Service', 'Free*', 'Replies within 24hr window', 'fas fa-comment', 'green'],
            ] as [$type, $price, $desc, $icon, $color])
                <div class="bg-white rounded-xl p-6 text-center shadow-sm border">
                    <div class="h-12 w-12 mx-auto rounded-lg bg-{{ $color }}-100 flex items-center justify-center mb-4">
                        <i class="{{ $icon }} text-{{ $color }}-600 text-xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900">{{ $type }}</h3>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $price }}</p>
                    <p class="text-xs text-gray-500 mt-1">per message</p>
                    <p class="text-xs text-gray-400 mt-3">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
        <p class="text-center text-xs text-gray-400 mt-6">*Service messages are free within Meta's 24-hour conversation window. Prices exclude GST.</p>
    </div>
</section>

{{-- FAQ --}}
<section class="py-20">
    <div class="max-w-3xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-gray-900 text-center mb-12">Pricing FAQ</h2>
        <div class="space-y-4" x-data="{ open: null }">
            @foreach([
                ['Is there a free trial?', 'Yes! All plans include a 14-day free trial. No credit card required. You get full access to all features during the trial period.'],
                ['How does wallet billing work?', 'You recharge your wallet with any amount (min ₹500). When you send messages, the cost is automatically deducted. You can track all transactions in real-time.'],
                ['Can I change plans?', 'Yes, you can upgrade or downgrade your plan anytime. Changes take effect immediately.'],
                ['What payment methods are supported?', 'We support UPI, credit/debit cards, net banking through Razorpay, and international payments through Stripe.'],
                ['Are there any hidden fees?', 'No hidden fees. You pay the monthly plan fee + per-message costs. That is all.'],
                ['What happens if my wallet runs out?', 'Messages will pause until you recharge. We send low-balance alerts before this happens.'],
            ] as $i => [$q, $a])
                <div class="border rounded-xl">
                    <button @click="open = open === {{ $i }} ? null : {{ $i }}" class="w-full flex justify-between items-center px-6 py-4 text-left font-medium text-gray-900 hover:bg-gray-50">
                        {{ $q }}
                        <i class="fas fa-chevron-down text-gray-400 transition-transform" :class="open === {{ $i }} ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open === {{ $i }}" x-cloak x-collapse>
                        <p class="px-6 pb-4 text-gray-600 text-sm">{{ $a }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="py-16 bg-emerald-600">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold text-white">Start Your Free Trial Today</h2>
        <p class="mt-4 text-emerald-100">No credit card required. Cancel anytime.</p>
        <a href="{{ route('register') }}" class="mt-8 inline-flex px-8 py-4 bg-white text-emerald-700 text-lg font-semibold rounded-xl hover:bg-emerald-50 shadow-lg">
            Get Started Free <i class="fas fa-arrow-right ml-2"></i>
        </a>
    </div>
</section>

@endsection