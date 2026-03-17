@extends('layouts.app')
@section('title', 'Manage Plans')
@section('page-title', 'Plans Management')

@section('content')
<div class="space-y-6">
    <div class="flex justify-end">
        <a href="{{ route('admin.plans.create') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-md text-sm hover:bg-emerald-700">
            <i class="fas fa-plus mr-1"></i> Create Plan
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Price</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">WA Numbers</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Automations</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Agents</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Subscribers</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Active</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($plans as $plan)
                    <tr>
                        <td class="px-6 py-3 text-sm font-medium">{{ $plan->name }} <span class="text-xs text-gray-400">({{ $plan->slug }})</span></td>
                        <td class="px-6 py-3 text-sm text-right font-bold">₹{{ number_format($plan->price) }}/{{ $plan->billing_cycle === 'yearly' ? 'yr' : 'mo' }}</td>
                        <td class="px-6 py-3 text-sm text-center">{{ $plan->whatsapp_numbers == -1 ? '∞' : $plan->whatsapp_numbers }}</td>
                        <td class="px-6 py-3 text-sm text-center">{{ $plan->automation_flows == -1 ? '∞' : $plan->automation_flows }}</td>
                        <td class="px-6 py-3 text-sm text-center">{{ $plan->agents == -1 ? '∞' : $plan->agents }}</td>
                        <td class="px-6 py-3 text-sm text-center font-medium">{{ $plan->subscriptions_count }}</td>
                        <td class="px-6 py-3 text-center">
                            <span class="h-3 w-3 rounded-full inline-block {{ $plan->is_active ? 'bg-green-500' : 'bg-gray-300' }}"></span>
                        </td>
                        <td class="px-6 py-3 text-right">
                            <a href="{{ route('admin.plans.edit', $plan) }}" class="text-gray-400 hover:text-blue-600 p-1"><i class="fas fa-edit"></i></a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection