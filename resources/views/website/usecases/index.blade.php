@extends('website.layouts.app')

@section('title', 'Use Cases - WhatsApp Automation for Every Business')
@section('meta_description', 'Discover how businesses use Whatify for WhatsApp automation. E-commerce, education, healthcare, real estate, restaurants and travel — pre-built solutions for every industry.')

@section('content')

<section class="bg-gradient-to-br from-emerald-50 to-white py-20">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <h1 class="text-4xl lg:text-5xl font-extrabold text-gray-900">WhatsApp Automation <span class="text-emerald-600">Use Cases</span></h1>
        <p class="mt-6 text-lg text-gray-600 max-w-3xl mx-auto">See how businesses across industries use Whatify to automate communication, boost sales and improve customer experience.</p>
    </div>
</section>

<section class="py-20">
    <div class="max-w-7xl mx-auto px-4 grid md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($useCases as $slug => $uc)
            <a href="{{ route('website.usecases.show', $slug) }}" class="bg-white rounded-2xl shadow-sm border hover:shadow-xl transition-shadow group overflow-hidden">
                <div class="p-8">
                    <div class="text-5xl mb-4">{{ $uc['emoji'] }}</div>
                    <h2 class="text-xl font-bold text-gray-900 group-hover:text-emerald-600 transition-colors">{{ $uc['headline'] }}</h2>
                    <p class="mt-3 text-sm text-gray-600 leading-relaxed">{{ $uc['description'] }}</p>
                    <div class="mt-6 flex flex-wrap gap-2">
                        @foreach(array_slice($uc['features'], 0, 3) as [$featureTitle, $featureDesc])
                            <span class="text-xs bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-full">{{ $featureTitle }}</span>
                        @endforeach
                    </div>
                    <div class="mt-6 text-sm text-emerald-600 font-semibold group-hover:translate-x-1 transition-transform">
                        Learn More <i class="fas fa-arrow-right ml-1"></i>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</section>

<section class="py-16 bg-emerald-600">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold text-white">Don't See Your Industry?</h2>
        <p class="mt-4 text-emerald-100">Whatify works for any business that communicates with customers. Contact us for a custom solution.</p>
        <div class="mt-8 flex gap-4 justify-center">
            <a href="{{ route('register') }}" class="px-8 py-3 bg-white text-emerald-700 font-semibold rounded-xl hover:bg-emerald-50">Start Free Trial</a>
            <a href="{{ route('website.contact') }}" class="px-8 py-3 border-2 border-white text-white font-semibold rounded-xl hover:bg-emerald-700">Contact Us</a>
        </div>
    </div>
</section>

@endsection