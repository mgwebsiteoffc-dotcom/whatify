@extends('layouts.app')
@section('title', 'Automations')
@section('page-title', 'Automations')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            ['Total', $stats['total'], 'fas fa-robot', 'blue'],
            ['Active', $stats['active'], 'fas fa-play-circle', 'green'],
            ['Draft', $stats['draft'], 'fas fa-pencil-alt', 'gray'],
            ['Executions', number_format($stats['total_executions']), 'fas fa-bolt', 'purple'],
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

    <div class="flex justify-between items-center">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="rounded-md border-gray-300 text-sm px-3 py-2 border">
            <select name="status" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm px-3 py-2 border">
                <option value="">All Status</option>
                @foreach(['active', 'inactive', 'draft'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <select name="trigger" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm px-3 py-2 border">
                <option value="">All Triggers</option>
                @foreach(['keyword', 'incoming_message', 'contact_created', 'tag_added', 'shopify_order', 'campaign_reply', 'api_trigger'] as $t)
                    <option value="{{ $t }}" {{ request('trigger') === $t ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $t)) }}</option>
                @endforeach
            </select>
        </form>
        <a href="{{ route('automations.create') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-md text-sm font-medium hover:bg-emerald-700">
            <i class="fas fa-plus mr-2"></i>New Automation
        </a>
    </div>

    @if($automations->isEmpty())
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <i class="fas fa-robot text-5xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-semibold mb-2">No Automations Yet</h3>
            <p class="text-gray-500 mb-6">Create chatbot flows and automated workflows.</p>
            <a href="{{ route('automations.create') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-md text-sm">
                <i class="fas fa-plus mr-1"></i> Create Automation
            </a>
        </div>
    @else
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Automation</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trigger</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Steps</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Executions</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($automations as $auto)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <a href="{{ route('automations.show', $auto) }}" class="text-sm font-medium text-gray-900 hover:text-emerald-600">{{ $auto->name }}</a>
                                @if($auto->description)
                                    <p class="text-xs text-gray-500 mt-0.5">{{ Str::limit($auto->description, 60) }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700">
                                    {{ ucfirst(str_replace('_', ' ', $auto->trigger_type)) }}
                                </span>
                                @if($auto->trigger_type === 'keyword' && ($auto->trigger_config['keywords'] ?? []))
                                    <p class="text-[10px] text-gray-400 mt-0.5">{{ implode(', ', array_slice($auto->trigger_config['keywords'], 0, 3)) }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center text-sm">{{ $auto->steps_count }}</td>
                            <td class="px-6 py-4 text-center text-sm font-medium">{{ number_format($auto->execution_count) }}</td>
                            <td class="px-6 py-4 text-center">
                                <form method="POST" action="{{ route('automations.toggle', $auto) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium cursor-pointer
                                        {{ $auto->status === 'active' ? 'bg-green-100 text-green-700 hover:bg-green-200' :
                                           ($auto->status === 'draft' ? 'bg-gray-100 text-gray-600 hover:bg-gray-200' : 'bg-red-100 text-red-700 hover:bg-red-200') }}">
                                        <span class="mr-1 h-1.5 w-1.5 rounded-full {{ $auto->status === 'active' ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                        {{ ucfirst($auto->status) }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('automations.builder', $auto) }}" class="text-gray-400 hover:text-blue-600 p-1" title="Edit Flow"><i class="fas fa-project-diagram"></i></a>
                                    <a href="{{ route('automations.show', $auto) }}" class="text-gray-400 hover:text-gray-600 p-1" title="View"><i class="fas fa-eye"></i></a>
                                    <form method="POST" action="{{ route('automations.duplicate', $auto) }}">@csrf
                                        <button class="text-gray-400 hover:text-green-600 p-1" title="Duplicate"><i class="fas fa-copy"></i></button>
                                    </form>
                                    <form method="POST" action="{{ route('automations.destroy', $auto) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')
                                        <button class="text-gray-400 hover:text-red-600 p-1" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div>{{ $automations->links() }}</div>
    @endif
</div>
@endsection