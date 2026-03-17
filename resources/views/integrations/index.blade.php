@extends('layouts.app')
@section('title', 'Integrations')
@section('page-title', 'Integration Marketplace')

@section('content')
<div class="space-y-6">

    {{-- Connected Integrations --}}
    @if($integrations->isNotEmpty())
        <div>
            <h3 class="text-lg font-semibold mb-4">Connected Integrations</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($integrations as $integration)
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="p-5">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    @php
                                        $iconMap = [
                                            'shopify' => ['fab fa-shopify', '#96BF48'],
                                            'woocommerce' => ['fab fa-wordpress', '#96588A'],
                                            'google_sheets' => ['fas fa-table', '#0F9D58'],
                                        ];
                                        [$icon, $color] = $iconMap[$integration->type] ?? ['fas fa-plug', '#6B7280'];
                                    @endphp
                                    <div class="h-10 w-10 rounded-lg flex items-center justify-center" style="background-color: {{ $color }}20">
                                        <i class="{{ $icon }} text-xl" style="color: {{ $color }}"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-900">{{ $integration->name }}</h4>
                                        <p class="text-xs text-gray-500">
                                            @if($integration->config['shop_domain'] ?? null)
                                                {{ $integration->config['shop_domain'] }}
                                            @elseif($integration->config['store_url'] ?? null)
                                                {{ parse_url($integration->config['store_url'], PHP_URL_HOST) }}
                                            @elseif($integration->config['spreadsheet_title'] ?? null)
                                                {{ $integration->config['spreadsheet_title'] }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                    {{ $integration->status === 'active' ? 'bg-green-100 text-green-700' :
                                       ($integration->status === 'error' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700') }}">
                                    <span class="mr-1 h-1.5 w-1.5 rounded-full
                                        {{ $integration->status === 'active' ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                    {{ ucfirst($integration->status) }}
                                </span>
                            </div>

                            @if($integration->last_synced_at)
                                <p class="text-xs text-gray-400 mb-3">
                                    <i class="fas fa-sync mr-1"></i>Last synced: {{ $integration->last_synced_at->diffForHumans() }}
                                </p>
                            @endif

                            @if($integration->error_message)
                                <p class="text-xs text-red-500 mb-3">
                                    <i class="fas fa-exclamation-circle mr-1"></i>{{ Str::limit($integration->error_message, 60) }}
                                </p>
                            @endif

                            <div class="flex gap-2">
                                <a href="{{ route('integrations.show', $integration) }}" class="flex-1 text-center px-3 py-1.5 border rounded-md text-xs hover:bg-gray-50">
                                    <i class="fas fa-cog mr-1"></i>Manage
                                </a>
                                <form method="POST" action="{{ route('integrations.sync', [$integration, 'test']) }}">
                                    @csrf
                                    <button class="px-3 py-1.5 border rounded-md text-xs hover:bg-gray-50">
                                        <i class="fas fa-plug mr-1"></i>Test
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Available Integrations --}}
    <div>
        <h3 class="text-lg font-semibold mb-4">Available Integrations</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($available as $item)
                @php
                    $isConnected = $integrations->where('type', $item['type'])->isNotEmpty();
                @endphp
                <div class="bg-white rounded-lg shadow overflow-hidden {{ !$item['available'] ? 'opacity-60' : '' }}">
                    <div class="p-5">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="h-12 w-12 rounded-lg flex items-center justify-center" style="background-color: {{ $item['color'] }}20">
                                <i class="{{ $item['icon'] }} text-2xl" style="color: {{ $item['color'] }}"></i>
                            </div>
                            <div>
                                <h4 class="text-base font-semibold text-gray-900">{{ $item['name'] }}</h4>
                                @if($isConnected)
                                    <span class="text-xs text-green-600"><i class="fas fa-check-circle mr-1"></i>Connected</span>
                                @endif
                            </div>
                        </div>

                        <p class="text-sm text-gray-500 mb-3">{{ $item['description'] }}</p>

                        <div class="mb-4">
                            <ul class="space-y-1">
                                @foreach($item['features'] as $feature)
                                    <li class="text-xs text-gray-600 flex items-center gap-1.5">
                                        <i class="fas fa-check text-green-500 text-[10px]"></i>{{ $feature }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        @if($item['available'])
                            @if($isConnected)
                                <a href="{{ route('integrations.show', $integrations->where('type', $item['type'])->first()) }}"
                                   class="block text-center px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">
                                    <i class="fas fa-cog mr-1"></i>Manage
                                </a>
                            @else
                                <a href="{{ route('integrations.create', $item['type']) }}"
                                   class="block text-center px-4 py-2 text-white rounded-md text-sm hover:opacity-90"
                                   style="background-color: {{ $item['color'] }}">
                                    <i class="fas fa-plug mr-1"></i>Connect {{ $item['name'] }}
                                </a>
                            @endif
                        @else
                            <a href="{{ route('billing.plans') }}"
                               class="block text-center px-4 py-2 bg-gray-100 text-gray-500 rounded-md text-sm">
                                <i class="fas fa-lock mr-1"></i>Upgrade to Connect
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection