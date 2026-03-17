@extends('layouts.app')
@section('title', 'Create Template')
@section('page-title', 'Create Message Template')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Form --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6">
            <form method="POST" action="{{ route('whatsapp.templates.store') }}" id="templateForm" class="space-y-5"
                  x-data="templateBuilder()">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">WhatsApp Account *</label>
                        <select name="whatsapp_account_id" required class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ old('whatsapp_account_id') == $acc->id ? 'selected' : '' }}>
                                    {{ $acc->display_name ?? $acc->phone_number }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Language *</label>
                        <select name="language" class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                            <option value="en" selected>English</option>
                            <option value="en_US">English (US)</option>
                            <option value="hi">Hindi</option>
                            <option value="mr">Marathi</option>
                            <option value="ta">Tamil</option>
                            <option value="te">Telugu</option>
                            <option value="bn">Bengali</option>
                            <option value="gu">Gujarati</option>
                            <option value="kn">Kannada</option>
                            <option value="ml">Malayalam</option>
                            <option value="pa">Punjabi</option>
                            <option value="ur">Urdu</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Template Name *</label>
                        <input type="text" name="name" x-model="name" value="{{ old('name') }}" required
                               pattern="[a-z0-9_]+" placeholder="order_confirmation"
                               class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border font-mono">
                        <p class="text-xs text-gray-500 mt-1">Lowercase, numbers and underscores only</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Category *</label>
                        <select name="category" x-model="category" class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                            <option value="marketing">Marketing</option>
                            <option value="utility">Utility</option>
                            <option value="authentication">Authentication</option>
                        </select>
                    </div>
                </div>

                {{-- Header --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Header</label>
                    <select name="header_type" x-model="headerType" class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                        <option value="none">No Header</option>
                        <option value="text">Text</option>
                        <option value="image">Image</option>
                        <option value="video">Video</option>
                        <option value="document">Document</option>
                    </select>

                    <div x-show="headerType === 'text'" class="mt-2">
                        <input type="text" name="header_text" x-model="headerText" maxlength="60"
                               placeholder="Header text (max 60 chars)"
                               class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    </div>

                    <div x-show="['image','video','document'].includes(headerType)" class="mt-2">
                        <input type="url" name="header_media_url" placeholder="Media URL (for sample)"
                               class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    </div>
                </div>

                {{-- Body --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Body *</label>
                    <textarea name="body" x-model="body" rows="5" required maxlength="1024"
                              placeholder="Hello {{1}}, your order {{2}} has been confirmed. Thank you for shopping with us!"
                              class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border"></textarea>
                    <div class="flex justify-between mt-1">
                        <p class="text-xs text-gray-500">Use {{ '{{1}}' }}, {{ '{{2}}' }}, etc. for variables</p>
                        <p class="text-xs text-gray-500" x-text="body.length + '/1024'"></p>
                    </div>

                    {{-- Sample variables --}}
                    <template x-if="bodyVariables.length > 0">
                        <div class="mt-2 space-y-2 p-3 bg-gray-50 rounded">
                            <p class="text-xs font-medium text-gray-600">Sample Values for Variables:</p>
                            <template x-for="(v, i) in bodyVariables" :key="i">
                                <input type="text" :name="'sample_body_vars[' + i + ']'" :placeholder="'Sample for {{' + (i+1) + '}}'"
                                       class="w-full rounded border-gray-300 text-sm px-2 py-1">
                            </template>
                        </div>
                    </template>
                </div>

                {{-- Footer --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Footer</label>
                    <input type="text" name="footer" x-model="footer" maxlength="60"
                           placeholder="Footer text (max 60 chars)"
                           class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                </div>

                {{-- Buttons --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium text-gray-700">Buttons (max 3)</label>
                        <button type="button" @click="addButton()" x-show="buttons.length < 3"
                                class="text-xs text-emerald-600 hover:text-emerald-700 font-medium">
                            <i class="fas fa-plus mr-1"></i> Add Button
                        </button>
                    </div>

                    <template x-for="(btn, index) in buttons" :key="index">
                        <div class="flex gap-2 mb-2 p-3 bg-gray-50 rounded">
                            <select :name="'buttons[' + index + '][type]'" x-model="btn.type"
                                    class="rounded-md border-gray-300 text-sm px-2 py-1.5">
                                <option value="QUICK_REPLY">Quick Reply</option>
                                <option value="URL">URL</option>
                                <option value="PHONE_NUMBER">Phone</option>
                            </select>
                            <input type="text" :name="'buttons[' + index + '][text]'" x-model="btn.text"
                                   placeholder="Button text" maxlength="25"
                                   class="flex-1 rounded-md border-gray-300 text-sm px-2 py-1.5">
                            <input x-show="btn.type === 'URL'" type="url" :name="'buttons[' + index + '][url]'"
                                   x-model="btn.url" placeholder="https://example.com/{{1}}"
                                   class="flex-1 rounded-md border-gray-300 text-sm px-2 py-1.5">
                            <input x-show="btn.type === 'PHONE_NUMBER'" type="text" :name="'buttons[' + index + '][phone_number]'"
                                   x-model="btn.phone_number" placeholder="+919876543210"
                                   class="flex-1 rounded-md border-gray-300 text-sm px-2 py-1.5">
                            <button type="button" @click="buttons.splice(index, 1)" class="text-red-400 hover:text-red-600 px-2">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </template>
                </div>

                <div class="flex gap-3 pt-4 border-t">
                    <button type="submit" class="px-6 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 font-medium">
                        <i class="fas fa-paper-plane mr-2"></i>Submit for Approval
                    </button>
                    <a href="{{ route('whatsapp.templates.index') }}" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Preview --}}
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-6 sticky top-20" x-data>
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Preview</h3>
            <div class="bg-[#ECE5DD] rounded-lg p-4 min-h-[300px]">
                <div class="bg-white rounded-lg shadow-sm p-3 max-w-[250px] ml-auto">
                    {{-- Header preview --}}
                    <div x-show="$root.querySelector('[name=header_type]').value === 'text'"
                         class="font-bold text-sm mb-1"
                         x-text="$root.querySelector('[name=header_text]')?.value || ''"></div>

                    <div x-show="['image','video','document'].includes($root.querySelector('[name=header_type]')?.value)"
                         class="bg-gray-200 rounded h-32 flex items-center justify-center mb-2">
                        <i class="fas fa-image text-2xl text-gray-400"></i>
                    </div>

                    {{-- Body preview --}}
                    <p class="text-sm text-gray-800 whitespace-pre-line"
                       x-text="$root.querySelector('[name=body]')?.value || 'Message body preview...'"></p>

                    {{-- Footer preview --}}
                    <p class="text-xs text-gray-400 mt-2"
                       x-text="$root.querySelector('[name=footer]')?.value || ''"></p>

                    <p class="text-[10px] text-gray-400 text-right mt-1">{{ now()->format('h:i A') }}</p>
                </div>
            </div>

            <div class="mt-4 p-3 bg-yellow-50 rounded text-xs text-yellow-700">
                <p class="font-medium"><i class="fas fa-info-circle mr-1"></i> Template Guidelines:</p>
                <ul class="mt-1 space-y-0.5 list-disc list-inside">
                    <li>Name: lowercase, underscores only</li>
                    <li>Body: max 1024 characters</li>
                    <li>Header/Footer: max 60 characters</li>
                    <li>Max 3 buttons per template</li>
                    <li>Approval takes 1-24 hours</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function templateBuilder() {
    return {
        name: '{{ old("name", "") }}',
        category: '{{ old("category", "marketing") }}',
        headerType: '{{ old("header_type", "none") }}',
        headerText: '{{ old("header_text", "") }}',
        body: '{{ old("body", "") }}',
        footer: '{{ old("footer", "") }}',
        buttons: [],

        get bodyVariables() {
            const matches = this.body.match(/\{\{(\d+)\}\}/g);
            return matches ? [...new Set(matches)] : [];
        },

        addButton() {
            if (this.buttons.length < 3) {
                this.buttons.push({ type: 'QUICK_REPLY', text: '', url: '', phone_number: '' });
            }
        },
    }
}
</script>
@endpush
@endsection