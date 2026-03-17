@extends('layouts.app')
@section('title', 'Manage Users')
@section('page-title', 'Users Management')

@section('content')
<div class="space-y-6">
    <form method="GET" class="bg-white rounded-lg shadow p-4 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, phone..."
               class="flex-1 min-w-[200px] rounded-md border-gray-300 text-sm px-3 py-2 border">
        <select name="role" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm px-3 py-2 border">
            <option value="">All Roles</option>
            @foreach(['business_owner', 'team_agent', 'partner'] as $r)
                <option value="{{ $r }}" {{ request('role') === $r ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$r)) }}</option>
            @endforeach
        </select>
        <select name="status" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm px-3 py-2 border">
            <option value="">All Status</option>
            @foreach(['active', 'pending', 'suspended', 'inactive'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button class="px-4 py-2 bg-gray-100 rounded-md text-sm"><i class="fas fa-search"></i></button>
    </form>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Business</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Wallet</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3">
                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                            <div class="text-xs text-gray-500">{{ $user->email }} · {{ $user->phone }}</div>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-500">
                            {{ $user->business?->company_name ?? '-' }}
                            <div class="text-xs text-gray-400 capitalize">{{ $user->business?->industry ?? '' }}</div>
                        </td>
                        <td class="px-6 py-3 text-sm">
                            @if($user->subscription?->plan)
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700">
                                    {{ $user->subscription->plan->name }}
                                </span>
                            @else
                                <span class="text-gray-400 text-xs">No plan</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-sm text-right font-medium">₹{{ number_format($user->wallet?->balance ?? 0, 2) }}</td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                {{ $user->status === 'active' ? 'bg-green-100 text-green-700' :
                                   ($user->status === 'suspended' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700') }}">
                                {{ ucfirst($user->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-xs text-gray-500">{{ $user->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-3 text-right">
                            <div class="flex justify-end gap-1">
                                <a href="{{ route('admin.users.show', $user) }}" class="p-1 text-gray-400 hover:text-gray-600" title="View"><i class="fas fa-eye"></i></a>
                                <form method="POST" action="{{ route('admin.users.toggleStatus', $user) }}">@csrf
                                    <button class="p-1 text-gray-400 hover:text-yellow-600" title="{{ $user->status === 'active' ? 'Suspend' : 'Activate' }}">
                                        <i class="fas {{ $user->status === 'active' ? 'fa-ban' : 'fa-check-circle' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.users.loginAs', $user) }}">@csrf
                                    <button class="p-1 text-gray-400 hover:text-blue-600" title="Login As"><i class="fas fa-sign-in-alt"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div>{{ $users->links() }}</div>
</div>
@endsection