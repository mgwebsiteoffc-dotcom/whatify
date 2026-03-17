@extends('layouts.app')
@section('title', 'Create Automation')
@section('page-title', 'Create Automation')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-lg shadow p-6">
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
                        <label class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 {{ old('trigger_type') === $value ? 'border-emerald-500 bg-emerald-50' : '' }}">
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