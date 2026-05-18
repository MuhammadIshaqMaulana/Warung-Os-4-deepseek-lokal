<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-black text-[#41322A]">Selamat Datang</h2>
        <p class="text-sm text-[#A39284] font-medium">Masuk untuk mengelola warung Anda.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-6">
            <label for="email" class="block text-[10px] font-black text-[#A39284] uppercase tracking-widest mb-2">Alamat Email</label>
            <input id="email" type="email" name="email" class="block w-full bg-[#F7F2E9] border-transparent focus:border-[#A35322] focus:bg-white focus:ring-4 focus:ring-[#A35322]/10 rounded-2xl py-4 font-bold text-[#41322A] transition-all" value="{{ old('email') }}" required autofocus placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mb-6">
            <div class="flex justify-between items-end mb-2">
                <label for="password" class="block text-[10px] font-black text-[#A39284] uppercase tracking-widest">Kata Sandi</label>
                @if (Route::has('password.request'))
                    <a class="text-[10px] font-black text-[#A35322] hover:text-[#8C471D] uppercase tracking-widest transition-colors" href="{{ route('password.request') }}">
                        Lupa Sandi?
                    </a>
                @endif
            </div>
            <input id="password" type="password" name="password" class="block w-full bg-[#F7F2E9] border-transparent focus:border-[#A35322] focus:bg-white focus:ring-4 focus:ring-[#A35322]/10 rounded-2xl py-4 font-bold text-[#41322A] transition-all" required autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center mb-10">
            <input id="remember_me" type="checkbox" class="w-5 h-5 rounded-lg border-[#E8E1D5] text-[#A35322] focus:ring-[#A35322]/20" name="remember">
            <label for="remember_me" class="ms-3 text-sm font-bold text-[#7A6A5E]">Ingat saya di perangkat ini</label>
        </div>

        <button type="submit" class="w-full bg-[#A35322] text-white py-5 rounded-2xl font-black text-lg hover:bg-[#8C471D] transition-all transform active:scale-95 shadow-xl shadow-orange-100 flex items-center justify-center gap-3">
            Masuk Sekarang
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M14 5l7 7-7 7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path></svg>
        </button>

        @if (Route::has('register'))
        <div class="mt-8 text-center">
            <p class="text-sm font-bold text-[#A39284]">
                Belum punya akun? 
                <a href="{{ route('register') }}" class="text-[#A35322] hover:underline ml-1">Daftar Warung</a>
            </p>
        </div>
        @endif
    </form>
</x-guest-layout>
