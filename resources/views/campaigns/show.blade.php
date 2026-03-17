@extends('layouts.app')
@section('title', $campaign->name)
@section('page-title')
    <a href="{{ route('campaigns.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left mr-2"></i></a>
    {{ $campaign->name }}
@endsection

@section('content')
<div class="space-y-6">
    {{-- Status Banner --}}
    @if(in_array($campaign->status, ['sending', 'processing']))
        <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-3 w-3 rounded-full bg-orange-500 animate-pulse"></div>
                <p class="text-sm font-medium text-orange-800">Campaign is actively sending messages...</p>
                <p class="text-xs text-orange-600">{{ number_format($campaign->sent_count) }} / {{ number_format($campaign->total_contacts) }} sent</p>
            </div>
            <form method="POST" action="{{ route('campaigns.pause', $campaign) }}">@csrf
                <button class="px-3 py-1.5 bg-orange-600 text-white rounded text-sm hover:bg-orange-700">
                    <i class="fas fa-pause mr-1"></i> Pause
                </button>
            </form>
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
        @foreach([
            ['Total', $campaign->total_contacts, 'fas fa-users', 'gray'],
            ['Sent', $campaign->sent_count, 'fas fa-check', 'blue'],
            ['Delivered', $campaign->delivered_count, 'fas fa-check-double', 'green'],
            ['Read', $campaign->read_count, 'fas fa-eye', 'purple'],
            ['Failed', $campaign->failed_count, 'fas fa-times', 'red'],
            ['Cost', '₹' . number_format($campaign->total_cost, 2), 'fas fa-rupee-sign', 'emerald'],
        ] as [$label, $value, $icon, $color])
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <i class="{{ $icon }} text-{{ $color }}-500 text-lg mb-1"></i>
                <p class="text-lg font-bold text-gray-900">{{ is_numeric($value) ? number_format($value) : $value }}</p>
                <p class="text-xs text-gray-500">{{ $label }}</p>
            </div>
        @endforeach
    </div>

    {{-- Delivery Rates --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Campaign Details</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between py-1 border-b">
                    <span class="text-gray-500">Status</span>
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                        {{ $campaign->status === 'completed' ? 'bg-green-100 text-green-700' :
                           ($campaign->status === 'sending' ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-700') }}">
                        {{ ucfirst($campaign->status) }}
                    </span>
                </div>
                <div class="flex justify-between py-1 border-b">
                    <span class="text-gray-500">Template</span>
                    <span class="font-medium">{{ $campaign->template?->name ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b">
                    <span class="text-gray-500">WhatsApp Account</span>
                    <span>{{ $campaign->whatsappAccount?->display_name ?? $campaign->whatsappAccount?->phone_number ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b">
                    <span class="text-gray-500">Created</span>
                    <span>{{ $campaign->created_at->format('M d, Y h:i A') }}</span>
                </div>
                @if($campaign->started_at)
                    <div class="flex justify-between py-1 border-b">
                        <span class="text-gray-500">Started</span>
                        <span>{{ $campaign->started_at->format('M d, Y h:i A') }}</span>
                    </div>
                @endif
                @if($campaign->completed_at)
                    <div class="flex justify-between py-1 border-b">
                        <span class="text-gray-500">Completed</span>
                        <span>{{ $campaign->completed_at->format('M d, Y h:i A') }}</span>
                    </div>
                @endif
                <div class="flex justify-between py-1 border-b">
                    <span class="text-gray-500">Delivery Rate</span>
                    <span class="font-medium text-green-600">{{ $campaign->getDeliveryRate() }}%</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-gray-500">Read Rate</span>
                    <span class="font-medium text-blue-600">{{ $campaign->getReadRate() }}%</span>
                </div>
            </div>

            {{-- Actions --}}
            <div class="mt-4 flex gap-2 flex-wrap">
                @if($campaign->status === 'paused')
                    <form method="POST" action="{{ route('campaigns.resume', $campaign) }}">@csrf
                        <button class="px-3 py-1.5 bg-green-600 text-white rounded text-sm">
                            <i class="fas fa-play mr-1"></i> Resume
                        </button>
                    </form>
                @endif
                @if(!in_array($campaign->status, ['completed', 'cancelled']))
                    <form method="POST" action="{{ route('campaigns.cancel', $campaign) }}" onsubmit="return confirm('Cancel this campaign?')">@csrf
                        <button class="px-3 py-1.5 border border-red-300 text-red-600 rounded text-sm">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </button>
                    </form>
                @endif
                <form method="POST" action="{{ route('campaigns.duplicate', $campaign) }}">@csrf
                    <button class="px-3 py-1.5 border border-gray-300 rounded text-sm">
                        <i class="fas fa-copy mr-1"></i> Duplicate
                    </button>
                </form>
            </div>
        </div>

        {{-- Status Breakdown Chart --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Delivery Breakdown</h3>
            @if($campaign->total_contacts > 0)
                <div class="space-y-4">
                    @foreach([
                        ['Sent', $statusBreakdown['sent'] ?? 0, 'bg-blue-500'],
                        ['Delivered', $statusBreakdown['delivered'] ?? 0, 'bg-green-500'],
                        ['Read', $statusBreakdown['read'] ?? 0, 'bg-purple-500'],
                        ['Replied', $statusBreakdown['replied'] ?? 0, 'bg-indigo-500'],
                        ['Failed', $statusBreakdown['failed'] ?? 0, 'bg-red-500'],
                        ['Pending', $statusBreakdown['pending'] ?? 0, 'bg-gray-300'],
                    ] as [$label, $count, $barColor])
                        @php $pct = $campaign->total_contacts > 0 ? ($count / $campaign->total_contacts) * 100 : 0; @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600">{{ $label }}</span>
                                <span class="font-medium">{{ number_format($count) }} ({{ number_format($pct, 1) }}%)</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="{{ $barColor }} h-2 rounded-full" style="width: {{ min(100, $pct) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-500 py-8">No data</p>
            @endif
        </div>
    </div>

    {{-- Contact Results --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold">Contact Results</h3>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Error</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Updated</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($campaignContacts as $cc)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-sm">{{ $cc->contact?->name ?? 'Unknown' }}</td>
                        <td class="px-6 py-3 text-sm font-mono text-gray-500">{{ $cc->contact?->phone }}</td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                {{ $cc->status === 'delivered' ? 'bg-green-100 text-green-700' :
                                   ($cc->status === 'read' ? 'bg-blue-100 text-blue-700' :
                                   ($cc->status === 'sent' ? 'bg-yellow-100 text-yellow-700' :
                                   ($cc->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700'))) }}">
                                {{ ucfirst($cc->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-xs text-red-500">{{ $cc->error_message }}</td>
                        <td class="px-6 py-3 text-xs text-gray-500">{{ $cc->updated_at->diffForHumans() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4">{{ $campaignContacts->links() }}</div>
    </div>
</div>
@endsection