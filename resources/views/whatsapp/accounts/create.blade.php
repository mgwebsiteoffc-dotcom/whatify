@extends('layouts.app')
@section('title', 'Connect WhatsApp')
@section('page-title', 'Connect WhatsApp Number')

@section('content')
<div class="max-w-2xl">
    {{-- Instructions --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <h4 class="text-sm font-semibold text-blue-800 mb-2"><i class="fas fa-info-circle mr-1"></i> How to get your API credentials</h4>
        <ol class="text-sm text-blue-700 space-y-1 list-decimal list-inside">
            <li>Go to <a href="https://business.facebook.com" target="_blank" class="underline">Meta Business Suite</a></li>
            <li>Navigate to WhatsApp Manager → Phone Numbers</li>
            <li>Copy your Phone Number ID, WABA ID and generate a permanent Access Token</li>
            <li>Ensure your webhook URL is set to: <code class="bg-blue-100 px-1 rounded">{{ url('/api/webhook/whatsapp') }}</code></li>
            <li>Webhook Verify Token: <code class="bg-blue-100 px-1 rounded">{{ config('whatify.whatsapp.verify_token') }}</code></li>
        </ol>
    </div>

    <div class="bg-white rounded-lg shadow p-6">

    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-lg">
    <p class="text-sm text-emerald-800">
        <i class="fas fa-magic mr-1"></i>
        Want an easier setup?
        <a href="{{ route('whatsapp.accounts.embeddedSignup') }}" class="font-semibold underline">Use the guided setup wizard →</a>
    </p>
</div>
        <form method="POST" action="{{ route('whatsapp.accounts.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700">Phone Number *</label>
                <input type="text" name="phone_number" value="{{ old('phone_number') }}" required
                       placeholder="919876543210"
                       class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-3 py-2 border">
                <p class="text-xs text-gray-500 mt-1">With country code, no + sign</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Display Name</label>
                <input type="text" name="display_name" value="{{ old('display_name') }}"
                       placeholder="Your Business Name"
                       class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-3 py-2 border">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Phone Number ID *</label>
                <input type="text" name="phone_number_id" value="{{ old('phone_number_id') }}" required
                       placeholder="1234567890123456"
                       class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-3 py-2 border">
                <p class="text-xs text-gray-500 mt-1">Found in WhatsApp Manager → Phone Numbers</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">WABA ID (WhatsApp Business Account ID) *</label>
                <input type="text" name="waba_id" value="{{ old('waba_id') }}" required
                       placeholder="1234567890123456"
                       class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-3 py-2 border">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Meta Business ID</label>
                <input type="text" name="business_id_meta" value="{{ old('business_id_meta') }}"
                       placeholder="1234567890123456"
                       class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-3 py-2 border">
            </div>

        <div>
    <label class="block text-sm font-medium text-gray-700">Permanent Access Token *</label>
    <textarea name="access_token" rows="3" required
              class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-3 py-2 border font-mono text-xs">{{ old('access_token', session('access_token', '')) }}</textarea>
    <p class="text-xs text-gray-500 mt-1">Generate from Meta Business Settings → System Users</p>
</div>

            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Webhook Configuration</h4>
                <div class="space-y-2 text-sm">
                    <div>
                        <span class="text-gray-500">Callback URL:</span>
                        <code class="ml-2 bg-white px-2 py-1 rounded border text-xs">{{ url('/api/webhook/whatsapp')}}</code>
                    </div>
                    <div>
                        <span class="text-gray-500">Verify Token:</span>
                        <code class="ml-2 bg-white px-2 py-1 rounded border text-xs">{{ config('whatify.whatsapp.verify_token') }}</code>
                    </div>
                    <div>
                        <span class="text-gray-500">Subscribed Fields:</span>
                        <code class="ml-2 bg-white px-2 py-1 rounded border text-xs">messages</code>
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-6 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 font-medium">
                    <i class="fas fa-plug mr-2"></i>Connect Account
                </button>
                <a href="{{ route('whatsapp.accounts.index') }}" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 font-medium">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection