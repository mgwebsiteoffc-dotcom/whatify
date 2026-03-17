@extends('layouts.app')
@section('title', 'Message Templates')
@section('page-title', 'Message Templates')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between gap-4">
        <div class="flex gap-2 flex-wrap">
            {{-- Filters --}}
            <form method="GET" class="flex gap-2 flex-wrap">
                <select name="status" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm px-3 py-1.5">
                    <option value="">All Status</option>
                    @foreach(['approved', 'pending', 'rejected', 'paused'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <select name="category" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm px-3 py-1.5">
                    <option value="">All Categories</option>
                    @foreach(['marketing', 'utility', 'authentication'] as $c)
                        <option value="{{ $c }}" {{ request('category') === $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
                    @endforeach
                </select>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search templates..."
                       class="rounded-md border-gray-300 text-sm px-3 py-1.5">
            </form>
        </div>
        <a href="{{ route('whatsapp.templates.create') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 text-sm font-medium whitespace-nowrap">
            <i class="fas fa-plus mr-2"></i>Create Template
        </a>
    </div>

    {{-- Templates List --}}
    @if($templates->isEmpty())
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <i class="fas fa-file-alt text-5xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No Templates Found</h3>
            <p class="text-gray-500 mb-6">Create your first message template or sync from WhatsApp.</p>
            <div class="flex gap-3 justify-center">
                <a href="{{ route('whatsapp.templates.create') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 text-sm">
                    <i class="fas fa-plus mr-1"></i> Create Template
                </a>
                @if($accounts->isNotEmpty())
                    <form method="POST" action="{{ route('whatsapp.accounts.syncTemplates', $accounts->first()) }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">
                            <i class="fas fa-sync mr-1"></i> Sync from WhatsApp
                        </button>
                    </form>
                @endif
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Template</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Language</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Account</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($templates as $template)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $template->name }}</p>
                                    <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ Str::limit($template->body, 80) }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                    {{ $template->category === 'marketing' ? 'bg-purple-100 text-purple-700' :
                                       ($template->category === 'utility' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700') }}">
                                    {{ ucfirst($template->category) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                    {{ $template->status === 'approved' ? 'bg-green-100 text-green-700' :
                                       ($template->status === 'pending' ? 'bg-yellow-100 text-yellow-700' :
                                       ($template->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700')) }}">
                                    {{ ucfirst($template->status) }}
                                </span>
                                @if($template->rejection_reason)
                                    <p class="text-xs text-red-500 mt-1">{{ Str::limit($template->rejection_reason, 40) }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $template->language }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $template->whatsappAccount?->display_name ?? $template->whatsappAccount?->phone_number ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('whatsapp.templates.show', $template) }}" class="text-gray-400 hover:text-gray-600" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form method="POST" action="{{ route('whatsapp.templates.destroy', $template) }}"
                                          onsubmit="return confirm('Delete this template?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-600" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $templates->withQueryString()->links() }}</div>
    @endif
</div>
@endsection