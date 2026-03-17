<div class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
    <button type="button" class="-m-2.5 p-2.5 text-gray-700 lg:hidden" @click="sidebarOpen = true">
        <i class="fas fa-bars"></i>
    </button>

    <div class="h-6 w-px bg-gray-200 lg:hidden"></div>

    <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
        <div class="flex flex-1 items-center">
            <h1 class="text-lg font-semibold text-gray-900">@yield('page-title', 'Dashboard')</h1>
        </div>

        <div class="flex items-center gap-x-4 lg:gap-x-6">

            {{-- Admin Badge --}}
            @if(auth()->user()->isSuperAdmin() && !session('admin_original_id'))
                <span class="hidden sm:inline-flex items-center rounded-full bg-red-100 px-3 py-0.5 text-xs font-medium text-red-700">
                    <i class="fas fa-shield-alt mr-1"></i> Super Admin
                </span>
            @endif

            {{-- Viewing-as Badge --}}
            @if(session('admin_original_id'))
                <span class="inline-flex items-center rounded-full bg-yellow-100 px-3 py-0.5 text-xs font-medium text-yellow-700">
                    <i class="fas fa-eye mr-1"></i> Viewing as {{ auth()->user()->name }}
                </span>
            @endif

            {{-- Notifications --}}
            <a href="{{ route('notifications.index') }}" class="relative -m-2.5 p-2.5 text-gray-400 hover:text-gray-500">
                <i class="fas fa-bell text-lg"></i>
                @php $unreadCount = auth()->user()->notifications()->where('is_read', false)->count(); @endphp
                @if($unreadCount > 0)
                    <span class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-600 rounded-full">
                        {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                    </span>
                @endif
            </a>

            <div class="hidden lg:block lg:h-6 lg:w-px lg:bg-gray-200"></div>

            {{-- Profile dropdown --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="flex items-center gap-x-2 -m-1.5 p-1.5">
                    <div class="h-8 w-8 rounded-full bg-emerald-600 flex items-center justify-center text-white font-bold text-sm">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <span class="hidden lg:flex lg:items-center">
                        <span class="text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</span>
                        <i class="fas fa-chevron-down ml-2 text-xs text-gray-400"></i>
                    </span>
                </button>

                <div x-show="open" @click.away="open = false" x-cloak
                     class="absolute right-0 z-10 mt-2.5 w-56 origin-top-right rounded-md bg-white py-2 shadow-lg ring-1 ring-gray-900/5">
                    <div class="px-3 py-2 border-b">
                        <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium mt-1
                            {{ auth()->user()->isSuperAdmin() ? 'bg-red-100 text-red-700' : (auth()->user()->isAgent() ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700') }}">
                            {{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}
                        </span>
                    </div>
                    <a href="{{ route('account.edit') }}" class="block px-3 py-1.5 text-sm text-gray-900 hover:bg-gray-50">
                        <i class="fas fa-user mr-2 text-gray-400"></i> Account Settings
                    </a>
                    @if(auth()->user()->isBusinessOwner() || session('admin_original_id'))
                        <a href="{{ route('business.edit') }}" class="block px-3 py-1.5 text-sm text-gray-900 hover:bg-gray-50">
                            <i class="fas fa-building mr-2 text-gray-400"></i> Business Profile
                        </a>
                    @endif
                    @if(auth()->user()->isSuperAdmin() && !session('admin_original_id'))
                        <a href="{{ route('admin.settings.index') }}" class="block px-3 py-1.5 text-sm text-gray-900 hover:bg-gray-50">
                            <i class="fas fa-cogs mr-2 text-gray-400"></i> Platform Settings
                        </a>
                    @endif
                    <hr class="my-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left px-3 py-1.5 text-sm text-gray-900 hover:bg-gray-50">
                            <i class="fas fa-sign-out-alt mr-2 text-gray-400"></i> Sign out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>