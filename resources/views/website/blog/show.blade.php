@extends('website.layouts.app')

@section('title', $post->meta_title ?? $post->title)
@section('meta_description', $post->meta_description ?? $post->excerpt)
@section('meta_keywords', $post->meta_keywords ?? '')
@section('og_title', $post->meta_title ?? $post->title)
@section('og_image', $post->featured_image ? asset('storage/' . $post->featured_image) : asset('images/og-default.png'))

@section('schema')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "BlogPosting",
    "headline": "{{ $post->title }}",
    "description": "{{ $post->excerpt }}",
    "datePublished": "{{ $post->published_at?->toIso8601String() }}",
    "dateModified": "{{ $post->updated_at->toIso8601String() }}",
    "author": {"@type": "Person", "name": "{{ $post->author?->name ?? 'Whatify' }}"},
    "publisher": {"@type": "Organization", "name": "Whatify"},
    "url": "{{ $post->url }}",
    "wordCount": "{{ str_word_count(strip_tags($post->body)) }}",
    "timeRequired": "PT{{ $post->read_time }}M"
    @if($post->featured_image),"image": "{{ asset('storage/' . $post->featured_image) }}"@endif
}
</script>
@if($post->faq)
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
        @foreach($post->faq as $faq)
        {"@type":"Question","name":"{{ $faq['question'] }}","acceptedAnswer":{"@type":"Answer","text":"{{ $faq['answer'] }}"}}{{ $loop->last ? '' : ',' }}
        @endforeach
    ]
}
</script>
@endif
@endsection

@section('content')

<article class="py-16">
    <div class="max-w-4xl mx-auto px-4">
        {{-- Breadcrumb --}}
        <nav class="text-sm text-gray-500 mb-8">
            <a href="{{ route('website.home') }}" class="hover:text-emerald-600">Home</a>
            <span class="mx-2">/</span>
            <a href="{{ route('website.blog') }}" class="hover:text-emerald-600">Blog</a>
            @if($post->category)
                <span class="mx-2">/</span>
                <a href="{{ route('website.blog.category', $post->category->slug) }}" class="hover:text-emerald-600">{{ $post->category->name }}</a>
            @endif
        </nav>

        {{-- Header --}}
        @if($post->category)
            <span class="text-sm font-medium text-emerald-600 uppercase">{{ $post->category->name }}</span>
        @endif
        <h1 class="mt-2 text-3xl lg:text-4xl font-extrabold text-gray-900 leading-tight">{{ $post->title }}</h1>
        <div class="mt-4 flex items-center gap-4 text-sm text-gray-500">
            <span><i class="fas fa-user mr-1"></i> {{ $post->author?->name ?? 'Whatify Team' }}</span>
            <span><i class="fas fa-calendar mr-1"></i> {{ $post->published_at?->format('M d, Y') }}</span>
            <span><i class="fas fa-clock mr-1"></i> {{ $post->read_time }} min read</span>
            <span><i class="fas fa-eye mr-1"></i> {{ number_format($post->views) }} views</span>
        </div>

        {{-- Featured Image --}}
        @if($post->featured_image)
            <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full rounded-2xl mt-8 shadow-lg" loading="lazy">
        @endif

        {{-- Body --}}
        <div class="mt-10 prose prose-lg prose-emerald max-w-none
            prose-headings:text-gray-900 prose-p:text-gray-700 prose-a:text-emerald-600
            prose-img:rounded-xl prose-img:shadow-md">
            {!! $post->body !!}
        </div>

        {{-- FAQ Section --}}
        @if($post->faq && count($post->faq) > 0)
            <div class="mt-12 border-t pt-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Frequently Asked Questions</h2>
                <div class="space-y-4" x-data="{ open: null }">
                    @foreach($post->faq as $i => $faq)
                        <div class="border rounded-xl">
                            <button @click="open = open === {{ $i }} ? null : {{ $i }}" class="w-full flex justify-between items-center px-6 py-4 text-left font-medium text-gray-900 hover:bg-gray-50">
                                {{ $faq['question'] }}
                                <i class="fas fa-chevron-down text-gray-400 transition-transform" :class="open === {{ $i }} ? 'rotate-180' : ''"></i>
                            </button>
                            <div x-show="open === {{ $i }}" x-cloak x-collapse>
                                <p class="px-6 pb-4 text-gray-600 text-sm">{{ $faq['answer'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- CTA --}}
        <div class="mt-12 bg-emerald-50 border border-emerald-200 rounded-2xl p-8 text-center">
            <h3 class="text-xl font-bold text-gray-900">Ready to automate your WhatsApp marketing?</h3>
            <p class="mt-2 text-gray-600">Start your 14-day free trial today. No credit card required.</p>
            <a href="{{ route('register') }}" class="mt-4 inline-flex px-6 py-3 bg-emerald-600 text-white rounded-xl font-semibold hover:bg-emerald-700">
                Start Free Trial <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>

        {{-- Share --}}
        <div class="mt-8 flex items-center gap-4">
            <span class="text-sm font-medium text-gray-500">Share:</span>
            <a href="https://twitter.com/intent/tweet?url={{ urlencode($post->url) }}&text={{ urlencode($post->title) }}" target="_blank" class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-blue-100 hover:text-blue-500"><i class="fab fa-twitter"></i></a>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($post->url) }}" target="_blank" class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-blue-100 hover:text-blue-600"><i class="fab fa-linkedin-in"></i></a>
            <a href="https://wa.me/?text={{ urlencode($post->title . ' ' . $post->url) }}" target="_blank" class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-green-100 hover:text-green-600"><i class="fab fa-whatsapp"></i></a>
        </div>
    </div>
</article>

{{-- Related Posts --}}
@if($related->isNotEmpty())
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-2xl font-bold text-gray-900 mb-8">Related Articles</h2>
        <div class="grid md:grid-cols-3 gap-8">
            @foreach($related as $rPost)
                <article class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition-shadow border">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 line-clamp-2">
                            <a href="{{ $rPost->url }}" class="hover:text-emerald-600">{{ $rPost->title }}</a>
                        </h3>
                        <p class="text-sm text-gray-600 mt-2 line-clamp-2">{{ $rPost->excerpt }}</p>
                        <p class="text-xs text-gray-400 mt-3">{{ $rPost->published_at?->format('M d, Y') }} · {{ $rPost->read_time }} min</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

@endsection