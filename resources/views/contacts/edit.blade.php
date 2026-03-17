@extends('layouts.app')
@section('title', 'Edit Contact')
@section('page-title', 'Edit Contact')

@section('content')
<div class="max-w-2xl" x-data="{
    customAttrs: {{ json_encode(collect($contact->custom_attributes ?? [])->map(fn($v, $k) => ['key' => $k, 'value' => $v])->values()) ?: '[{key:\"\",value:\"\"}]' }}
}">
    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('contacts.update', $contact) }}" class="space-y-5">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="name" value="{{ old('name', $contact->name) }}"
                           class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" value="{{ old('email', $contact->email) }}"
                           class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Country Code</label>
                    <select name="country_code" class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                        <option value="91" {{ $contact->country_code == '91' ? 'selected' : '' }}>+91</option>
                        <option value="1" {{ $contact->country_code == '1' ? 'selected' : '' }}>+1</option>
                        <option value="44" {{ $contact->country_code == '44' ? 'selected' : '' }}>+44</option>
                        <option value="971" {{ $contact->country_code == '971' ? 'selected' : '' }}>+971</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Phone (read-only)</label>
                    <input type="text" value="{{ $contact->phone }}" disabled
                           class="mt-1 w-full rounded-md border-gray-200 bg-gray-50 text-sm px-3 py-2 border font-mono">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    @foreach(['active', 'inactive', 'blocked', 'opted_out'] as $s)
                        <option value="{{ $s }}" {{ $contact->status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tags</label>
                <div class="flex flex-wrap gap-2">
                    @foreach($tags as $tag)
                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 border rounded-full cursor-pointer hover:bg-gray-50 text-sm">
                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                   {{ $contact->tags->contains($tag->id) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-emerald-600">
                            <span class="inline-block w-2 h-2 rounded-full" style="background-color: {{ $tag->color }}"></span>
                            {{ $tag->name }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-medium text-gray-700">Custom Attributes</label>
                    <button type="button" @click="customAttrs.push({key:'', value:''})" class="text-xs text-emerald-600">
                        <i class="fas fa-plus mr-1"></i> Add
                    </button>
                </div>
                <template x-for="(attr, idx) in customAttrs" :key="idx">
                    <div class="flex gap-2 mb-2">
                        <input type="text" :name="'custom_attributes['+idx+'][key]'" x-model="attr.key" placeholder="Key"
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
                <button type="submit" class="px-6 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700">
                    <i class="fas fa-save mr-2"></i>Save Changes
                </button>
                <a href="{{ route('contacts.show', $contact) }}" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection