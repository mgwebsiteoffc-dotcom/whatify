@extends('layouts.app')
@section('title', 'Create Campaign')
@section('page-title', 'Create Campaign')

@section('content')
<div class="max-w-3xl" x-data="campaignCreator()">
    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('campaigns.store') }}" class="space-y-6">
            @csrf

            {{-- Step 1: Basic Info --}}
            <div class="border-b pb-6">
                <h3 class="text-lg font-semibold mb-4"><span class="text-emerald-600 mr-2">1.</span>Campaign Details</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Campaign Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Diwali Sale 2024"
                               class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">WhatsApp Account *</label>
                        <select name="whatsapp_account_id" required class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                            <option value="">Select Account</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->display_name ?? $acc->phone_number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Message Template *</label>
                        <select name="template_id" x-model="selectedTemplateId" required class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                            <option value="">Select Template</option>
                            @foreach($templates as $tpl)
                                <option value="{{ $tpl->id }}" data-body="{{ $tpl->body }}" data-category="{{ $tpl->category }}">
                                    {{ $tpl->name }} ({{ $tpl->category }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" rows="2" placeholder="Internal notes about this campaign..."
                                  class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Step 2: Audience --}}
            <div class="border-b pb-6">
                <h3 class="text-lg font-semibold mb-4"><span class="text-emerald-600 mr-2">2.</span>Select Audience</h3>
                <div class="space-y-3">
                    <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="audience_type" value="all" x-model="audienceType" class="text-emerald-600">
                        <div>
                            <span class="text-sm font-medium">All Active Contacts</span>
                            <span class="text-xs text-gray-500 ml-2">({{ number_format($totalContacts) }} contacts)</span>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="audience_type" value="tags" x-model="audienceType" class="text-emerald-600">
                        <div>
                            <span class="text-sm font-medium">By Tags</span>
                            <span class="text-xs text-gray-500 ml-2">Select specific tag groups</span>
                        </div>
                    </label>
                </div>

                <div x-show="audienceType === 'tags'" x-cloak class="mt-3 pl-8">
                    <div class="flex flex-wrap gap-2">
                        @foreach($tags as $tag)
                            <label class="inline-flex items-center gap-1.5 px-3 py-1.5 border rounded-full cursor-pointer hover:bg-gray-50 text-sm">
                                <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" class="rounded border-gray-300 text-emerald-600">
                                <span class="inline-block w-2 h-2 rounded-full" style="background-color: {{ $tag->color }}"></span>
                                {{ $tag->name }} ({{ $tag->contacts_count }})
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Step 3: Variables --}}
            <div class="border-b pb-6" x-show="hasVariables" x-cloak>
                <h3 class="text-lg font-semibold mb-4"><span class="text-emerald-600 mr-2">3.</span>Template Variables</h3>
                <p class="text-sm text-gray-500 mb-3">Map template variables to contact data or static values.</p>
                <template x-for="(v, idx) in variables" :key="idx">
                    <div class="flex gap-3 mb-3 items-center">
                        <span class="text-sm font-mono text-gray-500 w-16" x-text="'{{' + (idx+1) + '}}'"></span>
                        <select :name="'template_variables['+idx+'][source]'" class="rounded-md border-gray-300 text-sm px-3 py-2 border flex-1">
                            <option value="static">Static Value</option>
                            <option value="contact_name">Contact Name</option>
                            <option value="contact_phone">Contact Phone</option>
                            <option value="contact_email">Contact Email</option>
                            <option value="custom_attribute">Custom Attribute</option>
                        </select>
                        <input type="text" :name="'template_variables['+idx+'][value]'" placeholder="Value or attribute key"
                               class="rounded-md border-gray-300 text-sm px-3 py-2 border flex-1">
                    </div>
                </template>
            </div>

            {{-- Step 4: Schedule --}}
            <div class="border-b pb-6">
<h3 class="text-lg font-semibold mb-4">
    <span class="text-emerald-600 mr-2" x-text="hasVariables ? '4.' : '3.'"></span>
    Scheduling & Settings
</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Schedule (optional)</label>
                        <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}"
                               class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                        <p class="text-xs text-gray-500 mt-1">Leave empty for immediate send</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Messages Per Second</label>
                        <input type="number" name="messages_per_second" value="{{ old('messages_per_second', 30) }}" min="1" max="80"
                               class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                        <p class="text-xs text-gray-500 mt-1">Recommended: 30 (max: 80)</p>
                    </div>
                </div>
            </div>

            {{-- Cost Estimate --}}
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-yellow-800">Estimated Cost</p>
                        <p class="text-xs text-yellow-600">Based on template category and audience size</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-yellow-800">
                            Wallet: <strong>₹{{ number_format(auth()->user()->wallet?->balance ?? 0, 2) }}</strong>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3">
                <button type="submit" name="action" value="send_now" class="px-6 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 font-medium"
                        onclick="return confirm('Send campaign now?')">
                    <i class="fas fa-paper-plane mr-2"></i>Send Now
                </button>
                <button type="submit" name="action" value="schedule" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">
                    <i class="fas fa-clock mr-2"></i>Schedule
                </button>
                <button type="submit" name="action" value="draft" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 font-medium">
                    <i class="fas fa-save mr-2"></i>Save Draft
                </button>
                <a href="{{ route('campaigns.index') }}" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function campaignCreator() {
    return {
        audienceType: 'all',
        selectedTemplateId: '',
        variables: [],
        get hasVariables() {
            return this.variables.length > 0;
        },
        init() {
            this.$watch('selectedTemplateId', (id) => {
                const option = document.querySelector(`option[value="${id}"]`);
                if (option) {
                    const body = option.dataset.body || '';
                    const matches = body.match(/\{\{(\d+)\}\}/g);
                    this.variables = matches ? [...new Set(matches)].map(() => ({})) : [];
                }
            });
        }
    }
}
</script>
@endpush
@endsection