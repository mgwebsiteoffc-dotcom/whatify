@extends('layouts.app')
@section('title', 'Inbox')
@section('page-title', 'Shared Inbox')

@section('content')
<div class="flex h-[calc(100vh-10rem)] bg-white rounded-lg shadow overflow-hidden" x-data="inboxManager()">

    {{-- Left Panel: Conversation List --}}
    <div class="w-80 border-r flex flex-col flex-shrink-0">
        {{-- Filters --}}
        <div class="p-3 border-b space-y-2">
            <form method="GET" class="flex gap-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                       class="flex-1 rounded-md border-gray-300 text-xs px-2 py-1.5 border">
                <button class="px-2 py-1.5 bg-gray-100 rounded-md text-xs"><i class="fas fa-search"></i></button>
            </form>
            <div class="flex gap-1 text-xs">
                @foreach(['all' => 'All', 'open' => 'Open', 'pending' => 'Pending', 'resolved' => 'Resolved'] as $key => $label)
                    <a href="{{ route('inbox.index', array_merge(request()->query(), ['status' => $key])) }}"
                       class="px-2 py-1 rounded-full {{ (request('status', 'all') === $key) ? 'bg-emerald-100 text-emerald-700 font-medium' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        {{ $label }}
                        <span class="ml-0.5 text-[10px]">({{ $statusCounts[$key] ?? 0 }})</span>
                    </a>
                @endforeach
            </div>
            <div class="flex gap-1 text-xs">
                <a href="{{ route('inbox.index', array_merge(request()->query(), ['assigned' => 'me'])) }}"
                   class="px-2 py-1 rounded {{ request('assigned') === 'me' ? 'bg-blue-100 text-blue-700' : 'text-gray-500 hover:bg-gray-100' }}">
                    My Chats
                </a>
                <a href="{{ route('inbox.index', array_merge(request()->query(), ['assigned' => 'unassigned'])) }}"
                   class="px-2 py-1 rounded {{ request('assigned') === 'unassigned' ? 'bg-yellow-100 text-yellow-700' : 'text-gray-500 hover:bg-gray-100' }}">
                    Unassigned
                </a>
                <a href="{{ route('inbox.index', ['status' => request('status', 'all')]) }}"
                   class="px-2 py-1 rounded text-gray-500 hover:bg-gray-100">
                    All
                </a>
            </div>
        </div>

        {{-- Conversation List --}}
        <div class="flex-1 overflow-y-auto">
            @forelse($conversations as $conv)
                <a href="{{ route('inbox.index', array_merge(request()->query(), ['conversation_id' => $conv->id])) }}"
                   class="flex items-start gap-3 p-3 border-b hover:bg-gray-50 cursor-pointer {{ $selectedConversation?->id === $conv->id ? 'bg-emerald-50 border-l-4 border-l-emerald-500' : '' }}">
                    <div class="h-10 w-10 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0
                        {{ $conv->status === 'open' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                        {{ strtoupper(substr($conv->contact?->name ?? $conv->contact?->phone ?? '?', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ $conv->contact?->name ?? $conv->contact?->phone ?? 'Unknown' }}
                            </p>
                            <span class="text-[10px] text-gray-400 flex-shrink-0">
                                {{ $conv->last_message_at?->shortRelativeDiffForHumans() }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 truncate mt-0.5">{{ $conv->last_message ?? 'No messages' }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            @if($conv->assignedAgent)
                                <span class="text-[10px] text-blue-500"><i class="fas fa-user mr-0.5"></i>{{ $conv->assignedAgent->name }}</span>
                            @endif
                            @if(!$conv->is_bot_active)
                                <span class="text-[10px] text-red-400"><i class="fas fa-robot mr-0.5"></i>Bot off</span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="p-8 text-center text-gray-500 text-sm">
                    <i class="fas fa-inbox text-3xl mb-2"></i>
                    <p>No conversations</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Right Panel: Chat --}}
    @if($selectedConversation)
        <div class="flex-1 flex flex-col">
            {{-- Chat Header --}}
            <div class="p-4 border-b flex items-center justify-between bg-gray-50">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-emerald-100 flex items-center justify-center text-sm font-bold text-emerald-700">
                        {{ strtoupper(substr($selectedConversation->contact?->name ?? '?', 0, 1)) }}
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">
                            {{ $selectedConversation->contact?->name ?? 'Unknown' }}
                        </h3>
                        <p class="text-xs text-gray-500">
                            +{{ $selectedConversation->contact?->country_code }}{{ $selectedConversation->contact?->phone }}
                            @if($selectedConversation->contact?->email)
                                · {{ $selectedConversation->contact->email }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    {{-- Assign --}}
                    <form method="POST" action="{{ route('inbox.assign', $selectedConversation) }}" class="flex gap-1">
                        @csrf
                        <select name="agent_id" onchange="this.form.submit()" class="rounded-md border-gray-300 text-xs px-2 py-1 border">
                            <option value="">Unassigned</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" {{ $selectedConversation->assigned_agent_id == $agent->id ? 'selected' : '' }}>
                                    {{ $agent->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>

                    {{-- Status --}}
                    <form method="POST" action="{{ route('inbox.updateStatus', $selectedConversation) }}" class="flex gap-1">
                        @csrf
                        <select name="status" onchange="this.form.submit()" class="rounded-md border-gray-300 text-xs px-2 py-1 border">
                            @foreach(['open', 'pending', 'resolved', 'closed'] as $s)
                                <option value="{{ $s }}" {{ $selectedConversation->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </form>

                    {{-- Toggle Bot --}}
                    <form method="POST" action="{{ route('inbox.toggleBot', $selectedConversation) }}">
                        @csrf
                        <button type="submit" class="px-2 py-1 rounded text-xs border {{ $selectedConversation->is_bot_active ? 'bg-green-100 text-green-700 border-green-300' : 'bg-red-100 text-red-700 border-red-300' }}"
                                title="{{ $selectedConversation->is_bot_active ? 'Bot Active - Click to disable' : 'Bot Disabled - Click to enable' }}">
                            <i class="fas fa-robot mr-1"></i>{{ $selectedConversation->is_bot_active ? 'Bot ON' : 'Bot OFF' }}
                        </button>
                    </form>

                    {{-- Contact Profile Link --}}
                    <a href="{{ route('contacts.show', $selectedConversation->contact) }}" class="px-2 py-1 rounded text-xs border hover:bg-gray-100" title="View Contact">
                        <i class="fas fa-user"></i>
                    </a>
                </div>
            </div>

            {{-- Messages Area --}}
            <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-[#ECE5DD]" id="messagesArea">
                @foreach($messages as $msg)
                    <div class="flex {{ $msg->direction === 'outbound' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[70%] rounded-lg p-3 shadow-sm
                            {{ $msg->direction === 'outbound' ? 'bg-[#DCF8C6]' : 'bg-white' }}">

                            @if($msg->direction === 'outbound' && $msg->sent_by)
                                <p class="text-[10px] font-medium text-emerald-700 mb-1">
                                    {{ $msg->is_bot_response ? '🤖 Bot' : $msg->sent_by }}
                                </p>
                            @endif

                            @if($msg->type !== 'text' && $msg->type !== 'template')
                                <div class="mb-1">
                                    @if($msg->type === 'image' && $msg->media)
                                        <img src="{{ $msg->media['url'] ?? $msg->media['local_path'] ?? '' }}" class="rounded max-w-full max-h-48 mb-1" alt="Image">
                                    @elseif(in_array($msg->type, ['video', 'audio', 'document']))
                                        <div class="bg-gray-100 rounded p-2 text-xs text-gray-600">
                                            <i class="fas fa-{{ $msg->type === 'video' ? 'play-circle' : ($msg->type === 'audio' ? 'headphones' : 'file') }} mr-1"></i>
                                            {{ ucfirst($msg->type) }}
                                            @if($msg->media['filename'] ?? null)
                                                : {{ $msg->media['filename'] }}
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-500">[{{ ucfirst($msg->type) }}]</span>
                                    @endif
                                </div>
                            @endif

                            @if($msg->content)
                                <p class="text-sm whitespace-pre-line break-words">{{ $msg->content }}</p>
                            @endif

                            <div class="flex items-center justify-end gap-1 mt-1">
                                <span class="text-[10px] text-gray-400">{{ $msg->created_at->format('h:i A') }}</span>
                                @if($msg->direction === 'outbound')
                                    @php
                                        $tick = match($msg->status) {
                                            'read' => '<span class="text-blue-500">✓✓</span>',
                                            'delivered' => '✓✓',
                                            'sent' => '✓',
                                            'failed' => '<span class="text-red-500">✗</span>',
                                            default => '<span class="text-gray-300">⏳</span>',
                                        };
                                    @endphp
                                    <span class="text-[10px]">{!! $tick !!}</span>
                                @endif
                            </div>

                            @if($msg->status === 'failed' && $msg->error_message)
                                <p class="text-[10px] text-red-500 mt-1">Error: {{ $msg->error_message }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Reply Area --}}
            <div class="border-t bg-white p-3">
                {{-- Quick Actions --}}
                <div class="flex gap-2 mb-2">
                    <button @click="showTemplates = !showTemplates" class="text-xs text-gray-500 hover:text-gray-700 px-2 py-1 rounded hover:bg-gray-100">
                        <i class="fas fa-file-alt mr-1"></i>Template
                    </button>
                    <button @click="showMedia = !showMedia" class="text-xs text-gray-500 hover:text-gray-700 px-2 py-1 rounded hover:bg-gray-100">
                        <i class="fas fa-paperclip mr-1"></i>Media
                    </button>
                    <button @click="showNotes = !showNotes" class="text-xs text-gray-500 hover:text-gray-700 px-2 py-1 rounded hover:bg-gray-100">
                        <i class="fas fa-sticky-note mr-1"></i>Note
                    </button>
                </div>

                {{-- Template Selector --}}
                <div x-show="showTemplates" x-cloak class="mb-2 p-2 bg-gray-50 rounded">
                    <form method="POST" action="{{ route('inbox.sendTemplate', $selectedConversation) }}" class="flex gap-2">
                        @csrf
                        <select name="template_id" class="flex-1 rounded-md border-gray-300 text-xs px-2 py-1.5 border">
                            @foreach($templates as $tpl)
                                <option value="{{ $tpl->id }}">{{ $tpl->name }} ({{ $tpl->category }})</option>
                            @endforeach
                        </select>
                        <button type="submit" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-xs">Send Template</button>
                    </form>
                </div>

                {{-- Media Upload --}}
                <div x-show="showMedia" x-cloak class="mb-2 p-2 bg-gray-50 rounded">
                    <form method="POST" action="{{ route('inbox.sendMedia', $selectedConversation) }}" enctype="multipart/form-data" class="flex gap-2 items-end">
                        @csrf
                        <input type="file" name="media_file" required class="flex-1 text-xs">
                        <input type="text" name="caption" placeholder="Caption..." class="rounded-md border-gray-300 text-xs px-2 py-1.5 border w-40">
                        <button type="submit" class="px-3 py-1.5 bg-emerald-600 text-white rounded text-xs">Send</button>
                    </form>
                </div>

                {{-- Internal Note --}}
                <div x-show="showNotes" x-cloak class="mb-2 p-2 bg-yellow-50 rounded">
                    <form method="POST" action="{{ route('inbox.addNote', $selectedConversation) }}" class="flex gap-2">
                        @csrf
                        <input type="text" name="note" placeholder="Add internal note..." required class="flex-1 rounded-md border-yellow-300 text-xs px-2 py-1.5 border bg-white">
                        <button type="submit" class="px-3 py-1.5 bg-yellow-600 text-white rounded text-xs">Add Note</button>
                    </form>
                    @if($notes->isNotEmpty())
                        <div class="mt-2 space-y-1 max-h-24 overflow-y-auto">
                            @foreach($notes as $note)
                                <div class="text-xs bg-yellow-100 rounded p-1.5">
                                    <span class="font-medium">{{ $note->user->name }}:</span>
                                    {{ $note->note }}
                                    <span class="text-yellow-600 text-[10px]">{{ $note->created_at->diffForHumans() }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Text Reply --}}
                <form method="POST" action="{{ route('inbox.reply', $selectedConversation) }}" class="flex gap-2">
                    @csrf
                    <input type="text" name="message" placeholder="Type a message..." required autocomplete="off"
                           class="flex-1 rounded-full border-gray-300 text-sm px-4 py-2 border focus:border-emerald-500 focus:ring-emerald-500">
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-full hover:bg-emerald-700">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    @else
        {{-- No Conversation Selected --}}
        <div class="flex-1 flex items-center justify-center bg-gray-50">
            <div class="text-center text-gray-400">
                <i class="fas fa-comments text-6xl mb-4"></i>
                <h3 class="text-xl font-semibold mb-2">Select a conversation</h3>
                <p class="text-sm">Choose a conversation from the left panel to start chatting</p>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
function inboxManager() {
    return {
        showTemplates: false,
        showMedia: false,
        showNotes: false,
        init() {
            const area = document.getElementById('messagesArea');
            if (area) area.scrollTop = area.scrollHeight;
        }
    }
}
</script>
@endpush
@endsection