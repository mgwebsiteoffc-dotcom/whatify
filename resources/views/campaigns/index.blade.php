@extends('layouts.app')
@section('title', 'Campaigns')
@section('page-title', 'Campaigns')

@section('content')
<div class="space-y-6">
    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            ['Total', $stats['total'], 'fas fa-bullhorn', 'blue'],
            ['Active', $stats['active'], 'fas fa-play-circle', 'green'],
            ['Completed', $stats['completed'], 'fas fa-check-circle', 'emerald'],
            ['Draft', $stats['draft'], 'fas fa-pencil-alt', 'gray'],
        ] as [$label, $value, $icon, $color])
            <div class="bg-white rounded-lg shadow px-4 py-3 flex items-center gap-3">
                <div class="rounded-md bg-{{ $color }}-100 p-2"><i class="{{ $icon }} text-{{ $color }}-600"></i></div>
                <div>
                    <p class="text-xs text-gray-500">{{ $label }}</p>
                    <p class="text-lg font-bold">{{ $value }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row justify-between gap-4">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search campaigns..."
                   class="rounded-md border-gray-300 text-sm px-3 py-2 border">
            <select name="status" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm px-3 py-2 border">
                <option value="">All Status</option>
                @foreach(['draft', 'scheduled', 'sending', 'completed', 'paused', 'failed', 'cancelled'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </form>
        <a href="{{ route('campaigns.create') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-md text-sm font-medium hover:bg-emerald-700">
            <i class="fas fa-plus mr-2"></i>New Campaign
        </a>
    </div>

    {{-- Campaigns List --}}
    @if($campaigns->isEmpty())
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <i class="fas fa-bullhorn text-5xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-semibold mb-2">No Campaigns Yet</h3>
            <p class="text-gray-500 mb-6">Create your first broadcast campaign to reach your customers.</p>
            <a href="{{ route('campaigns.create') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-md text-sm">
                <i class="fas fa-plus mr-1"></i> Create Campaign
            </a>
        </div>
    @else
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Campaign</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Contacts</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Sent</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Delivered</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Read</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Cost</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($campaigns as $campaign)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <a href="{{ route('campaigns.show', $campaign) }}" class="text-sm font-medium text-gray-900 hover:text-emerald-600">
                                    {{ $campaign->name }}
                                </a>
                                <p class="text-xs text-gray-500">
                                    {{ $campaign->template?->name ?? 'N/A' }} · {{ $campaign->created_at->format('M d, h:i A') }}
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'draft' => 'bg-gray-100 text-gray-700',
                                        'scheduled' => 'bg-blue-100 text-blue-700',
                                        'processing' => 'bg-yellow-100 text-yellow-700',
                                        'sending' => 'bg-orange-100 text-orange-700',
                                        'completed' => 'bg-green-100 text-green-700',
                                        'paused' => 'bg-purple-100 text-purple-700',
                                        'failed' => 'bg-red-100 text-red-700',
                                        'cancelled' => 'bg-gray-100 text-gray-500',
                                    ];
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $statusColors[$campaign->status] ?? 'bg-gray-100 text-gray-700' }}">
                                    @if($campaign->status === 'sending')
                                        <span class="mr-1 h-1.5 w-1.5 rounded-full bg-orange-500 animate-pulse"></span>
                                    @endif
                                    {{ ucfirst($campaign->status) }}
                                </span>
                                @if($campaign->scheduled_at && $campaign->status === 'scheduled')
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $campaign->scheduled_at->format('M d, h:i A') }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center text-sm">{{ number_format($campaign->total_contacts) }}</td>
                            <td class="px-6 py-4 text-center text-sm text-green-600 font-medium">{{ number_format($campaign->sent_count) }}</td>
                            <td class="px-6 py-4 text-center text-sm">{{ number_format($campaign->delivered_count) }}</td>
                            <td class="px-6 py-4 text-center text-sm text-blue-600">{{ number_format($campaign->read_count) }}</td>
                            <td class="px-6 py-4 text-right text-sm font-medium">₹{{ number_format($campaign->total_cost, 2) }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('campaigns.show', $campaign) }}" class="text-gray-400 hover:text-gray-600 p-1" title="View"><i class="fas fa-eye"></i></a>
                                    @if($campaign->status === 'sending')
                                        <form method="POST" action="{{ route('campaigns.pause', $campaign) }}">@csrf
                                            <button class="text-gray-400 hover:text-yellow-600 p-1" title="Pause"><i class="fas fa-pause"></i></button>
                                        </form>
                                    @endif
                                    @if($campaign->status === 'paused')
                                        <form method="POST" action="{{ route('campaigns.resume', $campaign) }}">@csrf
                                            <button class="text-gray-400 hover:text-green-600 p-1" title="Resume"><i class="fas fa-play"></i></button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('campaigns.duplicate', $campaign) }}">@csrf
                                        <button class="text-gray-400 hover:text-blue-600 p-1" title="Duplicate"><i class="fas fa-copy"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div>{{ $campaigns->links() }}</div>
    @endif
</div>
@endsection