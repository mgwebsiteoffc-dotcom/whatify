@extends('layouts.app')
@section('title', 'Create Automation')
@section('page-title', 'Create Automation')

@section('content')
<div class="max-w-4xl space-y-8">

    {{-- Industry Templates --}}
    @if(isset($industryTemplates) && $industryTemplates->isNotEmpty())
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-semibold mb-2">
                <i class="fas fa-magic text-purple-500 mr-2"></i>Quick Start with Templates
            </h3>
            <p class="text-sm text-gray-500 mb-6">Pre-built automation flows for your industry. Click to use and customize.</p>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($industryTemplates as $template)
                    <div class="border-2 rounded-xl p-4 hover:border-emerald-400 hover:bg-emerald-50 transition-all group">
                        <div class="flex items-start justify-between mb-2">
                            <h4 class="font-semibold text-gray-900 text-sm group-hover:text-emerald-700">
                                {{ $template->name }}
                            </h4>
                            <span class="text-[10px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full capitalize">
                                {{ str_replace('_', ' ', $template->industry) }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mb-3 line-clamp-2">{{ $template->description }}</p>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 text-xs text-gray-400">
                                <span><i class="fas fa-bolt mr-0.5"></i>{{ ucfirst(str_replace('_', ' ', $template->trigger_type)) }}</span>
                                <span><i class="fas fa-project-diagram mr-0.5"></i>{{ count($template->steps) }} steps</span>
                            </div>
                            <form method="POST" action="{{ route('automations.useTemplate') }}">
                                @csrf
                                <input type="hidden" name="template_id" value="{{ $template->id }}">
                                <button type="submit" class="text-xs font-semibold text-emerald-600 hover:text-emerald-800 bg-emerald-100 px-3 py-1 rounded-full hover:bg-emerald-200 transition-colors">
                                    Use Template →
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="text-center text-sm text-gray-400 flex items-center gap-4">
            <div class="flex-1 border-t"></div>
            <span>OR CREATE FROM SCRATCH</span>
            <div class="flex-1 border-t"></div>
        </div>
    @endif

    {{-- Create From Scratch Form --}}
    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Create Custom Automation</h3>
        <form method="POST" action="{{ route('automations.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700">Automation Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Welcome Bot, Order Confirmation..."
                       class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" rows="2" class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Trigger Type *</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach([
                        ['keyword', 'Keyword Match', 'fas fa-key', 'When customer sends specific keywords'],
                        ['incoming_message', 'Any Message', 'fas fa-comment', 'Any incoming message triggers this'],
                        ['contact_created', 'New Contact', 'fas fa-user-plus', 'When a new contact is added'],
                        ['tag_added', 'Tag Added', 'fas fa-tag', 'When a tag is applied to contact'],
                        ['shopify_order', 'Shopify Order', 'fas fa-shopping-cart', 'When new Shopify order is placed'],
                        ['campaign_reply', 'Campaign Reply', 'fas fa-reply', 'When customer replies to a campaign'],
                        ['api_trigger', 'API Trigger', 'fas fa-code', 'Triggered via external API call'],
                    ] as [$value, $label, $icon, $desc])
                        <label class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50">
                            <input type="radio" name="trigger_type" value="{{ $value }}" class="mt-1 text-emerald-600" {{ old('trigger_type') === $value ? 'checked' : '' }} required>
                            <div>
                                <div class="flex items-center gap-2">
                                    <i class="{{ $icon }} text-gray-400 text-sm"></i>
                                    <span class="text-sm font-medium">{{ $label }}</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $desc }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex gap-3 pt-4 border-t">
                <button type="submit" class="px-6 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 font-medium">
                    <i class="fas fa-plus mr-2"></i>Create & Build Flow
                </button>
                <a href="{{ route('automations.index') }}" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection