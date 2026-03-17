@extends('website.layouts.app')

@section('title', 'WhatsApp Business API Automation Platform')
@section('meta_description', 'Whatify helps businesses automate WhatsApp messaging with broadcasts, chatbots, shared inbox and CRM. Connect WhatsApp API in minutes. Start free trial.')
@section('meta_keywords', 'whatsapp business api, whatsapp automation, whatsapp marketing platform, whatsapp chatbot builder, bulk whatsapp sender, whatsapp crm')

@section('schema')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebPage",
    "name": "Whatify - WhatsApp Business API Automation Platform",
    "description": "Automate WhatsApp messaging with broadcasts, chatbots, shared inbox and CRM integration.",
    "url": "{{ route('website.home') }}",
    "mainEntity": {
        "@type": "SoftwareApplication",
        "name": "Whatify",
        "applicationCategory": "BusinessApplication",
        "offers": {
            "@type": "AggregateOffer",
            "priceCurrency": "INR",
            "lowPrice": "999"
        }
    }
}
</script>
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
        {"@type":"Question","name":"What is Whatify?","acceptedAnswer":{"@type":"Answer","text":"Whatify is a WhatsApp Business API automation platform that helps businesses send broadcasts, build chatbots, manage conversations and integrate with eCommerce tools like Shopify and WooCommerce."}},
        {"@type":"Question","name":"How much does Whatify cost?","acceptedAnswer":{"@type":"Answer","text":"Whatify plans start from ₹999/month with a 14-day free trial. You pay for messages separately through a wallet system at ₹0.50-₹0.90 per message."}},
        {"@type":"Question","name":"Can I connect my Shopify store?","acceptedAnswer":{"@type":"Answer","text":"Yes! Whatify integrates with Shopify for order confirmations, shipping updates, abandoned cart recovery and COD verification through WhatsApp."}},
        {"@type":"Question","name":"Do I need a WhatsApp Business API to use Whatify?","acceptedAnswer":{"@type":"Answer","text":"Whatify helps you connect to the WhatsApp Business API through Meta's official process. You can set up your number directly from the platform."}}
    ]
}
</script>
@endsection

@section('content')

{{-- Hero Section --}}
<section class="relative overflow-hidden bg-gradient-to-br from-emerald-50 via-white to-green-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <div class="inline-flex items-center gap-2 bg-emerald-100 text-emerald-700 px-4 py-1.5 rounded-full text-sm font-medium mb-6">
                    <i class="fas fa-bolt"></i> Official WhatsApp Business Solution Provider
                </div>
                <h1 class="text-4xl lg:text-5xl xl:text-6xl font-extrabold text-gray-900 leading-tight">
                    Automate Your Business on
                    <span class="text-emerald-600">WhatsApp</span>
                </h1>
                <p class="mt-6 text-lg text-gray-600 leading-relaxed max-w-xl">
                    Send broadcast campaigns, build chatbots, manage conversations with shared inbox, and integrate with Shopify & WooCommerce — all from one powerful platform.
                </p>
                <div class="mt-8 flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('register') }}" class="px-8 py-4 bg-emerald-600 text-white text-lg font-semibold rounded-xl hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-200 text-center">
                        Start 14-Day Free Trial <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                    <a href="{{ route('website.features') }}" class="px-8 py-4 border-2 border-gray-300 text-gray-700 text-lg font-semibold rounded-xl hover:border-emerald-500 hover:text-emerald-600 transition-colors text-center">
                        <i class="fas fa-play-circle mr-2"></i> See How It Works
                    </a>
                </div>
                <div class="mt-8 flex items-center gap-6 text-sm text-gray-500">
                    <span><i class="fas fa-check text-emerald-500 mr-1"></i> No credit card required</span>
                    <span><i class="fas fa-check text-emerald-500 mr-1"></i> Setup in 5 minutes</span>
                    <span><i class="fas fa-check text-emerald-500 mr-1"></i> Cancel anytime</span>
                </div>
            </div>
            <div class="relative">
                <div class="bg-white rounded-2xl shadow-2xl p-6 transform rotate-1 hover:rotate-0 transition-transform">
                    <div class="bg-[#ECE5DD] rounded-xl p-4 space-y-3">
                        <div class="bg-white rounded-lg p-3 max-w-[80%] shadow-sm">
                            <p class="text-sm">Hi! 👋 Welcome to our store. How can I help you?</p>
                            <p class="text-[10px] text-gray-400 text-right mt-1">10:30 AM</p>
                        </div>
                        <div class="bg-[#DCF8C6] rounded-lg p-3 max-w-[70%] ml-auto shadow-sm">
                            <p class="text-sm">I want to check my order status</p>
                            <p class="text-[10px] text-gray-400 text-right mt-1">10:31 AM ✓✓</p>
                        </div>
                        <div class="bg-white rounded-lg p-3 max-w-[80%] shadow-sm">
                            <p class="text-sm">Sure! 📦 Your order #1234 has been shipped. Track here: <span class="text-blue-500 underline">tracking.link/1234</span></p>
                            <p class="text-[10px] text-gray-400 text-right mt-1">10:31 AM</p>
                        </div>
                        <div class="flex gap-2 max-w-[80%]">
                            <div class="bg-white rounded-lg py-2 px-4 text-center text-sm text-blue-500 font-medium shadow-sm flex-1">📦 Track Order</div>
                            <div class="bg-white rounded-lg py-2 px-4 text-center text-sm text-blue-500 font-medium shadow-sm flex-1">💬 Support</div>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-3 text-xs text-gray-400">
                        <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-green-500"></span> Bot Active</span>
                        <span>Avg response: 2 seconds</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Trusted By --}}
<section class="py-12 bg-gray-50 border-y">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <p class="text-sm text-gray-500 mb-6 uppercase tracking-wider font-medium">Trusted by 500+ businesses across India</p>
        <div class="flex flex-wrap justify-center gap-8 items-center opacity-40">
            @for($i = 1; $i <= 6; $i++)
                <div class="h-8 w-24 bg-gray-300 rounded"></div>
            @endfor
        </div>
    </div>
</section>

{{-- Features Overview --}}
<section class="py-20" id="features">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900">Everything You Need for WhatsApp Marketing</h2>
            <p class="mt-4 text-lg text-gray-600">One platform to manage all your WhatsApp business communications</p>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach([
                ['fas fa-bullhorn', 'Broadcast Campaigns', 'Send personalized template messages to thousands of customers at once with scheduling and analytics.', 'emerald'],
                ['fas fa-robot', 'Chatbot Flow Builder', 'Build interactive chatbots with our visual drag-and-drop flow builder. No coding required.', 'blue'],
                ['fas fa-inbox', 'Shared Team Inbox', 'Collaborate with your team on customer conversations. Assign chats, add notes, and resolve issues.', 'purple'],
                ['fas fa-address-book', 'Contacts CRM', 'Manage customer data with tags, custom fields, import/export and smart segmentation.', 'orange'],
                ['fas fa-plug', 'Shopify & WooCommerce', 'Connect your online store for automated order updates, cart recovery and COD verification.', 'pink'],
                ['fas fa-chart-bar', 'Analytics Dashboard', 'Track message delivery, read rates, campaign performance and spending in real-time.', 'cyan'],
                ['fas fa-wallet', 'Wallet Billing', 'Pay-as-you-go pricing with wallet system. Recharge and send — transparent per-message costs.', 'amber'],
                ['fas fa-file-alt', 'Template Manager', 'Create, submit and manage WhatsApp message templates with live preview.', 'indigo'],
                ['fas fa-handshake', 'Partner/Reseller Panel', 'White-label partner program with commission tracking, referral links and payout management.', 'rose'],
            ] as [$icon, $title, $desc, $color])
                <div class="bg-white rounded-xl p-6 border hover:shadow-lg transition-shadow group">
                    <div class="h-12 w-12 rounded-lg bg-{{ $color }}-100 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <i class="{{ $icon }} text-{{ $color }}-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $title }}</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- How It Works --}}
<section class="py-20 bg-emerald-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900">Get Started in 3 Simple Steps</h2>
            <p class="mt-4 text-lg text-gray-600">Go from signup to sending your first WhatsApp campaign in minutes</p>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            @foreach([
                ['1', 'Connect WhatsApp', 'Sign up and connect your WhatsApp Business API number in just a few clicks.', 'fab fa-whatsapp'],
                ['2', 'Import Contacts', 'Upload your customer contacts via CSV or sync from Shopify/WooCommerce.', 'fas fa-file-import'],
                ['3', 'Start Campaigns', 'Create broadcast campaigns, build chatbots and start engaging customers.', 'fas fa-rocket'],
            ] as [$num, $title, $desc, $icon])
                <div class="text-center">
                    <div class="h-20 w-20 mx-auto rounded-full bg-emerald-600 flex items-center justify-center text-white text-3xl font-bold mb-6 shadow-lg shadow-emerald-200">
                        {{ $num }}
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $title }}</h3>
                    <p class="text-gray-600">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
        <div class="text-center mt-12">
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-emerald-600 text-white text-lg font-semibold rounded-xl hover:bg-emerald-700 shadow-lg">
                Get Started Free <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

{{-- Industries --}}
<section class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900">Built for Every Industry</h2>
            <p class="mt-4 text-lg text-gray-600">Pre-built automation templates and workflows for your business type</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach([
                ['ecommerce', '🛒', 'E-Commerce'],
                ['education', '🎓', 'Education'],
                ['healthcare', '🏥', 'Healthcare'],
                ['real-estate', '🏠', 'Real Estate'],
                ['restaurant', '🍽️', 'Restaurants'],
                ['travel', '✈️', 'Travel'],
            ] as [$slug, $emoji, $label])
                <a href="{{ route('website.usecases.show', $slug) }}"
                   class="bg-white rounded-xl p-6 border text-center hover:shadow-lg hover:border-emerald-300 transition-all group">
                    <div class="text-4xl mb-3">{{ $emoji }}</div>
                    <h3 class="text-sm font-semibold text-gray-900 group-hover:text-emerald-600">{{ $label }}</h3>
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- Pricing Preview --}}
<section class="py-20 bg-gray-50" id="pricing">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900">Simple, Transparent Pricing</h2>
            <p class="mt-4 text-lg text-gray-600">Start free, upgrade as you grow. Pay only for messages sent.</p>
        </div>
        <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            @foreach($plans as $plan)
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden {{ $plan->slug === 'growth' ? 'ring-2 ring-emerald-500 relative' : '' }}">
                    @if($plan->slug === 'growth')
                        <div class="bg-emerald-600 text-white text-center py-1.5 text-sm font-medium">⭐ Most Popular</div>
                    @endif
                    <div class="p-8">
                        <h3 class="text-xl font-bold text-gray-900">{{ $plan->name }}</h3>
                        <div class="mt-4">
                            <span class="text-4xl font-extrabold text-gray-900">₹{{ number_format($plan->price) }}</span>
                            <span class="text-gray-500">/month</span>
                        </div>
                        <ul class="mt-6 space-y-3">
                            @foreach([
                                ($plan->whatsapp_numbers == -1 ? 'Unlimited' : $plan->whatsapp_numbers) . ' WhatsApp Number(s)',
                                ($plan->agents == -1 ? 'Unlimited' : $plan->agents) . ' Team Agent(s)',
                                ($plan->automation_flows == -1 ? 'Unlimited' : $plan->automation_flows) . ' Automation Flows',
                                ($plan->campaigns_per_month == -1 ? 'Unlimited' : $plan->campaigns_per_month) . ' Campaigns/Month',
                                ($plan->contacts_limit == -1 ? 'Unlimited' : number_format($plan->contacts_limit)) . ' Contacts',
                            ] as $feature)
                                <li class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-check text-emerald-500 mr-3 w-4"></i>{{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                        <a href="{{ route('register') }}" class="mt-8 block text-center px-6 py-3 rounded-xl font-semibold transition-colors
                            {{ $plan->slug === 'growth' ? 'bg-emerald-600 text-white hover:bg-emerald-700' : 'border-2 border-emerald-600 text-emerald-600 hover:bg-emerald-50' }}">
                            Start Free Trial
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
        <p class="text-center mt-8 text-gray-500 text-sm">All plans include a 14-day free trial. Messages charged separately via wallet. <a href="{{ route('website.pricing') }}" class="text-emerald-600 underline">View full pricing →</a></p>
    </div>
</section>

{{-- FAQ --}}
<section class="py-20">
    <div class="max-w-3xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-gray-900 text-center mb-12">Frequently Asked Questions</h2>
        <div class="space-y-4" x-data="{ open: null }">
            @foreach([
                ['What is Whatify?', 'Whatify is a WhatsApp Business API automation platform. It helps businesses send broadcast campaigns, build chatbots, manage customer conversations, and integrate with e-commerce tools like Shopify and WooCommerce.'],
                ['How much does it cost?', 'Platform plans start from ₹999/month. Messages are charged separately through a wallet system — Marketing messages at ₹0.90, Utility at ₹0.50, and Authentication at ₹0.30 per message.'],
                ['Do I need technical skills?', 'No! Whatify is designed for non-technical users. Our visual chatbot builder, campaign manager and CRM are all easy to use without any coding knowledge.'],
                ['Can I integrate with my Shopify store?', 'Yes! Whatify offers native Shopify integration for order confirmations, shipping updates, abandoned cart recovery and COD verification — all automated through WhatsApp.'],
                ['Is there a free trial?', 'Yes, all plans come with a 14-day free trial. No credit card required. You can explore all features before committing.'],
                ['What is WhatsApp Business API?', 'WhatsApp Business API is the official way for medium and large businesses to communicate with customers on WhatsApp at scale. It enables automated messages, chatbots, and integration with business tools.'],
            ] as $index => [$question, $answer])
                <div class="border rounded-xl overflow-hidden">
                    <button @click="open = open === {{ $index }} ? null : {{ $index }}"
                            class="w-full flex justify-between items-center px-6 py-4 text-left font-medium text-gray-900 hover:bg-gray-50">
                        {{ $question }}
                        <i class="fas fa-chevron-down text-gray-400 transition-transform" :class="open === {{ $index }} ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open === {{ $index }}" x-cloak x-collapse>
                        <div class="px-6 pb-4 text-gray-600 text-sm leading-relaxed">{{ $answer }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="py-20 bg-emerald-600">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl lg:text-4xl font-bold text-white">Ready to Grow Your Business with WhatsApp?</h2>
        <p class="mt-4 text-lg text-emerald-100">Join 500+ businesses already using Whatify to engage customers on WhatsApp.</p>
        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('register') }}" class="px-8 py-4 bg-white text-emerald-700 text-lg font-semibold rounded-xl hover:bg-emerald-50 transition-colors shadow-lg">
                Start Free Trial — No Card Required
            </a>
            <a href="https://wa.me/919999999999" target="_blank" class="px-8 py-4 border-2 border-white text-white text-lg font-semibold rounded-xl hover:bg-emerald-700 transition-colors">
                <i class="fab fa-whatsapp mr-2"></i> Chat With Us
            </a>
        </div>
    </div>
</section>

{{-- Blog Preview --}}
@if($posts->isNotEmpty())
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900">Latest from Our Blog</h2>
            <a href="{{ route('website.blog') }}" class="text-emerald-600 font-medium hover:underline">View all posts →</a>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            @foreach($posts as $post)
                <article class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition-shadow">
                    @if($post->featured_image)
                        <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-48 object-cover">
                    @else
                        <div class="w-full h-48 bg-emerald-100 flex items-center justify-center">
                            <i class="fas fa-newspaper text-4xl text-emerald-300"></i>
                        </div>
                    @endif
                    <div class="p-6">
                        @if($post->category)
                            <span class="text-xs font-medium text-emerald-600 uppercase">{{ $post->category->name }}</span>
                        @endif
                        <h3 class="text-lg font-semibold text-gray-900 mt-1 mb-2 line-clamp-2">
                            <a href="{{ $post->url }}" class="hover:text-emerald-600">{{ $post->title }}</a>
                        </h3>
                        <p class="text-sm text-gray-600 line-clamp-2">{{ $post->excerpt }}</p>
                        <div class="mt-4 flex items-center justify-between text-xs text-gray-400">
                            <span>{{ $post->published_at?->format('M d, Y') }}</span>
                            <span>{{ $post->read_time }} min read</span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

@endsection