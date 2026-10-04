<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Warung OS') }}</title>

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
                    <span class="flex items-center justify-center w-16 h-16 text-6xl leading-none select-none" aria-hidden="true">🏠</span>
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
