<div class="flex grow flex-col gap-y-5 overflow-y-auto bg-emerald-700 px-6 pb-4">
    <div class="flex h-16 shrink-0 items-center">
        <a href="{{ auth()->user()->isSuperAdmin() ? route('admin.dashboard') : route('dashboard') }}" class="text-2xl font-bold text-white">
            <i class="fab fa-whatsapp mr-2"></i>Whatify
        </a>
    </div>

    {{-- Admin Switch Back Banner --}}
    @if(session('admin_original_id'))
        <div class="rounded-lg bg-yellow-400 p-2.5 text-center">
            <p class="text-xs text-yellow-900 font-medium mb-1">Viewing as: {{ auth()->user()->name }}</p>
            <a href="{{ route('admin.switchBack') }}" class="inline-flex items-center gap-1 text-xs font-bold text-yellow-900 bg-yellow-200 px-3 py-1 rounded-full hover:bg-yellow-100">
                <i class="fas fa-exchange-alt"></i> Switch Back to Admin
            </a>
        </div>
    @endif

    @php $role = auth()->user()->role; @endphp

    {{-- ======================== --}}
    {{-- SUPER ADMIN SIDEBAR --}}
    {{-- ======================== --}}
    @if($role === 'super_admin' && !session('admin_original_id'))
        <nav class="flex flex-1 flex-col">
            <ul role="list" class="flex flex-1 flex-col gap-y-7">
                <li>
                    <div class="text-xs font-semibold leading-6 text-emerald-300 uppercase tracking-wider">Platform Admin</div>
                    <ul role="list" class="-mx-2 mt-2 space-y-1">
                        @foreach([
                            ['admin.dashboard', 'fas fa-tachometer-alt', 'Dashboard'],
                            ['admin.users.index', 'fas fa-users', 'Users'],
                            ['admin.partners.index', 'fas fa-handshake', 'Partners'],
                            ['admin.payouts.index', 'fas fa-money-bill-wave', 'Payouts'],
                            ['admin.plans.index', 'fas fa-layer-group', 'Plans'],
                            ['admin.blog.index', 'fas fa-newspaper', 'Blog'],
                            ['admin.settings.index', 'fas fa-cogs', 'Settings'],
                        ] as [$route, $icon, $label])
                            <li>
                                <a href="{{ route($route) }}"
                                   class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold
                                          {{ request()->routeIs($route.'*') || request()->routeIs(str_replace('.index', '', $route).'.*') ? 'bg-emerald-800 text-white' : 'text-emerald-200 hover:text-white hover:bg-emerald-800' }}">
                                    <i class="{{ $icon }} w-5 text-center"></i>
                                    {{ $label }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>

                <li>
                    <div class="text-xs font-semibold leading-6 text-emerald-300 uppercase tracking-wider">Quick Links</div>
                    <ul role="list" class="-mx-2 mt-2 space-y-1">
                        <li>
                            <a href="{{ route('notifications.index') }}" class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 text-emerald-200 hover:text-white hover:bg-emerald-800">
                                <i class="fas fa-bell w-5 text-center"></i>
                                Notifications
                                @php $unread = auth()->user()->notifications()->where('is_read', false)->count(); @endphp
                                @if($unread > 0)
                                    <span class="ml-auto bg-red-500 text-white text-[10px] rounded-full px-1.5 py-0.5">{{ $unread }}</span>
                                @endif
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('account.edit') }}" class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 text-emerald-200 hover:text-white hover:bg-emerald-800">
                                <i class="fas fa-user-cog w-5 text-center"></i> My Account
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="mt-auto">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="group flex w-full gap-x-3 rounded-md p-2 text-sm font-semibold text-emerald-200 hover:bg-emerald-800 hover:text-white">
                            <i class="fas fa-sign-out-alt w-5 text-center"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </nav>

    {{-- ======================== --}}
    {{-- BUSINESS OWNER SIDEBAR --}}
    {{-- ======================== --}}
    @elseif($role === 'business_owner' || session('admin_original_id'))

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
                        @foreach([
                            ['dashboard', 'fas fa-home', 'Dashboard'],
                            ['inbox.index', 'fas fa-inbox', 'Inbox'],
                            ['contacts.index', 'fas fa-address-book', 'Contacts'],
                            ['campaigns.index', 'fas fa-bullhorn', 'Campaigns'],
                            ['automations.index', 'fas fa-robot', 'Automations'],
                            ['whatsapp.accounts.index', 'fab fa-whatsapp', 'WhatsApp'],
                            ['whatsapp.templates.index', 'fas fa-file-alt', 'Templates'],
                            ['integrations.index', 'fas fa-plug', 'Integrations'],
                            ['analytics.index', 'fas fa-chart-bar', 'Analytics'],
                        ] as [$route, $icon, $label])
                            <li>
                                <a href="{{ route($route) }}"
                                   class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold
                                          {{ request()->routeIs($route.'*') || request()->routeIs(str_replace('.index', '', $route).'.*') ? 'bg-emerald-800 text-white' : 'text-emerald-200 hover:text-white hover:bg-emerald-800' }}">
                                    <i class="{{ $icon }} w-5 text-center"></i>
                                    {{ $label }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>

                <li>
                    <div class="text-xs font-semibold leading-6 text-emerald-300 uppercase tracking-wider">Account</div>
                    <ul role="list" class="-mx-2 mt-2 space-y-1">
                        @foreach([
                            ['wallet.index', 'fas fa-wallet', 'Wallet'],
                            ['tags.index', 'fas fa-tags', 'Tags'],
                            ['team.index', 'fas fa-users', 'Team'],
                            ['billing.plans', 'fas fa-credit-card', 'Billing'],
                            ['notifications.index', 'fas fa-bell', 'Notifications'],
                            ['business.edit', 'fas fa-building', 'Business Profile'],
                            ['account.edit', 'fas fa-cog', 'Settings'],
                        ] as [$route, $icon, $label])
                            <li>
                                <a href="{{ route($route) }}"
                                   class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold
                                          {{ request()->routeIs($route.'*') ? 'bg-emerald-800 text-white' : 'text-emerald-200 hover:text-white hover:bg-emerald-800' }}">
                                    <i class="{{ $icon }} w-5 text-center"></i>
                                    {{ $label }}
                                </a>
                            </li>
                        @endforeach

                        {{-- Partner link --}}
                        @if(auth()->user()->partner)
                            <li>
                                <a href="{{ route('partner.dashboard') }}"
                                   class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold text-emerald-200 hover:text-white hover:bg-emerald-800">
                                    <i class="fas fa-handshake w-5 text-center"></i> Partner Panel
                                </a>
                            </li>
                        @else
                            <li>
                                <a href="{{ route('partner.apply') }}"
                                   class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold text-emerald-200 hover:text-white hover:bg-emerald-800">
                                    <i class="fas fa-handshake w-5 text-center"></i> Become Partner
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>

                <li class="mt-auto">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="group flex w-full gap-x-3 rounded-md p-2 text-sm font-semibold text-emerald-200 hover:bg-emerald-800 hover:text-white">
                            <i class="fas fa-sign-out-alt w-5 text-center"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </nav>

    {{-- ======================== --}}
    {{-- TEAM AGENT SIDEBAR --}}
    {{-- ======================== --}}
    @elseif($role === 'team_agent')
        <nav class="flex flex-1 flex-col">
            <ul role="list" class="flex flex-1 flex-col gap-y-7">
                <li>
                    <div class="text-xs font-semibold leading-6 text-emerald-300 uppercase tracking-wider">Agent Panel</div>
                    <ul role="list" class="-mx-2 mt-2 space-y-1">
                        @foreach([
                            ['dashboard', 'fas fa-home', 'Dashboard'],
                            ['inbox.index', 'fas fa-inbox', 'Inbox'],
                            ['contacts.index', 'fas fa-address-book', 'Contacts'],
                            ['notifications.index', 'fas fa-bell', 'Notifications'],
                            ['account.edit', 'fas fa-cog', 'Settings'],
                        ] as [$route, $icon, $label])
                            <li>
                                <a href="{{ route($route) }}"
                                   class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold
                                          {{ request()->routeIs($route.'*') ? 'bg-emerald-800 text-white' : 'text-emerald-200 hover:text-white hover:bg-emerald-800' }}">
                                    <i class="{{ $icon }} w-5 text-center"></i>
                                    {{ $label }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
                <li class="mt-auto">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="group flex w-full gap-x-3 rounded-md p-2 text-sm font-semibold text-emerald-200 hover:bg-emerald-800 hover:text-white">
                            <i class="fas fa-sign-out-alt w-5 text-center"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </nav>

    {{-- ======================== --}}
    {{-- PARTNER ONLY SIDEBAR --}}
    {{-- ======================== --}}
    @elseif($role === 'partner')
        <nav class="flex flex-1 flex-col">
            <ul role="list" class="flex flex-1 flex-col gap-y-7">
                <li>
                    <div class="text-xs font-semibold leading-6 text-emerald-300 uppercase tracking-wider">Partner Panel</div>
                    <ul role="list" class="-mx-2 mt-2 space-y-1">
                        @foreach([
                            ['partner.dashboard', 'fas fa-tachometer-alt', 'Dashboard'],
                            ['partner.payouts', 'fas fa-money-bill-wave', 'Payouts'],
                            ['partner.settings', 'fas fa-cog', 'Settings'],
                            ['notifications.index', 'fas fa-bell', 'Notifications'],
                            ['account.edit', 'fas fa-user-cog', 'Account'],
                        ] as [$route, $icon, $label])
                            <li>
                                <a href="{{ route($route) }}"
                                   class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold
                                          {{ request()->routeIs($route.'*') ? 'bg-emerald-800 text-white' : 'text-emerald-200 hover:text-white hover:bg-emerald-800' }}">
                                    <i class="{{ $icon }} w-5 text-center"></i>
                                    {{ $label }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
                <li class="mt-auto">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="group flex w-full gap-x-3 rounded-md p-2 text-sm font-semibold text-emerald-200 hover:bg-emerald-800 hover:text-white">
                            <i class="fas fa-sign-out-alt w-5 text-center"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </nav>
    @endif
</div>