<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    <meta name="author" content="Whatify">

    <title>@yield('title', 'WhatsApp Business API Automation Platform') - Whatify</title>
    <meta name="description" content="@yield('meta_description', 'Whatify - WhatsApp Business API automation platform. Send broadcasts, build chatbots, manage conversations and grow your business with WhatsApp marketing.')">
    <meta name="keywords" content="@yield('meta_keywords', 'whatsapp api, whatsapp business, whatsapp automation, whatsapp marketing, whatsapp chatbot, bulk whatsapp, whatsapp crm')">

    {{-- Open Graph --}}
    <meta property="og:title" content="@yield('og_title', 'Whatify - WhatsApp Business API Automation')">
    <meta property="og:description" content="@yield('meta_description', 'Send broadcasts, build chatbots and automate WhatsApp for your business.')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="@yield('og_image', asset('images/og-default.png'))">
    <meta property="og:site_name" content="Whatify">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', 'Whatify - WhatsApp Automation')">
    <meta name="twitter:description" content="@yield('meta_description')">

    {{-- Canonical --}}
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Hreflang --}}
    <link rel="alternate" hreflang="en" href="{{ url()->current() }}">

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Structured Data --}}
    @yield('schema')

    {{-- Organization Schema (all pages) --}}
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Whatify",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "description": "WhatsApp Business API automation platform for marketing, CRM and customer engagement",
        "url": "{{ config('app.url') }}",
        "offers": {
            "@type": "AggregateOffer",
            "priceCurrency": "INR",
            "lowPrice": "999",
            "highPrice": "9999"
        },
        "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": "4.8",
            "ratingCount": "250"
        }
    }
    </script>

    @stack('head')
</head>
<body class="antialiased">

    {{-- Navigation --}}
    @include('website.layouts.nav')

    {{-- Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('website.layouts.footer')

    {{-- WhatsApp Chat Widget --}}
    <a href="https://wa.me/919999999999?text=Hi%2C%20I%20want%20to%20know%20about%20Whatify"
       target="_blank"
       class="fixed bottom-6 right-6 h-14 w-14 bg-green-500 rounded-full flex items-center justify-center text-white shadow-lg hover:bg-green-600 transition-colors z-50">
        <i class="fab fa-whatsapp text-2xl"></i>
    </a>

    @stack('scripts')
</body>
</html>