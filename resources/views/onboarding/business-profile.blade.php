@extends('layouts.guest')
@section('title', 'Setup - Business Profile')
@section('heading', 'Tell us about your business')
@section('subheading', 'Step 1 of 4')

@section('content')
<form method="POST" action="{{ route('onboarding.business-profile') }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    <div>
        <label class="block text-sm font-medium text-gray-700">Company Name *</label>
        <input type="text" name="company_name" value="{{ old('company_name') }}" required
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-3 py-2 border">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Display Name</label>
        <input type="text" name="display_name" value="{{ old('display_name') }}"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-3 py-2 border">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Website</label>
        <input type="url" name="website" value="{{ old('website') }}" placeholder="https://example.com"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-3 py-2 border">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Business Size *</label>
        <select name="business_size" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-3 py-2 border">
            <option value="">Select</option>
            <option value="1-10">1-10 employees</option>
            <option value="11-50">11-50 employees</option>
            <option value="51-200">51-200 employees</option>
            <option value="201-500">201-500 employees</option>
            <option value="500+">500+ employees</option>
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Logo</label>
        <input type="file" name="logo" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
    </div>

    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700">
        Continue <i class="fas fa-arrow-right ml-2"></i>
    </button>
</form>
@endsection