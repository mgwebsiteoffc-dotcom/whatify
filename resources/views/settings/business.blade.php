@extends('layouts.app')
@section('title', 'Business Profile')
@section('page-title', 'Business Profile')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('business.update') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Company Name *</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $business->company_name ?? '') }}" required
                           class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 px-3 py-2 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Display Name</label>
                    <input type="text" name="display_name" value="{{ old('display_name', $business->display_name ?? '') }}"
                           class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 px-3 py-2 border">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Industry *</label>
                <select name="industry" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 px-3 py-2 border">
                    @foreach($industries as $key => $label)
                        <option value="{{ $key }}" {{ ($business->industry ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Website</label>
                <input type="url" name="website" value="{{ old('website', $business->website ?? '') }}"
                       class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 px-3 py-2 border">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" rows="3" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 px-3 py-2 border">{{ old('description', $business->description ?? '') }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">City</label>
                    <input type="text" name="city" value="{{ old('city', $business->city ?? '') }}"
                           class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 px-3 py-2 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">State</label>
                    <input type="text" name="state" value="{{ old('state', $business->state ?? '') }}"
                           class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 px-3 py-2 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Pincode</label>
                    <input type="text" name="pincode" value="{{ old('pincode', $business->pincode ?? '') }}"
                           class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 px-3 py-2 border">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">GSTIN</label>
                    <input type="text" name="gstin" value="{{ old('gstin', $business->gstin ?? '') }}"
                           class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 px-3 py-2 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Logo</label>
                    <input type="file" name="logo" accept="image/*" class="mt-1">
                </div>
            </div>

            <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700">
                Save Changes
            </button>
        </form>
    </div>
</div>
@endsection