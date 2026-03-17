@extends('website.layouts.app')

@section('title', 'About Us - Whatify')
@section('meta_description', 'Learn about Whatify — the WhatsApp Business API automation platform helping businesses grow with smart messaging, chatbots and integrations.')

@section('content')

<section class="bg-gradient-to-br from-emerald-50 to-white py-20">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h1 class="text-4xl lg:text-5xl font-extrabold text-gray-900">About <span class="text-emerald-600">Whatify</span></h1>
        <p class="mt-6 text-lg text-gray-600">We are building the most powerful and easy-to-use WhatsApp Business API automation platform for businesses of all sizes.</p>
    </div>
</section>

<section class="py-20">
    <div class="max-w-4xl mx-auto px-4 space-y-12">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Our Mission</h2>
            <p class="text-gray-600 leading-relaxed">
                Whatify was built to make WhatsApp Business API accessible to every business — from local shops to large enterprises.
                We believe that every business deserves the power of automated, personalized customer communication on the world's most popular messaging platform.
            </p>
        </div>
        <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-4">What We Offer</h2>
            <div class="grid md:grid-cols-2 gap-6">
                @foreach([
                    ['Official BSP', 'As an official WhatsApp Business Solution Provider, we provide reliable, compliant API access.'],
                    ['All-in-One Platform', 'Campaigns, chatbots, shared inbox, CRM and integrations — everything in one place.'],
                    ['Pay-Per-Message', 'Transparent wallet-based billing. No hidden fees. Pay only for what you use.'],
                    ['Made for India', 'Built with Indian businesses in mind. INR pricing, local payment gateways, regional language support.'],
                ] as [$title, $desc])
                    <div class="bg-white rounded-xl p-6 border">
                        <h3 class="font-semibold text-gray-900 mb-2">{{ $title }}</h3>
                        <p class="text-sm text-gray-600">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="text-center pt-8">
            <a href="{{ route('register') }}" class="px-8 py-4 bg-emerald-600 text-white text-lg font-semibold rounded-xl hover:bg-emerald-700 shadow-lg">
                Start Your Free Trial
            </a>
        </div>
    </div>
</section>

@endsection