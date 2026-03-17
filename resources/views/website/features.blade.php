@extends('website.layouts.app')

@section('title', 'Features - WhatsApp Automation, Chatbots, CRM & Integrations')
@section('meta_description', 'Explore Whatify features: broadcast campaigns, chatbot flow builder, shared team inbox, contacts CRM, Shopify integration, analytics dashboard and more.')
@section('meta_keywords', 'whatsapp automation features, whatsapp chatbot builder, whatsapp shared inbox, whatsapp crm, whatsapp shopify integration')

@section('schema')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebPage",
    "name": "Whatify Features",
    "description": "Complete list of Whatify WhatsApp automation features",
    "url": "{{ route('website.features') }}",
    "breadcrumb": {
        "@type": "BreadcrumbList",
        "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "Home", "item": "{{ route('website.home') }}"},
            {"@type": "ListItem", "position": 2, "name": "Features", "item": "{{ route('website.features') }}"}
        ]
    }
}
</script>
@endsection

@section('content')

{{-- Hero --}}
<section class="bg-gradient-to-br from-emerald-50 to-white py-20">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <h1 class="text-4xl lg:text-5xl font-extrabold text-gray-900">Powerful Features for <span class="text-emerald-600">WhatsApp Marketing</span></h1>
        <p class="mt-6 text-lg text-gray-600 max-w-3xl mx-auto">Everything you need to automate customer communication, boost sales and deliver exceptional support on WhatsApp.</p>
    </div>
</section>

{{-- Feature Sections --}}
@foreach([
    [
        'id' => 'broadcasts',
        'badge' => 'Campaigns',
        'title' => 'Broadcast Campaigns That Convert',
        'desc' => 'Send personalized WhatsApp messages to thousands of customers simultaneously. Schedule campaigns, track delivery rates, and measure engagement — all from one dashboard.',
        'points' => ['Send to 10,000+ contacts at once', 'Schedule campaigns for optimal timing', 'Dynamic variables for personalization', 'Real-time delivery & read tracking', 'Audience segmentation by tags', 'Auto-pause on low wallet balance'],
        'icon' => 'fas fa-bullhorn',
        'color' => 'emerald',
        'reverse' => false,
    ],
    [
        'id' => 'chatbot',
        'badge' => 'Automation',
        'title' => 'Visual Chatbot Flow Builder',
        'desc' => 'Build interactive chatbots without coding. Our drag-and-drop flow builder lets you create complex conversation flows with conditions, buttons, API calls and more.',
        'points' => ['17+ step types (message, buttons, lists, conditions)', 'Keyword triggers with smart matching', 'Collect data with validated questions', 'Branch logic based on user responses', 'API calls and webhook integration', 'Transfer to human agent when needed'],
        'icon' => 'fas fa-robot',
        'color' => 'blue',
        'reverse' => true,
    ],
    [
        'id' => 'inbox',
        'badge' => 'Team Inbox',
        'title' => 'Shared Inbox for Your Entire Team',
        'desc' => 'Manage all WhatsApp conversations in one place. Assign chats to agents, add internal notes, and resolve customer issues collaboratively.',
        'points' => ['Real-time conversation management', 'Agent assignment and routing', 'Internal notes for team collaboration', 'Bot on/off toggle per conversation', 'Send text, templates, and media', 'Conversation status tracking'],
        'icon' => 'fas fa-inbox',
        'color' => 'purple',
        'reverse' => false,
    ],
    [
        'id' => 'crm',
        'badge' => 'CRM',
        'title' => 'Contacts CRM Built for WhatsApp',
        'desc' => 'Manage your customer database with rich contact profiles, custom attributes, tags, and smart segmentation for targeted campaigns.',
        'points' => ['Import contacts from CSV/Excel', 'Custom fields and attributes', 'Tag-based segmentation', 'Contact activity timeline', 'Bulk actions (tag, block, export)', 'Auto-create contacts from WhatsApp'],
        'icon' => 'fas fa-address-book',
        'color' => 'orange',
        'reverse' => true,
    ],
    [
        'id' => 'integrations',
        'badge' => 'Integrations',
        'title' => 'Connect Your Favorite Tools',
        'desc' => 'Integrate with Shopify, WooCommerce, Google Sheets and more. Automate order updates, cart recovery, and customer data sync.',
        'points' => ['Shopify: orders, carts, shipping, COD', 'WooCommerce: orders and customer sync', 'Google Sheets: import/export contacts', 'Webhook support for custom integrations', 'API access for developers', 'More integrations coming soon'],
        'icon' => 'fas fa-plug',
        'color' => 'pink',
        'reverse' => false,
    ],
    [
        'id' => 'analytics',
        'badge' => 'Analytics',
        'title' => 'Actionable Analytics & Insights',
        'desc' => 'Track every message, campaign and conversation with detailed analytics. Understand your performance and optimize your WhatsApp strategy.',
        'points' => ['Message delivery and read rates', 'Campaign performance tracking', 'Contact growth analytics', 'Spending and wallet reports', 'Peak hours analysis', 'Top campaigns comparison'],
        'icon' => 'fas fa-chart-bar',
        'color' => 'cyan',
        'reverse' => true,
    ],
] as $feature)
    <section class="py-20 {{ $loop->even ? 'bg-gray-50' : '' }}" id="{{ $feature['id'] }}">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-center {{ $feature['reverse'] ? 'lg:flex-row-reverse' : '' }}">
                <div class="{{ $feature['reverse'] ? 'lg:order-2' : '' }}">
                    <span class="inline-flex items-center gap-2 bg-{{ $feature['color'] }}-100 text-{{ $feature['color'] }}-700 px-3 py-1 rounded-full text-xs font-medium uppercase tracking-wider">
                        <i class="{{ $feature['icon'] }} text-xs"></i> {{ $feature['badge'] }}
                    </span>
                    <h2 class="mt-4 text-3xl lg:text-4xl font-bold text-gray-900">{{ $feature['title'] }}</h2>
                    <p class="mt-4 text-lg text-gray-600 leading-relaxed">{{ $feature['desc'] }}</p>
                    <ul class="mt-8 space-y-3">
                        @foreach($feature['points'] as $point)
                            <li class="flex items-start gap-3 text-gray-700">
                                <i class="fas fa-check-circle text-{{ $feature['color'] }}-500 mt-0.5 flex-shrink-0"></i>
                                <span>{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-8">
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-{{ $feature['color'] }}-600 text-white rounded-lg font-semibold hover:bg-{{ $feature['color'] }}-700 transition-colors">
                            Try It Free <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="{{ $feature['reverse'] ? 'lg:order-1' : '' }}">
                    <div class="bg-white rounded-2xl shadow-xl p-8 border">
                        <div class="h-64 bg-{{ $feature['color'] }}-50 rounded-xl flex items-center justify-center">
                            <i class="{{ $feature['icon'] }} text-8xl text-{{ $feature['color'] }}-200"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endforeach

{{-- CTA --}}
<section class="py-20 bg-emerald-600">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl lg:text-4xl font-bold text-white">Start Using All Features Today</h2>
        <p class="mt-4 text-lg text-emerald-100">14-day free trial. No credit card required.</p>
        <a href="{{ route('register') }}" class="mt-8 inline-flex items-center gap-2 px-8 py-4 bg-white text-emerald-700 text-lg font-semibold rounded-xl hover:bg-emerald-50 shadow-lg">
            Start Free Trial <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</section>

@endsection