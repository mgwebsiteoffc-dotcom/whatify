@extends('layouts.app')
@section('title', $integration->name)
@section('page-title')
    <a href="{{ route('integrations.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left mr-2"></i></a>
    {{ $integration->name }}
@endsection

@section('content')
<div class="space-y-6">
    {{-- Status Card --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                @php
                    $iconMap = [
                        'shopify' => ['fab fa-shopify', '#96BF48'],
                        'woocommerce' => ['fab fa-wordpress', '#96588A'],
                        'google_sheets' => ['fas fa-table', '#0F9D58'],
                    ];
                    [$icon, $color] = $iconMap[$integration->type] ?? ['fas fa-plug', '#6B7280'];
                @endphp
                <div class="h-14 w-14 rounded-lg flex items-center justify-center" style="background-color: {{ $color }}20">
                    <i class="{{ $icon }} text-3xl" style="color: {{ $color }}"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $integration->name }}</h2>
                    <p class="text-sm text-gray-500">
                        @if($integration->config['shop_domain'] ?? null)
                            {{ $integration->config['shop_domain'] }}
                        @elseif($integration->config['store_url'] ?? null)
                            {{ $integration->config['store_url'] }}
                        @elseif($integration->config['spreadsheet_title'] ?? null)
                            {{ $integration->config['spreadsheet_title'] }}
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium
                    {{ $integration->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    <span class="mr-1.5 h-2 w-2 rounded-full {{ $integration->status === 'active' ? 'bg-green-500' : 'bg-red-500' }}"></span>
                    {{ ucfirst($integration->status) }}
                </span>
                @if($integration->last_synced_at)
                    <span class="text-xs text-gray-400">Synced {{ $integration->last_synced_at->diffForHumans() }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Actions</h3>
        <div class="flex flex-wrap gap-3">
            {{-- Test Connection --}}
            <form method="POST" action="{{ route('integrations.sync', [$integration, 'test']) }}">
                @csrf
                <button class="px-4 py-2 border rounded-md text-sm hover:bg-gray-50">
                    <i class="fas fa-plug mr-1"></i> Test Connection
                </button>
            </form>

            @if(in_array($integration->type, ['shopify', 'woocommerce']))
                <form method="POST" action="{{ route('integrations.sync', [$integration, 'orders']) }}">
                    @csrf
                    <button class="px-4 py-2 border rounded-md text-sm hover:bg-gray-50">
                        <i class="fas fa-shopping-cart mr-1"></i> Sync Orders
                    </button>
                </form>
            @endif

            @if($integration->type === 'shopify')
                <form method="POST" action="{{ route('integrations.sync', [$integration, 'customers']) }}">
                    @csrf
                    <button class="px-4 py-2 border rounded-md text-sm hover:bg-gray-50">
                        <i class="fas fa-users mr-1"></i> Sync Customers
                    </button>
                </form>
            @endif

            @if($integration->type === 'google_sheets')
                <form method="POST" action="{{ route('integrations.sync', [$integration, 'import_contacts']) }}">
                    @csrf
                    <button class="px-4 py-2 border rounded-md text-sm hover:bg-gray-50">
                        <i class="fas fa-file-import mr-1"></i> Import Contacts
                    </button>
                </form>
                <form method="POST" action="{{ route('integrations.sync', [$integration, 'export_contacts']) }}">
                    @csrf
                    <button class="px-4 py-2 border rounded-md text-sm hover:bg-gray-50">
                        <i class="fas fa-file-export mr-1"></i> Export Contacts
                    </button>
                </form>
            @endif

            <form method="POST" action="{{ route('integrations.disconnect', $integration) }}"
                  onsubmit="return confirm('Disconnect this integration?')">
                @csrf
                <button class="px-4 py-2 border border-red-300 text-red-600 rounded-md text-sm hover:bg-red-50">
                    <i class="fas fa-unlink mr-1"></i> Disconnect
                </button>
            </form>

            <form method="POST" action="{{ route('integrations.destroy', $integration) }}"
                  onsubmit="return confirm('Delete this integration permanently?')">
                @csrf @method('DELETE')
                <button class="px-4 py-2 border border-red-300 text-red-600 rounded-md text-sm hover:bg-red-50">
                    <i class="fas fa-trash mr-1"></i> Delete
                </button>
            </form>
        </div>
    </div>

    {{-- Configuration --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Configuration</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
            @foreach($integration->config ?? [] as $key => $value)
                @if(!in_array($key, ['access_token', 'consumer_secret', 'service_account_json', 'api_key']))
                    <div class="flex justify-between py-2 border-b">
                        <span class="text-gray-500 capitalize">{{ str_replace('_', ' ', $key) }}</span>
                        <span class="font-medium text-gray-900">
                            @if(is_bool($value))
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs {{ $value ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $value ? 'Enabled' : 'Disabled' }}
                                </span>
                            @elseif(is_array($value))
                                {{ implode(', ', $value) }}
                            @else
                                {{ Str::limit((string)$value, 50) }}
                            @endif
                        </span>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Recent Logs --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold">Recent Activity</h3>
        </div>
        @if($logs->isEmpty())
            <div class="p-8 text-center text-gray-500 text-sm">No activity logs yet</div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Details</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ $log->event }}</td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                    {{ $log->status === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-xs text-gray-500">
                                @if($log->error_message)
                                    <span class="text-red-500">{{ Str::limit($log->error_message, 60) }}</span>
                                @elseif($log->payload)
                                    @php
                                        $summary = [];
                                        if (isset($log->payload['count'])) $summary[] = "Count: {$log->payload['count']}";
                                        if (isset($log->payload['imported'])) $summary[] = "Imported: {$log->payload['imported']}";
                                        if (isset($log->payload['synced'])) $summary[] = "Synced: {$log->payload['synced']}";
                                        if (isset($log->payload['shop'])) $summary[] = $log->payload['shop'];
                                    @endphp
                                    {{ implode(' · ', $summary) ?: '-' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-3 text-xs text-gray-500">{{ $log->created_at->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection