@extends('layouts.app')
@section('title', 'Tags')
@section('page-title', 'Tags')

@section('content')
<div class="max-w-3xl space-y-6">
    {{-- Create Tag --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Create Tag</h3>
        <form method="POST" action="{{ route('tags.store') }}" class="flex gap-3 items-end">
            @csrf
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" required placeholder="e.g. Premium, VIP, Diwali Sale..."
                       class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Color</label>
                <input type="color" name="color" value="#3B82F6" class="mt-1 h-[38px] w-16 rounded border border-gray-300 cursor-pointer">
            </div>
            <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 text-sm">
                <i class="fas fa-plus mr-1"></i> Create
            </button>
        </form>
    </div>

    {{-- Tags List --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tag</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contacts</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($tags as $tag)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-medium"
                                  style="background-color: {{ $tag->color }}15; color: {{ $tag->color }};">
                                <span class="w-3 h-3 rounded-full" style="background-color: {{ $tag->color }}"></span>
                                {{ $tag->name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ number_format($tag->contacts_count) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $tag->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('contacts.index', ['tag' => $tag->id]) }}" class="text-gray-400 hover:text-blue-600 text-sm" title="View contacts">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <form method="POST" action="{{ route('tags.destroy', $tag) }}" onsubmit="return confirm('Delete tag? Contacts will not be deleted.')">
                                    @csrf @method('DELETE')
                                    <button class="text-gray-400 hover:text-red-600 text-sm" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                            No tags created yet. Create your first tag above.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection