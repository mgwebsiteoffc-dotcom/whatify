@extends('layouts.app')
@section('title', 'Blog Posts')
@section('page-title', 'Blog Management')

@section('content')
<div class="space-y-6">
    <div class="flex justify-end">
        <a href="{{ route('admin.blog.create') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-md text-sm hover:bg-emerald-700">
            <i class="fas fa-plus mr-1"></i> New Post
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Views</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Published</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($posts as $post)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3">
                            <div class="text-sm font-medium text-gray-900">{{ Str::limit($post->title, 50) }}</div>
                            <div class="text-xs text-gray-400">{{ $post->slug }}</div>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-500">{{ $post->category?->name ?? '-' }}</td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $post->status === 'published' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                {{ ucfirst($post->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-center text-sm">{{ number_format($post->views) }}</td>
                        <td class="px-6 py-3 text-sm text-gray-500">{{ $post->published_at?->format('M d, Y') ?? '-' }}</td>
                        <td class="px-6 py-3 text-right">
                            <a href="{{ $post->url }}" target="_blank" class="text-gray-400 hover:text-gray-600 p-1"><i class="fas fa-external-link-alt"></i></a>
                            <a href="{{ route('admin.blog.edit', $post) }}" class="text-gray-400 hover:text-blue-600 p-1"><i class="fas fa-edit"></i></a>
                            <form method="POST" action="{{ route('admin.blog.destroy', $post) }}" class="inline" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button class="text-gray-400 hover:text-red-600 p-1"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div>{{ $posts->links() }}</div>
</div>
@endsection