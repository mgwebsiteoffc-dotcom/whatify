@extends('layouts.app')
@section('title', 'Add Contact')
@section('page-title', 'Add Contact')

@section('content')
<div class="max-w-2xl" x-data="{ customAttrs: [{ key: '', value: '' }] }">
    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('contacts.store') }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border focus:border-emerald-500 focus:ring-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Country Code *</label>
                    <select name="country_code" class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                        <option value="91" selected>+91 (India)</option>
                        <option value="1">+1 (US/Canada)</option>
                        <option value="44">+44 (UK)</option>
                        <option value="971">+971 (UAE)</option>
                        <option value="65">+65 (Singapore)</option>
                        <option value="61">+61 (Australia)</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Phone Number *</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="9876543210"
                           class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border focus:border-emerald-500 focus:ring-emerald-500 font-mono">
                    <p class="text-xs text-gray-500 mt-1">10-digit mobile number without country code</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Source</label>
                <select name="source" class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    <option value="manual">Manual</option>
                    <option value="website">Website</option>
                    <option value="whatsapp">WhatsApp</option>
                    <option value="shopify">Shopify</option>
                    <option value="form">Form</option>
                    <option value="referral">Referral</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tags</label>
                <div class="flex flex-wrap gap-2">
                    @foreach($tags as $tag)
                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 border rounded-full cursor-pointer hover:bg-gray-50 text-sm">
                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}" class="rounded border-gray-300 text-emerald-600">
                            <span class="inline-block w-2 h-2 rounded-full" style="background-color: {{ $tag->color }}"></span>
                            {{ $tag->name }}
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Custom Attributes --}}
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-medium text-gray-700">Custom Attributes</label>
                    <button type="button" @click="customAttrs.push({key:'', value:''})" class="text-xs text-emerald-600 hover:text-emerald-700">
                        <i class="fas fa-plus mr-1"></i> Add Field
                    </button>
                </div>
                <template x-for="(attr, idx) in customAttrs" :key="idx">
                    <div class="flex gap-2 mb-2">
                        <input type="text" :name="'custom_attributes['+idx+'][key]'" x-model="attr.key" placeholder="Field name (e.g. city)"
                               class="flex-1 rounded-md border-gray-300 text-sm px-3 py-2 border">
                        <input type="text" :name="'custom_attributes['+idx+'][value]'" x-model="attr.value" placeholder="Value"
                               class="flex-1 rounded-md border-gray-300 text-sm px-3 py-2 border">
                        <button type="button" @click="customAttrs.splice(idx, 1)" class="text-red-400 hover:text-red-600 px-2">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </template>
            </div>

            <div class="flex gap-3 pt-4 border-t">
                <button type="submit" class="px-6 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 font-medium">
                    <i class="fas fa-save mr-2"></i>Save Contact
                </button>
                <a href="{{ route('contacts.index') }}" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection