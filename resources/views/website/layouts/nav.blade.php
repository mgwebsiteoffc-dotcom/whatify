<header class="bg-white border-b sticky top-0 z-50" x-data="{ mobileOpen: false }">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <a href="{{ route('website.home') }}" class="flex items-center gap-2">
                    <i class="fab fa-whatsapp text-3xl text-emerald-600"></i>
                    <span class="text-xl font-bold text-gray-900">Whatify</span>
                </a>
            </div>

            <div class="hidden md:flex items-center gap-1">
                <a href="{{ route('website.home') }}"
                   class="px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('website.home') ? 'text-emerald-600 bg-emerald-50' : 'text-gray-700 hover:text-emerald-600 hover:bg-gray-50' }}">
                    Home
                </a>
                <a href="{{ route('website.features') }}"
                   class="px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('website.features') ? 'text-emerald-600 bg-emerald-50' : 'text-gray-700 hover:text-emerald-600 hover:bg-gray-50' }}">
                    Features
                </a>
                <a href="{{ route('website.pricing') }}"
                   class="px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('website.pricing') ? 'text-emerald-600 bg-emerald-50' : 'text-gray-700 hover:text-emerald-600 hover:bg-gray-50' }}">
                    Pricing
                </a>
                <a href="{{ route('website.usecases') }}"
                   class="px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('website.usecases*') ? 'text-emerald-600 bg-emerald-50' : 'text-gray-700 hover:text-emerald-600 hover:bg-gray-50' }}">
                    Use Cases
                </a>
                <a href="{{ route('website.industries') }}"
                   class="px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('website.industries*') ? 'text-emerald-600 bg-emerald-50' : 'text-gray-700 hover:text-emerald-600 hover:bg-gray-50' }}">
                    Industries
                </a>
                <a href="{{ route('website.partner') }}"
                   class="px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('website.partner*') ? 'text-emerald-600 bg-emerald-50' : 'text-gray-700 hover:text-emerald-600 hover:bg-gray-50' }}">
                    Partner 💰
                </a>
                <a href="{{ route('website.blog') }}"
                   class="px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('website.blog*') ? 'text-emerald-600 bg-emerald-50' : 'text-gray-700 hover:text-emerald-600 hover:bg-gray-50' }}">
                    Blog
                </a>
                <a href="{{ route('website.contact') }}"
                   class="px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('website.contact*') ? 'text-emerald-600 bg-emerald-50' : 'text-gray-700 hover:text-emerald-600 hover:bg-gray-50' }}">
                    Contact
                </a>
            </div>

            <div class="hidden md:flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 text-sm font-medium text-emerald-600 hover:text-emerald-700">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-emerald-600">
                        Login
                    </a>
                    <a href="{{ route('register') }}" class="px-5 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">
                        Start Free Trial
                    </a>
                @endauth
            </div>

            <div class="md:hidden flex items-center">
                <button @click="mobileOpen = !mobileOpen" class="text-gray-700 p-2">
                    <i class="fas text-xl" :class="mobileOpen ? 'fa-times' : 'fa-bars'"></i>
                </button>
            </div>
        </div>

        <div x-show="mobileOpen" x-cloak class="md:hidden pb-4 border-t" x-transition>
            <div class="pt-2 space-y-1">
                <a href="{{ route('website.home') }}" class="block px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-md">Home</a>
                <a href="{{ route('website.features') }}" class="block px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-md">Features</a>
                <a href="{{ route('website.pricing') }}" class="block px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-md">Pricing</a>
                <a href="{{ route('website.usecases') }}" class="block px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-md">Use Cases</a>
                <a href="{{ route('website.industries') }}" class="block px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-md">Industries</a>
                <a href="{{ route('website.partner') }}" class="block px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-md">Partner Program 💰</a>
                <a href="{{ route('website.blog') }}" class="block px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-md">Blog</a>
                <a href="{{ route('website.contact') }}" class="block px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-md">Contact</a>
                <div class="pt-2 border-t flex gap-2 px-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="flex-1 text-center px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="flex-1 text-center px-4 py-2 border rounded-lg text-sm font-medium">Login</a>
                        <a href="{{ route('register') }}" class="flex-1 text-center px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium">Free Trial</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
</header>