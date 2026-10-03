<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@isset($title){{ $title }}@else{{ config('app.name', 'Warung OS') }}@endisset</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-[#F7F2E9] text-[#41322A]">
        <div class="flex h-screen overflow-hidden">
            <!-- Sidebar -->
            <aside class="w-16 sm:w-20 lg:w-72 bg-[#FAF6F0] h-screen p-3 lg:p-6 flex flex-col border-r border-[#E8E1D5] flex-shrink-0 transition-all duration-300">
                <div class="bg-[#41322A] text-white p-3 lg:p-4 rounded-2xl lg:rounded-3xl flex items-center mb-8 lg:mb-10 shadow-lg overflow-hidden">
                    <div class="bg-[#A35322] p-2 rounded-xl flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    </div>
                    <span class="font-bold text-sm truncate ml-3 hidden lg:block">{{ Auth::user()->name }}</span>
                </div>

                <nav class="space-y-2 flex-1 overflow-y-auto no-scrollbar">
                    <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="dashboard"><span class="hidden lg:block">Dashboard</span></x-sidebar-link>
                    <x-sidebar-link :href="route('transactions.create')" :active="request()->routeIs('transactions.create')" icon="cashier"><span class="hidden lg:block">Kasir</span></x-sidebar-link>
                    <x-sidebar-link :href="route('products.index')" :active="request()->routeIs('products.*')" icon="inventory"><span class="hidden lg:block">Inventaris</span></x-sidebar-link>
                    <x-sidebar-link :href="route('stocks.index')" :active="request()->routeIs('stocks.*')" icon="stock"><span class="hidden lg:block">Riwayat Stok</span></x-sidebar-link>
                    <x-sidebar-link :href="route('reports.index')" :active="request()->routeIs('reports.*')" icon="report"><span class="hidden lg:block">Laporan</span></x-sidebar-link>
                    <x-sidebar-link :href="route('transactions.index')" :active="request()->routeIs('transactions.index') || request()->routeIs('transactions.show')" icon="history"><span class="hidden lg:block">Riwayat</span></x-sidebar-link>
                </nav>

                <div class="pt-6 lg:pt-10 mt-auto">
                    <div class="bg-white p-4 rounded-2xl border border-[#E8E1D5] hidden lg:block">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Status</p>
                        <p class="text-xl font-black text-[#41322A]">Online</p>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="mt-4 lg:mt-6">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center lg:justify-start lg:px-4 py-3 text-gray-400 hover:text-rose-600 font-bold text-sm transition-colors">
                            <svg class="w-5 h-5 lg:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            <span class="hidden lg:block">Keluar</span>
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Main Scrollable Area -->
            <div class="flex-1 h-screen overflow-y-auto bg-[#F7F2E9] custom-scrollbar">
                <!-- Mobile Header -->
                <div class="lg:hidden bg-[#FAF6F0] p-4 flex justify-between items-center border-b border-[#E8E1D5]">
                    <span class="font-bold text-[#41322A] text-xs truncate">{{ Auth::user()->name }}</span>
                    <span class="text-[10px] font-black uppercase tracking-widest text-[#A39284]">{{ config('app.name') }}</span>
                </div>

                <!-- Page Content -->
                <main class="p-4 lg:p-10">
                    @isset($header)
                        <div class="mb-8">{{ $header }}</div>
                    @endisset

                    @if (session('success'))
                    <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl text-emerald-800 shadow-sm font-bold flex items-center" role="alert">
                        <svg class="w-5 h-5 mr-3 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    @endif

                    @if (session('error'))
                    <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl text-rose-800 shadow-sm font-bold flex items-center" role="alert">
                        <svg class="w-5 h-5 mr-3 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
