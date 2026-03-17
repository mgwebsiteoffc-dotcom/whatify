@extends('layouts.app')
@section('title', 'Connect WooCommerce')
@section('page-title')
    <a href="{{ route('integrations.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left mr-2"></i></a>
    Connect WooCommerce
@endsection

@section('content')
<div class="max-w-2xl">
    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-6">
        <h4 class="text-sm font-semibold text-purple-800 mb-2"><i class="fab fa-wordpress mr-1"></i> WooCommerce Setup Guide</h4>
        <ol class="text-sm text-purple-700 space-y-1 list-decimal list-inside">
            <li>Go to WooCommerce → Settings → Advanced → REST API</li>
            <li>Click "Add key" to generate API credentials</li>
            <li>Set Permissions to <strong>Read/Write</strong></li>
            <li>Copy the Consumer Key and Consumer Secret</li>
            <li>Ensure your site has SSL (https://)</li>
        </ol>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('integrations.store', 'woocommerce') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700">Store URL *</label>
                <input type="url" name="store_url" value="{{ old('store_url', $existing?->config['store_url'] ?? '') }}"
                       required placeholder="https://yourstore.com"
                       class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Consumer Key *</label>
                <input type="text" name="consumer_key" required placeholder="ck_xxxxxxxxxxxxx"
                       class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border font-mono">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Consumer Secret *</label>
                <input type="password" name="consumer_secret" required placeholder="cs_xxxxxxxxxxxxx"
                       class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border font-mono">
            </div>

            <div class="border-t pt-4">
                <p class="text-sm font-medium text-gray-700 mb-3">Features</p>
                <div class="space-y-2">
                    @foreach([
                        ['sync_orders', 'Sync Orders', true],
                        ['order_confirmation', 'Order Confirmation Messages', true],
                        ['shipping_updates', 'Shipping Update Messages', true],
                    ] as [$name, $label, $default])
                        <label class="flex items-center gap-3">
                            <input type="hidden" name="{{ $name }}" value="0">
                            <input type="checkbox" name="{{ $name }}" value="1" {{ $default ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-emerald-600">
                            <span class="text-sm text-gray-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 font-medium">
                    <i class="fab fa-wordpress mr-2"></i>Connect WooCommerce
                </button>
                <a href="{{ route('integrations.index') }}" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection