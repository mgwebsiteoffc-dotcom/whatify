@extends('layouts.app')
@section('title', 'User: ' . $user->name)
@section('page-title')
    <a href="{{ route('admin.users.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left mr-2"></i></a>
    {{ $user->name }}
@endsection

@section('content')
<div class="space-y-6">
    {{-- User Info + Actions --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-4">
                <div class="h-16 w-16 rounded-full bg-emerald-100 flex items-center justify-center text-2xl font-bold text-emerald-700">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold">{{ $user->name }}</h2>
                    <p class="text-gray-500">{{ $user->email }} · {{ $user->phone }}</p>
                    <p class="text-xs text-gray-400">Joined {{ $user->created_at->format('M d, Y') }} · Last login {{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                <form method="POST" action="{{ route('admin.users.toggleStatus', $user) }}">@csrf
                    <button class="px-4 py-2 border rounded-md text-sm {{ $user->status === 'active' ? 'border-red-300 text-red-600 hover:bg-red-50' : 'border-green-300 text-green-600 hover:bg-green-50' }}">
                        <i class="fas {{ $user->status === 'active' ? 'fa-ban' : 'fa-check' }} mr-1"></i>
                        {{ $user->status === 'active' ? 'Suspend' : 'Activate' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.users.loginAs', $user) }}">@csrf
                    <button class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">
                        <i class="fas fa-sign-in-alt mr-1"></i> Login As
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        @foreach([
            ['Wallet', '₹'.number_format($user->wallet?->balance ?? 0, 2), 'fas fa-wallet', 'emerald'],
            ['Contacts', number_format($user->contacts->count()), 'fas fa-address-book', 'blue'],
            ['Messages', number_format($messageStats['total']), 'fas fa-comment', 'purple'],
            ['Campaigns', number_format($user->campaigns->count()), 'fas fa-bullhorn', 'orange'],
            ['Automations', number_format($user->automations->count()), 'fas fa-robot', 'indigo'],
        ] as [$label, $value, $icon, $color])
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <i class="{{ $icon }} text-{{ $color }}-500 text-lg mb-1"></i>
                <p class="text-lg font-bold">{{ $value }}</p>
                <p class="text-xs text-gray-500">{{ $label }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Details --}}
        <div class="bg-white rounded-lg shadow p-6 space-y-3 text-sm">
            <h3 class="text-lg font-semibold mb-2">Account Details</h3>
            @foreach([
                ['Status', ucfirst($user->status)],
                ['Role', ucfirst(str_replace('_',' ',$user->role))],
                ['Business', $user->business?->company_name ?? '-'],
                ['Industry', ucfirst($user->business?->industry ?? '-')],
                ['Plan', $user->getActiveSubscription()?->plan?->name ?? 'None'],
                ['Subscription Status', ucfirst($user->getActiveSubscription()?->status ?? 'None')],
                ['WA Accounts', $user->whatsappAccounts->count()],
                ['Partner Ref', $user->partner_id ? "User #{$user->partner_id}" : 'Direct'],
                ['Total Recharged', '₹'.number_format($user->wallet?->total_recharged ?? 0, 2)],
                ['Total Spent', '₹'.number_format($user->wallet?->total_spent ?? 0, 2)],
            ] as [$k, $v])
                <div class="flex justify-between border-b py-1">
                    <span class="text-gray-500">{{ $k }}</span>
                    <span class="font-medium">{{ $v }}</span>
                </div>
            @endforeach
        </div>

        {{-- Add Credits --}}
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Add Wallet Credits</h3>
                <form method="POST" action="{{ route('admin.users.addCredits', $user) }}" class="space-y-3">
                    @csrf
                    <input type="number" name="amount" required min="1" max="100000" placeholder="Amount (₹)"
                           class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    <input type="text" name="description" required placeholder="Reason (e.g. Bonus, Refund, Promo)"
                           class="w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    <button class="px-4 py-2 bg-emerald-600 text-white rounded-md text-sm hover:bg-emerald-700 w-full">
                        <i class="fas fa-plus mr-1"></i> Add Credits
                    </button>
                </form>
            </div>

            {{-- Recent Transactions --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Recent Transactions</h3>
                <div class="space-y-2 max-h-64 overflow-y-auto">
                    @forelse($walletTransactions as $txn)
                        <div class="flex justify-between items-center text-sm border-b pb-2">
                            <div>
                                <p class="text-gray-800">{{ $txn->description }}</p>
                                <p class="text-xs text-gray-400">{{ $txn->created_at->format('M d, h:i A') }}</p>
                            </div>
                            <span class="font-medium {{ in_array($txn->type, ['credit','refund','bonus']) ? 'text-green-600' : 'text-red-600' }}">
                                {{ in_array($txn->type, ['credit','refund','bonus']) ? '+' : '-' }}₹{{ number_format($txn->amount, 2) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-gray-400 text-sm">No transactions</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection