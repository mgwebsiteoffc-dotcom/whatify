@extends('layouts.app')
@section('title', 'Template Details')
@section('page-title')
    <a href="{{ route('whatsapp.templates.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left mr-2"></i></a>
    Template: {{ $template->name }}
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        {{-- Details --}}
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Template Details</h3>
                <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium
                    {{ $template->status === 'approved' ? 'bg-green-100 text-green-700' :
                       ($template->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                    {{ ucfirst($template->status) }}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-500">Name:</span>
                    <span class="ml-2 font-mono font-medium">{{ $template->name }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Category:</span>
                    <span class="ml-2 capitalize font-medium">{{ $template->category }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Language:</span>
                    <span class="ml-2 font-medium">{{ $template->language }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Meta Template ID:</span>
                    <span class="ml-2 font-mono text-xs">{{ $template->template_id_meta ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Created:</span>
                    <span class="ml-2">{{ $template->created_at->format('M d, Y h:i A') }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Message Cost:</span>
                    <span class="ml-2 font-medium">₹{{ config("whatify.message_cost.{$template->category}") }}</span>
                </div>
            </div>

            @if($template->rejection_reason)
                <div class="mt-4 p-3 bg-red-50 rounded text-sm text-red-700">
                    <strong>Rejection Reason:</strong> {{ $template->rejection_reason }}
                </div>
            @endif
        </div>

        {{-- Content --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Template Content</h3>

            @if($template->header)
                <div class="mb-3">
                    <span class="text-xs font-medium text-gray-500 uppercase">Header ({{ $template->header['type'] ?? 'text' }})</span>
                    <p class="text-sm mt-1 font-medium">{{ $template->header['text'] ?? 'Media header' }}</p>
                </div>
            @endif

            <div class="mb-3">
                <span class="text-xs font-medium text-gray-500 uppercase">Body</span>
                <p class="text-sm mt-1 whitespace-pre-line bg-gray-50 p-3 rounded">{{ $template->body }}</p>
            </div>

            @if($template->footer)
                <div class="mb-3">
                    <span class="text-xs font-medium text-gray-500 uppercase">Footer</span>
                    <p class="text-sm mt-1 text-gray-500">{{ $template->footer }}</p>
                </div>
            @endif

            @if($template->buttons)
                <div>
                    <span class="text-xs font-medium text-gray-500 uppercase">Buttons</span>
                    <div class="mt-1 space-y-1">
                        @foreach($template->buttons as $btn)
                            <div class="flex items-center gap-2 text-sm p-2 bg-gray-50 rounded">
                                <span class="inline-flex items-center rounded px-1.5 py-0.5 text-xs bg-blue-100 text-blue-700">
                                    {{ $btn['type'] ?? 'QUICK_REPLY' }}
                                </span>
                                <span>{{ $btn['text'] ?? '' }}</span>
                                @if(!empty($btn['url']))
                                    <span class="text-gray-400 text-xs">→ {{ $btn['url'] }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Phone Preview --}}
    <div>
        <div class="bg-white rounded-lg shadow p-6 sticky top-20">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Phone Preview</h3>
            <div class="bg-[#ECE5DD] rounded-lg p-4">
                <div class="bg-white rounded-lg shadow-sm p-3 max-w-[250px] ml-auto">
                    @if($template->header)
                        @if(($template->header['type'] ?? 'text') === 'text')
                            <p class="font-bold text-sm mb-1">{{ $template->header['text'] ?? '' }}</p>
                        @else
                            <div class="bg-gray-200 rounded h-32 flex items-center justify-center mb-2">
                                <i class="fas fa-{{ $template->header['type'] === 'image' ? 'image' : ($template->header['type'] === 'video' ? 'play-circle' : 'file') }} text-2xl text-gray-400"></i>
                            </div>
                        @endif
                    @endif

                    <p class="text-sm text-gray-800 whitespace-pre-line">{{ $template->body }}</p>

                    @if($template->footer)
                        <p class="text-xs text-gray-400 mt-2">{{ $template->footer }}</p>
                    @endif

                    <p class="text-[10px] text-gray-400 text-right mt-1">{{ now()->format('h:i A') }} ✓✓</p>
                </div>

                @if($template->buttons)
                    <div class="mt-1 space-y-1 max-w-[250px] ml-auto">
                        @foreach($template->buttons as $btn)
                            <div class="bg-white rounded-lg text-center py-2 text-sm text-blue-500 font-medium shadow-sm">
                                @if(($btn['type'] ?? '') === 'URL')
                                    <i class="fas fa-external-link-alt mr-1"></i>
                                @elseif(($btn['type'] ?? '') === 'PHONE_NUMBER')
                                    <i class="fas fa-phone mr-1"></i>
                                @endif
                                {{ $btn['text'] ?? '' }}
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="mt-4 flex gap-2">
                <form method="POST" action="{{ route('whatsapp.templates.destroy', $template) }}"
                      onsubmit="return confirm('Delete this template?')" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full px-3 py-2 border border-red-300 text-red-600 rounded-md text-sm hover:bg-red-50">
                        <i class="fas fa-trash mr-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection