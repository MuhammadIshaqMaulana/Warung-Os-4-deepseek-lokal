<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-[#41322A] leading-tight">
            {{ __('Edit Item Inventaris') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-[3rem] border border-[#E8E1D5] shadow-xl overflow-hidden">
                <div class="p-8 lg:p-12">
                    <div class="flex items-center gap-4 mb-10">
                        <div class="w-16 h-16 bg-[#41322A] text-white rounded-[1.5rem] flex items-center justify-center text-2xl font-black">
                            {{ substr($product->name, 0, 1) }}
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-[#41322A]">{{ $product->name }}</h3>
                            <p class="text-xs font-bold text-[#A39284] uppercase tracking-widest">ID Produk: #{{ str_pad($product->id, 5, '0', STR_PAD_LEFT) }}</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('products.update', $product) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                            <!-- Nama Produk -->
                            <div class="md:col-span-2">
                                <label for="name" class="block text-xs font-black text-[#A39284] uppercase tracking-widest mb-2">Nama Produk</label>
                                <input id="name" type="text" name="name" class="block w-full bg-[#F7F2E9] border-transparent focus:border-[#A35322] focus:bg-white focus:ring-4 focus:ring-[#A35322]/10 rounded-2xl py-4 font-bold text-[#41322A] transition-all" value="{{ old('name', $product->name) }}" required />
                                @error('name')<span class="text-rose-500 text-xs mt-1 font-bold">{{ $message }}</span>@enderror
                            </div>

                            <!-- Kategori -->
                            <div>
                                <label for="category" class="block text-xs font-black text-[#A39284] uppercase tracking-widest mb-2">Kategori</label>
                                <select id="category" name="category" class="block w-full bg-[#F7F2E9] border-transparent focus:border-[#A35322] focus:bg-white focus:ring-4 focus:ring-[#A35322]/10 rounded-2xl py-4 font-bold text-[#41322A] transition-all">
                                    <option value="Umum" {{ $product->category === 'Umum' ? 'selected' : '' }}>Umum</option>
                                    <option value="Makanan" {{ $product->category === 'Makanan' ? 'selected' : '' }}>Makanan</option>
                                    <option value="Minuman" {{ $product->category === 'Minuman' ? 'selected' : '' }}>Minuman</option>
                                    <option value="Sembako" {{ $product->category === 'Sembako' ? 'selected' : '' }}>Sembako</option>
                                    <option value="Harian" {{ $product->category === 'Harian' ? 'selected' : '' }}>Harian</option>
                                    <option value="Lainnya" {{ $product->category === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                </select>
                                @error('category')<span class="text-rose-500 text-xs mt-1 font-bold">{{ $message }}</span>@enderror
                            </div>

                            <!-- Stok -->
                            <div>
                                <label for="stock" class="block text-xs font-black text-[#A39284] uppercase tracking-widest mb-2">Total Stok</label>
                                <input id="stock" type="number" name="stock" class="block w-full bg-[#F7F2E9] border-transparent focus:border-[#A35322] focus:bg-white focus:ring-4 focus:ring-[#A35322]/10 rounded-2xl py-4 font-bold text-[#41322A] transition-all" value="{{ old('stock', $product->stock) }}" min="0" required />
                                @error('stock')<span class="text-rose-500 text-xs mt-1 font-bold">{{ $message }}</span>@enderror
                            </div>

                            <!-- Harga Beli -->
                            <div>
                                <label for="buy_price" class="block text-xs font-black text-[#A39284] uppercase tracking-widest mb-2">Harga Beli (Modal)</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-[#A39284] font-bold">Rp</span>
                                    <input id="buy_price" type="number" name="buy_price" class="block w-full pl-12 bg-[#F7F2E9] border-transparent focus:border-[#A35322] focus:bg-white focus:ring-4 focus:ring-[#A35322]/10 rounded-2xl py-4 font-bold text-[#41322A] transition-all" value="{{ old('buy_price', $product->buy_price) }}" min="0" required />
                                </div>
                                @error('buy_price')<span class="text-rose-500 text-xs mt-1 font-bold">{{ $message }}</span>@enderror
                            </div>

                            <!-- Harga Jual -->
                            <div>
                                <label for="sell_price" class="block text-xs font-black text-[#A39284] uppercase tracking-widest mb-2 text-[#A35322]">Harga Jual</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-[#A35322] font-black">Rp</span>
                                    <input id="sell_price" type="number" name="sell_price" class="block w-full pl-12 bg-[#F7F2E9] border-transparent focus:border-[#A35322] focus:bg-white focus:ring-4 focus:ring-[#A35322]/10 rounded-2xl py-4 font-black text-[#41322A] transition-all" value="{{ old('sell_price', $product->sell_price) }}" min="0" required />
                                </div>
                                @error('sell_price')<span class="text-rose-500 text-xs mt-1 font-bold">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="flex flex-col md:flex-row items-center gap-4">
                            <button type="submit" class="w-full md:flex-1 bg-[#41322A] text-white py-5 rounded-2xl font-black text-lg hover:bg-[#2D231C] transition-all transform active:scale-95 shadow-xl flex items-center justify-center gap-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                Simpan Perubahan
                            </button>
                            <a href="{{ route('products.index') }}" class="w-full md:w-auto px-8 py-5 text-[#A39284] font-black text-center hover:text-[#41322A] transition-colors">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>