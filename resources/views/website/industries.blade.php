@extends('website.layouts.app')

@section('title', 'Industries - WhatsApp Automation Solutions by Industry')
@section('meta_description', 'Whatify provides industry-specific WhatsApp automation solutions for e-commerce, education, healthcare, real estate, restaurants and travel businesses.')

@section('content')

<section class="bg-gradient-to-br from-emerald-50 to-white py-20">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <h1 class="text-4xl lg:text-5xl font-extrabold text-gray-900">Industry <span class="text-emerald-600">Solutions</span></h1>
        <p class="mt-6 text-lg text-gray-600 max-w-3xl mx-auto">Pre-built WhatsApp automation templates and workflows designed specifically for your industry.</p>
    </div>
</section>

<section class="py-20">
    <div class="max-w-7xl mx-auto px-4 space-y-16">
        @foreach($useCases as $slug => $uc)
            <div class="grid lg:grid-cols-2 gap-12 items-center {{ $loop->even ? '' : '' }}">
                <div class="{{ $loop->even ? 'lg:order-2' : '' }}">
                    <div class="text-5xl mb-4">{{ $uc['emoji'] }}</div>
                    <h2 class="text-3xl font-bold text-gray-900">{{ $uc['headline'] }}</h2>
                    <p class="mt-4 text-gray-600 leading-relaxed">{{ $uc['description'] }}</p>
                    <div class="mt-6 grid grid-cols-2 gap-3">
                        @foreach(array_slice($uc['features'], 0, 4) as [$title, $desc])
                            <div class="flex items-start gap-2">
                                <i class="fas fa-check-circle text-emerald-500 mt-0.5 flex-shrink-0 text-sm"></i>
                                <span class="text-sm text-gray-700">{{ $title }}</span>
                            </div>
                        @endforeach
                    </div>
                    <a href="{{ route('website.usecases.show', $slug) }}" class="mt-6 inline-flex items-center gap-2 text-emerald-600 font-semibold hover:underline">
                        Learn More <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="{{ $loop->even ? 'lg:order-1' : '' }}">
                    <div class="bg-gray-50 rounded-2xl p-8 border">
                        <div class="grid grid-cols-2 gap-4">
                            @foreach($uc['stats'] as $stat)
                                <div class="bg-white rounded-xl p-4 text-center border">
                                    <p class="text-lg font-bold text-emerald-600">{{ $stat }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @if(!$loop->last)<hr class="border-gray-200">@endif
        @endforeach
    </div>
</section>

<section class="py-16 bg-emerald-600">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold text-white">Get Started with Your Industry Solution</h2>
        <p class="mt-4 text-emerald-100">Pre-built templates included. Launch your WhatsApp automation in minutes.</p>
        <a href="{{ route('register') }}" class="mt-8 inline-flex px-8 py-4 bg-white text-emerald-700 text-lg font-semibold rounded-xl hover:bg-emerald-50 shadow-lg">
            Start Free Trial
        </a>
    </div>
</section>

@endsection