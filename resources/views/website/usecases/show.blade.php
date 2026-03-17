@extends('website.layouts.app')

@section('title', $useCase['title'])
@section('meta_description', $useCase['description'])

@section('schema')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebPage",
    "name": "{{ $useCase['title'] }}",
    "description": "{{ $useCase['description'] }}",
    "url": "{{ url()->current() }}",
    "breadcrumb": {
        "@type": "BreadcrumbList",
        "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "Home", "item": "{{ route('website.home') }}"},
            {"@type": "ListItem", "position": 2, "name": "Use Cases", "item": "{{ route('website.usecases') }}"},
            {"@type": "ListItem", "position": 3, "name": "{{ $useCase['headline'] }}", "item": "{{ url()->current() }}"}
        ]
    }
}
</script>
@endsection

@section('content')

<section class="bg-gradient-to-br from-emerald-50 to-white py-20">
    <div class="max-w-7xl mx-auto px-4">
        <div class="max-w-3xl">
            <a href="{{ route('website.usecases') }}" class="text-sm text-emerald-600 hover:underline"><i class="fas fa-arrow-left mr-1"></i> All Use Cases</a>
            <div class="text-6xl mt-4">{{ $useCase['emoji'] }}</div>
            <h1 class="mt-4 text-4xl lg:text-5xl font-extrabold text-gray-900">{{ $useCase['headline'] }}</h1>
            <p class="mt-6 text-lg text-gray-600 leading-relaxed">{{ $useCase['description'] }}</p>
            <a href="{{ route('register') }}" class="mt-8 inline-flex items-center gap-2 px-8 py-4 bg-emerald-600 text-white text-lg font-semibold rounded-xl hover:bg-emerald-700 shadow-lg">
                Start Free Trial <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

{{-- Stats --}}
<section class="py-12 bg-emerald-600">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($useCase['stats'] as $stat)
                <div class="text-center">
                    <p class="text-2xl lg:text-3xl font-bold text-white">{{ $stat }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Features --}}
<section class="py-20">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-gray-900 text-center mb-16">What You Can Automate</h2>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($useCase['features'] as [$title, $desc])
                <div class="bg-white rounded-xl p-6 border hover:shadow-lg transition-shadow">
                    <div class="h-10 w-10 rounded-lg bg-emerald-100 flex items-center justify-center mb-4">
                        <i class="fas fa-check text-emerald-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $title }}</h3>
                    <p class="text-sm text-gray-600">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Integrations --}}
<section class="py-16 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Works With Your Existing Tools</h2>
        <div class="flex flex-wrap justify-center gap-4">
            @foreach($useCase['integrations'] as $integration)
                <span class="px-6 py-3 bg-white border rounded-xl text-sm font-medium text-gray-700 shadow-sm">{{ $integration }}</span>
            @endforeach
        </div>
    </div>
</section>

{{-- Other Use Cases --}}
<section class="py-20">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-2xl font-bold text-gray-900 mb-8">Explore Other Use Cases</h2>
        <div class="grid md:grid-cols-3 gap-6">
            @foreach($otherUseCases as $slug => $other)
                <a href="{{ route('website.usecases.show', $slug) }}" class="bg-white rounded-xl p-6 border hover:shadow-lg transition-shadow group">
                    <div class="text-3xl mb-3">{{ $other['emoji'] }}</div>
                    <h3 class="font-semibold text-gray-900 group-hover:text-emerald-600">{{ $other['headline'] }}</h3>
                    <p class="text-sm text-gray-500 mt-2 line-clamp-2">{{ $other['description'] }}</p>
                </a>
            @endforeach
        </div>
    </div>
</section>

<section class="py-16 bg-emerald-600">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold text-white">Ready to Get Started?</h2>
        <p class="mt-4 text-emerald-100">14-day free trial. Setup in 5 minutes.</p>
        <a href="{{ route('register') }}" class="mt-8 inline-flex px-8 py-4 bg-white text-emerald-700 text-lg font-semibold rounded-xl hover:bg-emerald-50 shadow-lg">
            Start Free Trial
        </a>
    </div>
</section>

@endsection