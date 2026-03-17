@extends('layouts.app')
@section('title', $contact->name ?? $contact->phone)
@section('page-title')
    <a href="{{ route('contacts.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left mr-2"></i></a>
    {{ $contact->name ?? $contact->phone }}
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Left: Contact Profile --}}
    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-center">
                <div class="h-20 w-20 mx-auto rounded-full bg-emerald-100 flex items-center justify-center text-3xl font-bold text-emerald-700">
                    {{ strtoupper(substr($contact->name ?? $contact->phone, 0, 1)) }}
                </div>
                <h2 class="mt-3 text-xl font-semibold text-gray-900">{{ $contact->name ?? 'Unknown' }}</h2>
                <p class="text-gray-500 font-mono">+{{ $contact->country_code }}{{ $contact->phone }}</p>
                @if($contact->email)
                    <p class="text-gray-500 text-sm">{{ $contact->email }}</p>
                @endif
            </div>

            <div class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between py-1 border-b">
                    <span class="text-gray-500">Status</span>
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                        {{ $contact->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ ucfirst(str_replace('_', ' ', $contact->status)) }}
                    </span>
                </div>
                <div class="flex justify-between py-1 border-b">
                    <span class="text-gray-500">Source</span>
                    <span class="capitalize">{{ $contact->source ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b">
                    <span class="text-gray-500">Created</span>
                    <span>{{ $contact->created_at->format('M d, Y') }}</span>
                </div>
                <div class="flex justify-between py-1 border-b">
                    <span class="text-gray-500">Last Message</span>
                    <span>{{ $contact->last_message_at?->diffForHumans() ?? 'Never' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b">
                    <span class="text-gray-500">Opted In</span>
                    <span>{{ $contact->opted_in_at?->format('M d, Y') ?? '-' }}</span>
                </div>
            </div>

            {{-- Tags --}}
            <div class="mt-4">
                <p class="text-xs font-medium text-gray-500 uppercase mb-2">Tags</p>
                <div class="flex flex-wrap gap-1">
                    @forelse($contact->tags as $tag)
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                              style="background-color: {{ $tag->color }}20; color: {{ $tag->color }};">
                            {{ $tag->name }}
                        </span>
                    @empty
                        <span class="text-xs text-gray-400">No tags</span>
                    @endforelse
                </div>
            </div>

            {{-- Custom Attributes --}}
            @if($contact->custom_attributes)
                <div class="mt-4">
                    <p class="text-xs font-medium text-gray-500 uppercase mb-2">Custom Fields</p>
                    <div class="space-y-1 text-sm">
                        @foreach($contact->custom_attributes as $key => $value)
                            <div class="flex justify-between py-1">
                                <span class="text-gray-500 capitalize">{{ str_replace('_', ' ', $key) }}</span>
                                <span class="font-medium">{{ $value }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mt-4 flex gap-2">
                <a href="{{ route('contacts.edit', $contact) }}" class="flex-1 text-center px-3 py-2 border rounded-md text-sm hover:bg-gray-50">
                    <i class="fas fa-edit mr-1"></i> Edit
                </a>
                <form method="POST" action="{{ route('contacts.destroy', $contact) }}" onsubmit="return confirm('Delete this contact?')" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full px-3 py-2 border border-red-300 text-red-600 rounded-md text-sm hover:bg-red-50">
                        <i class="fas fa-trash mr-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Right: Activity --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Recent Messages --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Recent Messages</h3>
            @if($messages->isEmpty())
                <p class="text-center text-gray-500 py-4">No messages yet</p>
            @else
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @foreach($messages as $msg)
                        <div class="flex gap-3 {{ $msg->direction === 'inbound' ? '' : 'flex-row-reverse' }}">
                            <div class="rounded-lg p-3 max-w-[70%] text-sm
                                {{ $msg->direction === 'inbound' ? 'bg-gray-100 text-gray-800' : 'bg-emerald-100 text-emerald-800' }}">
                                @if($msg->type !== 'text')
                                    <p class="text-xs font-medium opacity-60 mb-1">[{{ ucfirst($msg->type) }}]</p>
                                @endif
                                <p>{{ $msg->content ?? 'Media message' }}</p>
                                <div class="flex items-center justify-between mt-1 gap-3">
                                    <span class="text-[10px] opacity-50">{{ $msg->created_at->format('M d, h:i A') }}</span>
                                    @if($msg->direction === 'outbound')
                                        <span class="text-[10px] opacity-50">
                                            {{ $msg->status === 'read' ? '✓✓' : ($msg->status === 'delivered' ? '✓✓' : ($msg->status === 'sent' ? '✓' : ($msg->status === 'failed' ? '✗' : '⏳'))) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Conversations --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Conversations</h3>
            @forelse($conversations as $conv)
                <div class="flex items-center justify-between p-3 rounded hover:bg-gray-50 border-b last:border-0">
                    <div>
                        <p class="text-sm text-gray-900">{{ Str::limit($conv->last_message, 60) }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $conv->last_message_at?->diffForHumans() }}
                            @if($conv->assignedAgent)
                                · Assigned to {{ $conv->assignedAgent->name }}
                            @endif
                        </p>
                    </div>
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                        {{ $conv->status === 'open' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                        {{ ucfirst($conv->status) }}
                    </span>
                </div>
            @empty
                <p class="text-center text-gray-500 py-4">No conversations</p>
            @endforelse
        </div>

        {{-- Campaign History --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Campaign History</h3>
            @forelse($campaigns as $cc)
                <div class="flex items-center justify-between p-3 rounded hover:bg-gray-50 border-b last:border-0">
                    <div>
                        <p class="text-sm font-medium">{{ $cc->campaign?->name ?? 'Deleted Campaign' }}</p>
                        <p class="text-xs text-gray-500">{{ $cc->created_at->format('M d, Y') }}</p>
                    </div>
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                        {{ $cc->status === 'delivered' ? 'bg-green-100 text-green-700' : ($cc->status === 'read' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700') }}">
                        {{ ucfirst($cc->status) }}
                    </span>
                </div>
            @empty
                <p class="text-center text-gray-500 py-4">No campaigns</p>
            @endforelse
        </div>
    </div>
</div>
@endsection