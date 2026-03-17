@extends('layouts.app')
@section('title', $automation->name)
@section('page-title')
    <a href="{{ route('automations.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left mr-2"></i></a>
    {{ $automation->name }}
@endsection

@section('content')
<div class="space-y-6">
    {{-- Status Bar --}}
    <div class="bg-white rounded-lg shadow p-4 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <form method="POST" action="{{ route('automations.toggle', $automation) }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium
                    {{ $automation->status === 'active' ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    <span class="h-2.5 w-2.5 rounded-full {{ $automation->status === 'active' ? 'bg-green-500 animate-pulse' : 'bg-gray-400' }}"></span>
                    {{ $automation->status === 'active' ? 'Active' : ($automation->status === 'draft' ? 'Draft' : 'Inactive') }}
                    <span class="text-xs opacity-60">(click to toggle)</span>
                </button>
            </form>
            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-blue-100 text-blue-700">
                <i class="fas fa-bolt mr-1"></i>{{ ucfirst(str_replace('_', ' ', $automation->trigger_type)) }}
            </span>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('automations.builder', $automation) }}" class="px-4 py-2 bg-emerald-600 text-white rounded-md text-sm hover:bg-emerald-700">
                <i class="fas fa-project-diagram mr-1"></i> Edit Flow
            </a>
            <form method="POST" action="{{ route('automations.duplicate', $automation) }}">
                @csrf
                <button class="px-3 py-2 border rounded-md text-sm hover:bg-gray-50"><i class="fas fa-copy mr-1"></i> Duplicate</button>
            </form>
            <form method="POST" action="{{ route('automations.destroy', $automation) }}" onsubmit="return confirm('Delete this automation?')">
                @csrf @method('DELETE')
                <button class="px-3 py-2 border border-red-300 text-red-600 rounded-md text-sm hover:bg-red-50"><i class="fas fa-trash mr-1"></i> Delete</button>
            </form>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            ['Total Runs', $logStats['total'], 'fas fa-play', 'blue'],
            ['Completed', $logStats['completed'], 'fas fa-check-circle', 'green'],
            ['Failed', $logStats['failed'], 'fas fa-times-circle', 'red'],
            ['Running', $logStats['running'], 'fas fa-spinner', 'yellow'],
        ] as [$label, $value, $icon, $color])
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <i class="{{ $icon }} text-{{ $color }}-500 text-lg mb-1"></i>
                <p class="text-xl font-bold text-gray-900">{{ number_format($value) }}</p>
                <p class="text-xs text-gray-500">{{ $label }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Details --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Automation Details</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between py-1 border-b">
                    <span class="text-gray-500">Name</span>
                    <span class="font-medium">{{ $automation->name }}</span>
                </div>
                @if($automation->description)
                    <div class="flex justify-between py-1 border-b">
                        <span class="text-gray-500">Description</span>
                        <span>{{ $automation->description }}</span>
                    </div>
                @endif
                <div class="flex justify-between py-1 border-b">
                    <span class="text-gray-500">Trigger</span>
                    <span class="capitalize">{{ str_replace('_', ' ', $automation->trigger_type) }}</span>
                </div>
                @if($automation->trigger_type === 'keyword' && ($automation->trigger_config['keywords'] ?? []))
                    <div class="flex justify-between py-1 border-b">
                        <span class="text-gray-500">Keywords</span>
                        <div class="flex flex-wrap gap-1 justify-end">
                            @foreach($automation->trigger_config['keywords'] as $kw)
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs bg-gray-100 text-gray-700">{{ $kw }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex justify-between py-1 border-b">
                        <span class="text-gray-500">Match Type</span>
                        <span class="capitalize">{{ $automation->trigger_config['match_type'] ?? 'contains' }}</span>
                    </div>
                @endif
                <div class="flex justify-between py-1 border-b">
                    <span class="text-gray-500">Steps</span>
                    <span class="font-medium">{{ $automation->steps->count() }}</span>
                </div>
                <div class="flex justify-between py-1 border-b">
                    <span class="text-gray-500">Total Executions</span>
                    <span class="font-medium">{{ number_format($automation->execution_count) }}</span>
                </div>
                <div class="flex justify-between py-1 border-b">
                    <span class="text-gray-500">Created</span>
                    <span>{{ $automation->created_at->format('M d, Y h:i A') }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-gray-500">Last Updated</span>
                    <span>{{ $automation->updated_at->format('M d, Y h:i A') }}</span>
                </div>
            </div>
        </div>

        {{-- Flow Steps Summary --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Flow Steps</h3>
            @if($automation->steps->isEmpty())
                <div class="text-center py-8 text-gray-400">
                    <i class="fas fa-project-diagram text-3xl mb-2"></i>
                    <p>No steps configured</p>
                    <a href="{{ route('automations.builder', $automation) }}" class="text-emerald-600 text-sm hover:underline mt-2 inline-block">Build Flow →</a>
                </div>
            @else
                <div class="space-y-2">
                    @foreach($automation->steps as $index => $step)
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                            <div class="h-8 w-8 rounded-full bg-emerald-100 flex items-center justify-center text-xs font-bold text-emerald-700 flex-shrink-0">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 capitalize">
                                    @php
                                        $stepIcons = [
                                            'send_message' => '💬',
                                            'send_template' => '📋',
                                            'send_media' => '📎',
                                            'ask_question' => '❓',
                                            'buttons' => '🔘',
                                            'list_menu' => '📜',
                                            'condition' => '🔀',
                                            'delay' => '⏳',
                                            'add_tag' => '🏷️',
                                            'remove_tag' => '🗑️',
                                            'assign_agent' => '👤',
                                            'transfer_to_agent' => '🤝',
                                            'api_call' => '🔌',
                                            'webhook' => '🪝',
                                            'set_variable' => '📝',
                                            'update_contact' => '✏️',
                                            'goto_step' => '↩️',
                                            'end_flow' => '🛑',
                                        ];
                                    @endphp
                                    {{ $stepIcons[$step->type] ?? '⚙️' }}
                                    {{ str_replace('_', ' ', $step->type) }}
                                </p>
                                @if($step->type === 'send_message' && ($step->config['message'] ?? null))
                                    <p class="text-xs text-gray-500 truncate">{{ Str::limit($step->config['message'], 50) }}</p>
                                @elseif($step->type === 'delay')
                                    <p class="text-xs text-gray-500">{{ $step->config['value'] ?? 0 }} {{ $step->config['unit'] ?? 'seconds' }}</p>
                                @elseif($step->type === 'condition')
                                    <p class="text-xs text-gray-500">{{ count($step->branches ?? []) }} branches</p>
                                @endif
                            </div>
                            @if($step->next_step_id)
                                <i class="fas fa-arrow-right text-gray-300 text-xs"></i>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Recent Execution Logs --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-semibold">Recent Execution Logs</h3>
        </div>
        @if($recentLogs->isEmpty())
            <div class="p-8 text-center text-gray-500">
                <p>No executions yet</p>
            </div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Steps Run</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Started</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Error</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($recentLogs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm">
                                <a href="{{ route('contacts.show', $log->contact_id) }}" class="text-emerald-600 hover:underline">
                                    {{ $log->contact?->name ?? $log->contact?->phone ?? 'Unknown' }}
                                </a>
                            </td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                    {{ $log->status === 'completed' ? 'bg-green-100 text-green-700' :
                                       ($log->status === 'running' ? 'bg-blue-100 text-blue-700' :
                                       ($log->status === 'paused' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700')) }}">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-600">
                                {{ count($log->execution_path ?? []) }} steps
                            </td>
                            <td class="px-6 py-3 text-xs text-gray-500">
                                {{ $log->started_at?->format('M d, h:i A') }}
                            </td>
                            <td class="px-6 py-3 text-xs text-gray-500">
                                @if($log->started_at && $log->completed_at)
                                    {{ $log->started_at->diffForHumans($log->completed_at, true) }}
                                @elseif($log->status === 'running')
                                    <span class="text-blue-500">In progress...</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-3 text-xs text-red-500">
                                {{ $log->error_message ? Str::limit($log->error_message, 40) : '' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection