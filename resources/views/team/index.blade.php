@extends('layouts.app')
@section('title', 'Team')
@section('page-title', 'Team Management')

@section('content')
<div class="space-y-6">
    {{-- Add Member Form --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Add Team Member</h3>
        <form method="POST" action="{{ route('team.store') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            @csrf
            <div>
                <input type="text" name="name" placeholder="Name" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-3 py-2 border">
            </div>
            <div>
                <input type="email" name="email" placeholder="Email" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-3 py-2 border">
            </div>
            <div>
                <select name="role" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-3 py-2 border">
                    <option value="agent">Agent</option>
                    <option value="admin">Admin</option>
                    <option value="viewer">Viewer</option>
                </select>
            </div>
            <div>
                <button type="submit" class="w-full px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700">
                    <i class="fas fa-plus mr-1"></i> Add Member
                </button>
            </div>
        </form>
    </div>

    {{-- Members List --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Member</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Added</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($members as $member)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-full bg-emerald-100 flex items-center justify-center text-sm font-bold text-emerald-700">
                                    {{ strtoupper(substr($member->member->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $member->member->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $member->member->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 capitalize">{{ $member->role }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $member->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ ucfirst($member->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $member->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4 text-right">
                            <form method="POST" action="{{ route('team.destroy', $member) }}" onsubmit="return confirm('Remove this member?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            No team members yet. Add your first agent above.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection