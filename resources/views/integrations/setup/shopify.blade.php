@extends('layouts.app')
@section('title', 'Connect Shopify')
@section('page-title')
    <a href="{{ route('integrations.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left mr-2"></i></a>
    Connect Shopify
@endsection

@section('content')
<div class="max-w-2xl">
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <h4 class="text-sm font-semibold text-green-800 mb-2"><i class="fab fa-shopify mr-1"></i> Shopify Setup Guide</h4>
        <ol class="text-sm text-green-700 space-y-1 list-decimal list-inside">
            <li>Go to your Shopify Admin → Settings → Apps and sales channels</li>
            <li>Click "Develop apps" → Create an app</li>
            <li>Configure Admin API scopes: <code class="bg-green-100 px-1 rounded">read_orders, read_customers, read_checkouts, write_webhooks</code></li>
            <li>Install the app and copy the Admin API access token</li>
            <li>Your shop domain format: <code class="bg-green-100 px-1 rounded">your-store.myshopify.com</code></li>
        </ol>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('integrations.store', 'shopify') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700">Shop Domain *</label>
                <input type="text" name="shop_domain" value="{{ old('shop_domain', $existing?->config['shop_domain'] ?? '') }}"
                       required placeholder="your-store.myshopify.com"
                       class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border font-mono">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Admin API Access Token *</label>
                <input type="password" name="access_token" required placeholder="shpat_xxxxxxxxxxxxx"
                       class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border font-mono">
            </div>

            <div class="border-t pt-4">
                <p class="text-sm font-medium text-gray-700 mb-3">Automation Features</p>
                <div class="space-y-2">
                    @foreach([
                        ['sync_orders', 'Sync Orders', true],
                        ['sync_customers', 'Sync Customers', true],
                        ['order_confirmation', 'Order Confirmation Messages', true],
                        ['shipping_updates', 'Shipping Update Messages', true],
                        ['abandoned_cart', 'Abandoned Cart Recovery', true],
                        ['cod_verification', 'COD Verification', false],
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

            <div class="bg-gray-50 rounded-lg p-4 text-sm">
                <p class="font-medium text-gray-700 mb-1">Webhook URL (auto-configured)</p>
                <p class="text-gray-500 text-xs">Webhooks will be automatically registered in your Shopify store.</p>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 font-medium">
                    <i class="fab fa-shopify mr-2"></i>Connect Shopify
                </button>
                <a href="{{ route('integrations.index') }}" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection