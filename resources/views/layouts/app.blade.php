<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @stack('styles')
</head>
<body class="h-full" x-data="{ sidebarOpen: false }">
<div class="min-h-full">

    {{-- Mobile sidebar overlay --}}
    <div x-show="sidebarOpen" class="relative z-50 lg:hidden" x-cloak>
        <div x-show="sidebarOpen" class="fixed inset-0 bg-gray-900/80" @click="sidebarOpen = false"></div>
        <div class="fixed inset-0 flex">
            <div x-show="sidebarOpen" class="relative mr-16 flex w-full max-w-xs flex-1"
                 x-transition:enter="transition ease-in-out duration-300 transform"
                 x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in-out duration-300 transform"
                 x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">
                @include('layouts.partials.sidebar')
            </div>
        </div>
    </div>

    {{-- Desktop sidebar --}}
    <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-64 lg:flex-col">
        @include('layouts.partials.sidebar')
    </div>

    {{-- Main content --}}
    <div class="lg:pl-64">
        {{-- Top bar --}}
        @include('layouts.partials.topbar')

        <main class="py-6">
            <div class="px-4 sm:px-6 lg:px-8">
                {{-- Flash Messages --}}
                @if(session('success'))
                    <div class="mb-4 rounded-md bg-green-50 p-4" x-data="{show:true}" x-show="show">
                        <div class="flex">
                            <div class="flex-shrink-0"><i class="fas fa-check-circle text-green-400"></i></div>
                            <div class="ml-3"><p class="text-sm text-green-700">{{ session('success') }}</p></div>
                            <div class="ml-auto"><button @click="show=false" class="text-green-400 hover:text-green-600"><i class="fas fa-times"></i></button></div>
                        </div>
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-4 rounded-md bg-red-50 p-4" x-data="{show:true}" x-show="show">
                        <div class="flex">
                            <div class="flex-shrink-0"><i class="fas fa-exclamation-circle text-red-400"></i></div>
                            <div class="ml-3"><p class="text-sm text-red-700">{{ session('error') }}</p></div>
                            <div class="ml-auto"><button @click="show=false" class="text-red-400 hover:text-red-600"><i class="fas fa-times"></i></button></div>
                        </div>
                    </div>
                @endif
                @if($errors->any())
                    <div class="mb-4 rounded-md bg-red-50 p-4">
                        <ul class="list-disc list-inside text-sm text-red-700">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>