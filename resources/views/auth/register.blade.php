<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-black text-[#41322A]">Daftar Warung Baru</h2>
        <p class="text-sm text-[#A39284] font-medium">Mulai digitalisasi warung Anda sekarang.</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div class="mb-6">
            <label for="name" class="block text-[10px] font-black text-[#A39284] uppercase tracking-widest mb-2">Nama Pemilik / Warung</label>
            <input id="name" type="text" name="name" class="block w-full bg-[#F7F2E9] border-transparent focus:border-[#A35322] focus:bg-white focus:ring-4 focus:ring-[#A35322]/10 rounded-2xl py-4 font-bold text-[#41322A] transition-all" value="{{ old('name') }}" required autofocus placeholder="Contoh: Warung Berkah" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mb-6">
            <label for="email" class="block text-[10px] font-black text-[#A39284] uppercase tracking-widest mb-2">Alamat Email</label>
            <input id="email" type="email" name="email" class="block w-full bg-[#F7F2E9] border-transparent focus:border-[#A35322] focus:bg-white focus:ring-4 focus:ring-[#A35322]/10 rounded-2xl py-4 font-bold text-[#41322A] transition-all" value="{{ old('email') }}" required placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mb-6">
            <label for="password" class="block text-[10px] font-black text-[#A39284] uppercase tracking-widest mb-2">Kata Sandi Baru</label>
            <input id="password" type="password" name="password" class="block w-full bg-[#F7F2E9] border-transparent focus:border-[#A35322] focus:bg-white focus:ring-4 focus:ring-[#A35322]/10 rounded-2xl py-4 font-bold text-[#41322A] transition-all" required autocomplete="new-password" placeholder="Minimal 8 karakter" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mb-10">
            <label for="password_confirmation" class="block text-[10px] font-black text-[#A39284] uppercase tracking-widest mb-2">Konfirmasi Kata Sandi</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="block w-full bg-[#F7F2E9] border-transparent focus:border-[#A35322] focus:bg-white focus:ring-4 focus:ring-[#A35322]/10 rounded-2xl py-4 font-bold text-[#41322A] transition-all" required placeholder="Ulangi kata sandi" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <button type="submit" class="w-full bg-[#A35322] text-white py-5 rounded-2xl font-black text-lg hover:bg-[#8C471D] transition-all transform active:scale-95 shadow-xl shadow-orange-100 flex items-center justify-center gap-3">
            Daftar Sekarang
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
        </button>

        <div class="mt-8 text-center">
            <p class="text-sm font-bold text-[#A39284]">
                Sudah punya akun? 
                <a href="{{ route('login') }}" class="text-[#A35322] hover:underline ml-1">Masuk di sini</a>
            </p>
        </div>
    </form>
</x-guest-layout>
