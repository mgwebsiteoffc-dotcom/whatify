@extends('layouts.app')
@section('title', $post ? 'Edit Post' : 'New Post')
@section('page-title', $post ? 'Edit Post' : 'Create New Post')

@section('content')
<div class="max-w-4xl" x-data="{ faqItems: {{ json_encode($post?->faq ?? [['question' => '', 'answer' => '']]) }} }">
    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ $post ? route('admin.blog.update', $post) : route('admin.blog.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @if($post) @method('PUT') @endif

            <div>
                <label class="block text-sm font-medium text-gray-700">Title *</label>
                <input type="text" name="title" value="{{ old('title', $post?->title) }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Category</label>
                    <select name="category_id" class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                        <option value="">None</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $post?->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                        @foreach(['draft', 'published', 'archived'] as $s)
                            <option value="{{ $s }}" {{ old('status', $post?->status ?? 'draft') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Excerpt</label>
                <textarea name="excerpt" rows="2" class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">{{ old('excerpt', $post?->excerpt) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Body (HTML) *</label>
                <textarea name="body" rows="15" required class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border font-mono">{{ old('body', $post?->body) }}</textarea>
                <p class="text-xs text-gray-400 mt-1">Use HTML tags: h2, h3, p, ul, ol, li, a, strong, em, img</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Featured Image</label>
                    <input type="file" name="featured_image" accept="image/*" class="mt-1 text-sm">
                    @if($post?->featured_image)
                        <img src="{{ asset('storage/' . $post->featured_image) }}" class="h-20 rounded mt-2">
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Read Time (min)</label>
                    <input type="number" name="read_time" value="{{ old('read_time', $post?->read_time ?? 5) }}" min="1" class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                </div>
            </div>

            <div class="border-t pt-4">
                <p class="text-sm font-medium text-gray-700 mb-2">SEO</p>
                <div class="space-y-3">
                    <input type="text" name="meta_title" value="{{ old('meta_title', $post?->meta_title) }}" placeholder="Meta Title" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    <textarea name="meta_description" rows="2" placeholder="Meta Description" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">{{ old('meta_description', $post?->meta_description) }}</textarea>
                    <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $post?->meta_keywords) }}" placeholder="Keywords (comma separated)" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                </div>
            </div>

            <div class="border-t pt-4">
                <div class="flex justify-between items-center mb-3">
                    <p class="text-sm font-medium text-gray-700">FAQ (for AEO / Rich Snippets)</p>
                    <button type="button" @click="faqItems.push({question:'', answer:''})" class="text-xs text-emerald-600"><i class="fas fa-plus mr-1"></i>Add FAQ</button>
                </div>
                <template x-for="(faq, idx) in faqItems" :key="idx">
                    <div class="flex gap-2 mb-2 p-3 bg-gray-50 rounded">
                        <div class="flex-1 space-y-1">
                            <input type="text" :name="'faq['+idx+'][question]'" x-model="faq.question" placeholder="Question" class="w-full rounded border-gray-300 text-sm px-2 py-1.5 border">
                            <textarea :name="'faq['+idx+'][answer]'" x-model="faq.answer" rows="2" placeholder="Answer" class="w-full rounded border-gray-300 text-sm px-2 py-1.5 border"></textarea>
                        </div>
                        <button type="button" @click="faqItems.splice(idx, 1)" class="text-red-400 hover:text-red-600 px-2 self-start"><i class="fas fa-times"></i></button>
                    </div>
                </template>
            </div>

            <div class="flex gap-3 pt-4 border-t">
                <button type="submit" class="px-6 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700">
                    {{ $post ? 'Update Post' : 'Create Post' }}
                </button>
                <a href="{{ route('admin.blog.index') }}" class="px-6 py-2 border rounded-md text-gray-700 hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection