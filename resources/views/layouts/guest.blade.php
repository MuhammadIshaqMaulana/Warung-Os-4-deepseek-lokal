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
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <div class="mb-10 text-center">
                <div class="inline-flex bg-[#41322A] p-4 rounded-3xl shadow-xl mb-6">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                </div>
                <h1 class="text-4xl font-black tracking-tight text-[#41322A]">Warung OS</h1>
                <p class="text-[#A39284] font-bold text-sm uppercase tracking-widest mt-2">Digital POS System</p>
            </div>

            <div class="w-full sm:max-w-md bg-white rounded-[3rem] border border-[#E8E1D5] shadow-2xl overflow-hidden">
                <div class="p-10 lg:p-12">
                    {{ $slot }}
                </div>
                <div class="bg-[#FAF6F0] p-6 text-center border-t border-[#E8E1D5]">
                    <p class="text-[10px] text-[#A39284] font-black uppercase tracking-widest">© {{ date('Y') }} • Powered by Warung OS Engine</p>
                </div>
            </div>
        </div>
    </body>
</html>
