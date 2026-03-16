<div class="flex grow flex-col gap-y-5 overflow-y-auto bg-emerald-700 px-6 pb-4">
    <div class="flex h-16 shrink-0 items-center">
        <span class="text-2xl font-bold text-white">
            <i class="fab fa-whatsapp mr-2"></i>Whatify
        </span>
    </div>

    {{-- Wallet Balance --}}
    @if(auth()->user()->wallet)
        <div class="rounded-lg bg-emerald-800 p-3">
            <div class="text-xs text-emerald-200">Wallet Balance</div>
            <div class="text-lg font-bold text-white">₹{{ number_format(auth()->user()->wallet->balance, 2) }}</div>
            <a href="{{ route('wallet.recharge') }}" class="text-xs text-emerald-300 hover:text-white">
                <i class="fas fa-plus-circle"></i> Recharge
            </a>
        </div>
    @endif

    <nav class="flex flex-1 flex-col">
        <ul role="list" class="flex flex-1 flex-col gap-y-7">
            <li>
                <ul role="list" class="-mx-2 space-y-1">
                    @php
                        $menuItems = [
                            ['route' => 'dashboard', 'icon' => 'fas fa-home', 'label' => 'Dashboard'],
                        ];

                        if (auth()->user()->isBusinessOwner() || auth()->user()->isSuperAdmin()) {
                            $menuItems = array_merge($menuItems, [
                                ['route' => 'wallet.index', 'icon' => 'fas fa-wallet', 'label' => 'Wallet'],
                                ['route' => 'team.index', 'icon' => 'fas fa-users', 'label' => 'Team'],
                                ['route' => 'billing.plans', 'icon' => 'fas fa-credit-card', 'label' => 'Billing'],
                            ]);
                        }

                        $menuItems = array_merge($menuItems, [
                            ['route' => 'notifications.index', 'icon' => 'fas fa-bell', 'label' => 'Notifications'],
                            ['route' => 'business.edit', 'icon' => 'fas fa-building', 'label' => 'Business Profile'],
                            ['route' => 'account.edit', 'icon' => 'fas fa-cog', 'label' => 'Settings'],
                        ]);
                    @endphp

                    @foreach($menuItems as $item)
                        <li>
                            <a href="{{ route($item['route']) }}"
                               class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold
                                      {{ request()->routeIs($item['route'].'*') ? 'bg-emerald-800 text-white' : 'text-emerald-200 hover:text-white hover:bg-emerald-800' }}">
                                <i class="{{ $item['icon'] }} w-5 text-center"></i>
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </li>

            {{-- Upcoming modules placeholder --}}
            <li>
                <div class="text-xs font-semibold leading-6 text-emerald-300 uppercase">Coming Soon</div>
                <ul role="list" class="-mx-2 mt-2 space-y-1">
                    @foreach(['Inbox', 'Contacts', 'Campaigns', 'Automations', 'Integrations'] as $module)
                        <li>
                            <span class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 text-emerald-400 opacity-50">
                                <i class="fas fa-lock w-5 text-center"></i>
                                {{ $module }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </li>

            {{-- Logout --}}
            <li class="mt-auto">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="group flex w-full gap-x-3 rounded-md p-2 text-sm font-semibold text-emerald-200 hover:bg-emerald-800 hover:text-white">
                        <i class="fas fa-sign-out-alt w-5 text-center"></i>
                        Logout
                    </button>
                </form>
            </li>
        </ul>
    </nav>
</div>