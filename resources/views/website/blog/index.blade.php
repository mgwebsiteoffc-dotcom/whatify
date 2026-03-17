@extends('website.layouts.app')

@section('title', isset($cat) ? $cat->meta_title ?? $cat->name . ' - Blog' : 'Blog - WhatsApp Marketing Tips & Guides')
@section('meta_description', isset($cat) ? $cat->meta_description ?? "Articles about {$cat->name}" : 'Learn WhatsApp marketing strategies, automation tips, chatbot guides and business growth hacks from the Whatify blog.')

@section('content')

<section class="bg-gradient-to-br from-emerald-50 to-white py-16">
    <div class="max-w-7xl mx-auto px-4">
        <h1 class="text-4xl font-extrabold text-gray-900">{{ isset($cat) ? $cat->name : 'Blog' }}</h1>
        <p class="mt-4 text-lg text-gray-600">{{ isset($cat) ? $cat->description : 'Tips, guides and strategies for WhatsApp Business success.' }}</p>
    </div>
</section>

<section class="py-16">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid lg:grid-cols-4 gap-8">
            {{-- Posts --}}
            <div class="lg:col-span-3">
                @if($posts->isEmpty())
                    <div class="text-center py-16 text-gray-500">
                        <i class="fas fa-newspaper text-5xl mb-4"></i>
                        <p class="text-lg">No posts yet. Check back soon!</p>
                    </div>
                @else
                    <div class="grid md:grid-cols-2 gap-8">
                        @foreach($posts as $post)
                            <article class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition-shadow border">
                                @if($post->featured_image)
                                    <a href="{{ $post->url }}">
                                        <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-48 object-cover" loading="lazy">
                                    </a>
                                @else
                                    <a href="{{ $post->url }}" class="block w-full h-48 bg-gradient-to-br from-emerald-100 to-emerald-50 flex items-center justify-center">
                                        <i class="fas fa-newspaper text-4xl text-emerald-200"></i>
                                    </a>
                                @endif
                                <div class="p-6">
                                    @if($post->category)
                                        <a href="{{ route('website.blog.category', $post->category->slug) }}" class="text-xs font-medium text-emerald-600 uppercase hover:underline">{{ $post->category->name }}</a>
                                    @endif
                                    <h2 class="text-lg font-bold text-gray-900 mt-1 mb-2 line-clamp-2">
                                        <a href="{{ $post->url }}" class="hover:text-emerald-600">{{ $post->title }}</a>
                                    </h2>
                                    <p class="text-sm text-gray-600 line-clamp-2">{{ $post->excerpt }}</p>
                                    <div class="mt-4 flex items-center justify-between text-xs text-gray-400">
                                        <span>{{ $post->published_at?->format('M d, Y') }}</span>
                                        <span>{{ $post->read_time }} min read</span>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    <div class="mt-8">{{ $posts->links() }}</div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="lg:col-span-1 space-y-8">
                <div class="bg-white rounded-xl p-6 border">
                    <h3 class="font-semibold text-gray-900 mb-4">Categories</h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="{{ route('website.blog') }}" class="text-sm {{ !isset($cat) ? 'text-emerald-600 font-medium' : 'text-gray-600 hover:text-emerald-600' }}">
                                All Posts
                            </a>
                        </li>
                        @foreach($categories as $category)
                            <li>
                                <a href="{{ route('website.blog.category', $category->slug) }}"
                                   class="text-sm flex justify-between {{ (isset($cat) && $cat->id === $category->id) ? 'text-emerald-600 font-medium' : 'text-gray-600 hover:text-emerald-600' }}">
                                    {{ $category->name }}
                                    <span class="text-gray-400">({{ $category->posts_count }})</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="bg-emerald-600 rounded-xl p-6 text-white">
                    <h3 class="font-semibold mb-2">Try Whatify Free</h3>
                    <p class="text-sm text-emerald-100 mb-4">Automate your WhatsApp marketing. 14-day free trial.</p>
                    <a href="{{ route('register') }}" class="block text-center px-4 py-2 bg-white text-emerald-700 rounded-lg text-sm font-semibold hover:bg-emerald-50">
                        Start Free Trial
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection