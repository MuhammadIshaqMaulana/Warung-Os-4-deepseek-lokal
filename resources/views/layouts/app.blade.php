<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-[#F7F2E9] text-[#41322A]">
        <div class="flex min-h-screen">
            <!-- Sidebar -->
            <div class="w-72 bg-[#FAF6F0] min-h-screen p-6 hidden lg:block border-r border-[#E8E1D5]">
                <div class="bg-[#41322A] text-white p-4 rounded-3xl flex items-center mb-10 shadow-lg">
                    <div class="bg-[#A35322] p-2 rounded-xl mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    </div>
                    <span class="font-bold text-sm truncate">{{ Auth::user()->name }}</span>
                </div>

                <nav class="space-y-2">
                    <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="dashboard">Dashboard</x-sidebar-link>
                    <x-sidebar-link :href="route('transactions.create')" :active="request()->routeIs('transactions.create')" icon="cashier">Kasir</x-sidebar-link>
                    <x-sidebar-link :href="route('products.index')" :active="request()->routeIs('products.*')" icon="inventory">Inventaris</x-sidebar-link>
                    <x-sidebar-link :href="route('transactions.index')" :active="request()->routeIs('transactions.index')" icon="report">Laporan</x-sidebar-link>
                </nav>

                <div class="mt-auto pt-10">
                    <div class="bg-white p-6 rounded-3xl border border-[#E8E1D5]">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Status Sistem</p>
                        <p class="text-2xl font-black text-[#41322A]">Online</p>
                        <p class="text-[10px] text-gray-400 mt-2">Semua data tersinkronisasi dengan aman di cloud.</p>
                    </div>
                    
                    <form method="POST" action="{{ route('logout') }}" class="mt-6">
                        @csrf
                        <button type="submit" class="w-full flex items-center px-4 py-3 text-gray-400 hover:text-rose-600 font-bold text-sm transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>

            <div class="flex-1 flex flex-col">
                <!-- Mobile Nav -->
                <div class="lg:hidden bg-[#FAF6F0] p-4 flex justify-between items-center border-b border-[#E8E1D5]">
                    <span class="font-bold text-[#41322A]">{{ Auth::user()->name }}</span>
                    <button class="text-[#41322A]">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                    </button>
                </div>

                <!-- Page Content -->
                <main class="p-4 lg:p-10 overflow-y-auto">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
