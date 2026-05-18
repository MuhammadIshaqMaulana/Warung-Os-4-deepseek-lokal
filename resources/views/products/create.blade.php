<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-[#41322A] leading-tight">
            {{ __('Tambah Item Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-[3rem] border border-[#E8E1D5] shadow-xl overflow-hidden">
                <div class="p-8 lg:p-12">
                    <form method="POST" action="{{ route('products.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                            <!-- Nama Produk -->
                            <div class="md:col-span-2">
                                <label for="name" class="block text-xs font-black text-[#A39284] uppercase tracking-widest mb-2">Nama Produk</label>
                                <input id="name" type="text" name="name" class="block w-full bg-[#F7F2E9] border-transparent focus:border-[#A35322] focus:bg-white focus:ring-4 focus:ring-[#A35322]/10 rounded-2xl py-4 font-bold text-[#41322A] transition-all" value="{{ old('name') }}" placeholder="Contoh: Beras Pandan Wangi 5kg" required autofocus />
                                @error('name')<span class="text-rose-500 text-xs mt-1 font-bold">{{ $message }}</span>@enderror
                            </div>

                            <!-- Kategori -->
                            <div>
                                <label for="category" class="block text-xs font-black text-[#A39284] uppercase tracking-widest mb-2">Kategori</label>
                                <select id="category" name="category" class="block w-full bg-[#F7F2E9] border-transparent focus:border-[#A35322] focus:bg-white focus:ring-4 focus:ring-[#A35322]/10 rounded-2xl py-4 font-bold text-[#41322A] transition-all">
                                    <option value="Umum">Umum</option>
                                    <option value="Makanan">Makanan</option>
                                    <option value="Minuman">Minuman</option>
                                    <option value="Sembako">Sembako</option>
                                    <option value="Harian">Harian</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                                @error('category')<span class="text-rose-500 text-xs mt-1 font-bold">{{ $message }}</span>@enderror
                            </div>

                            <!-- Stok Awal -->
                            <div>
                                <label for="stock" class="block text-xs font-black text-[#A39284] uppercase tracking-widest mb-2">Stok Awal</label>
                                <input id="stock" type="number" name="stock" class="block w-full bg-[#F7F2E9] border-transparent focus:border-[#A35322] focus:bg-white focus:ring-4 focus:ring-[#A35322]/10 rounded-2xl py-4 font-bold text-[#41322A] transition-all" value="{{ old('stock', 0) }}" min="0" required />
                                @error('stock')<span class="text-rose-500 text-xs mt-1 font-bold">{{ $message }}</span>@enderror
                            </div>

                            <!-- Harga Beli -->
                            <div>
                                <label for="buy_price" class="block text-xs font-black text-[#A39284] uppercase tracking-widest mb-2">Harga Beli (Modal)</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-[#A39284] font-bold">Rp</span>
                                    <input id="buy_price" type="number" name="buy_price" class="block w-full pl-12 bg-[#F7F2E9] border-transparent focus:border-[#A35322] focus:bg-white focus:ring-4 focus:ring-[#A35322]/10 rounded-2xl py-4 font-bold text-[#41322A] transition-all" value="{{ old('buy_price', 0) }}" min="0" required />
                                </div>
                                @error('buy_price')<span class="text-rose-500 text-xs mt-1 font-bold">{{ $message }}</span>@enderror
                            </div>

                            <!-- Harga Jual -->
                            <div>
                                <label for="sell_price" class="block text-xs font-black text-[#A39284] uppercase tracking-widest mb-2 text-[#A35322]">Harga Jual</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-[#A35322] font-black">Rp</span>
                                    <input id="sell_price" type="number" name="sell_price" class="block w-full pl-12 bg-[#F7F2E9] border-transparent focus:border-[#A35322] focus:bg-white focus:ring-4 focus:ring-[#A35322]/10 rounded-2xl py-4 font-black text-[#41322A] transition-all" value="{{ old('sell_price', 0) }}" min="0" required />
                                </div>
                                @error('sell_price')<span class="text-rose-500 text-xs mt-1 font-bold">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="flex flex-col md:flex-row items-center gap-4">
                            <button type="submit" class="w-full md:flex-1 bg-[#A35322] text-white py-5 rounded-2xl font-black text-lg hover:bg-[#8C471D] transition-all transform active:scale-95 shadow-xl shadow-orange-100 flex items-center justify-center gap-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                Daftarkan Produk
                            </button>
                            <a href="{{ route('products.index') }}" class="w-full md:w-auto px-8 py-5 text-[#A39284] font-black text-center hover:text-[#41322A] transition-colors">Batal</a>
                        </div>
                    </form>
                </div>
                
                <div class="bg-[#F7F2E9] p-6 text-center">
                    <p class="text-[10px] text-[#A39284] font-bold uppercase tracking-widest">Produk baru akan langsung tersedia di menu Kasir setelah disimpan.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>