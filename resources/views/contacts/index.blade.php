@extends('layouts.app')
@section('title', 'Contacts')
@section('page-title', 'Contacts CRM')

@section('content')
<div class="space-y-6" x-data="contactManager()">

    {{-- Stats Row --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            ['Total', $stats['total'], 'fas fa-users', 'blue'],
            ['Active', $stats['active'], 'fas fa-user-check', 'green'],
            ['Opted Out', $stats['opted_out'], 'fas fa-user-slash', 'yellow'],
            ['Blocked', $stats['blocked'], 'fas fa-ban', 'red'],
        ] as [$label, $value, $icon, $color])
            <div class="bg-white rounded-lg shadow px-4 py-3 flex items-center gap-3">
                <div class="rounded-md bg-{{ $color }}-100 p-2">
                    <i class="{{ $icon }} text-{{ $color }}-600"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ $label }}</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($value) }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Toolbar --}}
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex flex-col lg:flex-row justify-between gap-4">
            {{-- Search & Filters --}}
            <form method="GET" class="flex flex-wrap gap-2 flex-1">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, phone, email..."
                           class="w-full pl-9 pr-3 py-2 rounded-md border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500 border">
                </div>
                <select name="tag" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm px-3 py-2 border">
                    <option value="">All Tags</option>
                    @foreach($tags as $tag)
                        <option value="{{ $tag->id }}" {{ request('tag') == $tag->id ? 'selected' : '' }}>{{ $tag->name }}</option>
                    @endforeach
                </select>
                <select name="status" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm px-3 py-2 border">
                    <option value="">All Status</option>
                    @foreach(['active', 'inactive', 'blocked', 'opted_out'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select>
                <select name="sort" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm px-3 py-2 border">
                    <option value="">Newest First</option>
                    <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Name A-Z</option>
                    <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Name Z-A</option>
                    <option value="recent_message" {{ request('sort') === 'recent_message' ? 'selected' : '' }}>Recent Message</option>
                    <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                </select>
                <button type="submit" class="px-3 py-2 bg-gray-100 rounded-md text-sm hover:bg-gray-200">
                    <i class="fas fa-filter"></i>
                </button>
            </form>

            {{-- Actions --}}
            <div class="flex gap-2 flex-shrink-0">
                <a href="{{ route('contacts.create') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-md text-sm font-medium hover:bg-emerald-700">
                    <i class="fas fa-plus mr-1"></i> Add Contact
                </a>
                <a href="{{ route('contacts.import.form') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">
                    <i class="fas fa-file-import mr-1"></i> Import
                </a>
                <a href="{{ route('contacts.export', request()->query()) }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">
                    <i class="fas fa-file-export mr-1"></i> Export
                </a>
            </div>
        </div>

        {{-- Bulk Actions Bar --}}
        <div x-show="selectedContacts.length > 0" x-cloak class="mt-4 pt-4 border-t flex items-center gap-3">
            <span class="text-sm text-gray-600" x-text="selectedContacts.length + ' selected'"></span>
            <form method="POST" action="{{ route('contacts.bulkAction') }}" class="flex gap-2" id="bulkForm">
                @csrf
                <template x-for="id in selectedContacts" :key="id">
                    <input type="hidden" name="contact_ids[]" :value="id">
                </template>
                <select name="action" class="rounded-md border-gray-300 text-sm px-3 py-1.5">
                    <option value="">Select Action</option>
                    <option value="add_tag">Add Tag</option>
                    <option value="remove_tag">Remove Tag</option>
                    <option value="activate">Activate</option>
                    <option value="block">Block</option>
                    <option value="opt_out">Opt Out</option>
                    <option value="delete">Delete</option>
                </select>
                <select name="tag_id" class="rounded-md border-gray-300 text-sm px-3 py-1.5">
                    <option value="">Select Tag</option>
                    @foreach($tags as $tag)
                        <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                    @endforeach
                </select>
                <button type="submit" onclick="return confirm('Apply bulk action?')" class="px-3 py-1.5 bg-gray-800 text-white rounded-md text-sm hover:bg-gray-700">
                    Apply
                </button>
            </form>
        </div>
    </div>

    {{-- Contacts Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($contacts->isEmpty())
            <div class="p-12 text-center">
                <i class="fas fa-address-book text-5xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No Contacts Found</h3>
                <p class="text-gray-500 mb-6">Add your first contact or import from a file.</p>
                <div class="flex gap-3 justify-center">
                    <a href="{{ route('contacts.create') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-md text-sm">
                        <i class="fas fa-plus mr-1"></i> Add Contact
                    </a>
                    <a href="{{ route('contacts.import.form') }}" class="px-4 py-2 border rounded-md text-sm">
                        <i class="fas fa-file-import mr-1"></i> Import CSV
                    </a>
                </div>
            </div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="pl-6 py-3 w-10">
                            <input type="checkbox" @change="toggleAll($event)" class="rounded border-gray-300 text-emerald-600">
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tags</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Message</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($contacts as $contact)
                        <tr class="hover:bg-gray-50">
                            <td class="pl-6 py-3">
                                <input type="checkbox" value="{{ $contact->id }}" x-model="selectedContacts"
                                       class="rounded border-gray-300 text-emerald-600">
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-full bg-emerald-100 flex items-center justify-center text-sm font-bold text-emerald-700 flex-shrink-0">
                                        {{ strtoupper(substr($contact->name ?? $contact->phone, 0, 1)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('contacts.show', $contact) }}" class="text-sm font-medium text-gray-900 hover:text-emerald-600">
                                            {{ $contact->name ?? 'Unknown' }}
                                        </a>
                                        @if($contact->email)
                                            <p class="text-xs text-gray-500">{{ $contact->email }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 font-mono">
                                +{{ $contact->country_code }}{{ $contact->phone }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($contact->tags->take(3) as $tag)
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                              style="background-color: {{ $tag->color }}20; color: {{ $tag->color }};">
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                    @if($contact->tags->count() > 3)
                                        <span class="text-xs text-gray-400">+{{ $contact->tags->count() - 3 }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                    {{ $contact->status === 'active' ? 'bg-green-100 text-green-700' :
                                       ($contact->status === 'blocked' ? 'bg-red-100 text-red-700' :
                                       ($contact->status === 'opted_out' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700')) }}">
                                    {{ ucfirst(str_replace('_', ' ', $contact->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 capitalize">{{ $contact->source ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500">
                                {{ $contact->last_message_at?->diffForHumans() ?? 'Never' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('contacts.show', $contact) }}" class="text-gray-400 hover:text-gray-600" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('contacts.edit', $contact) }}" class="text-gray-400 hover:text-blue-600" title="Edit"><i class="fas fa-edit"></i></a>
                                    <form method="POST" action="{{ route('contacts.destroy', $contact) }}" onsubmit="return confirm('Delete?')">
                                        @csrf @method('DELETE')
                                        <button class="text-gray-400 hover:text-red-600" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div>{{ $contacts->links() }}</div>
</div>

@push('scripts')
<script>
function contactManager() {
    return {
        selectedContacts: [],
        toggleAll(event) {
            if (event.target.checked) {
                this.selectedContacts = @json($contacts->pluck('id'));
            } else {
                this.selectedContacts = [];
            }
        }
    }
}
</script>
@endpush
@endsection