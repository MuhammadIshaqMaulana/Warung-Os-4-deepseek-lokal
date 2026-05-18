<x-app-layout>
    <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-10">
        <h2 class="font-black text-3xl text-[#41322A] leading-tight">
            {{ __('Inventaris Barang') }}
        </h2>
        <a href="{{ route('products.create') }}" class="mt-4 md:mt-0 inline-flex items-center px-8 py-4 bg-[#A35322] border border-transparent rounded-[1.5rem] font-black text-sm text-white uppercase tracking-widest hover:bg-[#8C471D] transition shadow-xl shadow-orange-100">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
            Tambah Item Baru
        </a>
    </div>

    <div class="max-w-7xl mx-auto">
        @if(session('success'))
        <div class="mb-6 flex items-center p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl text-emerald-800 shadow-sm" role="alert">
            <svg class="w-6 h-6 mr-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span class="font-bold">{{ session('success') }}</span>
        </div>
        @endif

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-[#E8E1D5] overflow-hidden">
            <div class="p-8 lg:p-10">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[#A39284] text-[10px] font-black uppercase tracking-widest border-b border-[#F7F2E9]">
                                <th class="pb-6">Produk</th>
                                <th class="pb-6">Kategori</th>
                                <th class="pb-6">Modal</th>
                                <th class="pb-6 text-[#A35322]">Harga Jual</th>
                                <th class="pb-6">Status Stok</th>
                                <th class="pb-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F7F2E9]">
                            @forelse($products as $product)
                            <tr class="group hover:bg-[#FAF6F0]/50 transition-colors">
                                <td class="py-6">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-[#F7F2E9] rounded-2xl flex items-center justify-center mr-4 text-[#A35322] font-black shadow-inner">
                                            {{ substr($product->name, 0, 1) }}
                                        </div>
                                        <div class="font-bold text-[#41322A]">{{ $product->name }}</div>
                                    </div>
                                </td>
                                <td class="py-6">
                                    <span class="px-3 py-1 bg-[#F7F2E9] rounded-lg text-[10px] font-black text-[#7A6A5E] uppercase">{{ $product->category ?? 'Umum' }}</span>
                                </td>
                                <td class="py-6 text-[#A39284] font-bold text-sm">Rp {{ number_format($product->buy_price, 0, ',', '.') }}</td>
                                <td class="py-6 font-black text-[#41322A]">Rp {{ number_format($product->sell_price, 0, ',', '.') }}</td>
                                <td class="py-6">
                                    <div class="flex items-center">
                                        <div class="w-16 bg-[#F7F2E9] rounded-full h-1.5 mr-3">
                                            <div class="h-1.5 rounded-full {{ $product->stock < 5 ? 'bg-rose-500' : 'bg-emerald-500' }}" style="width: {{ min(($product->stock / 20) * 100, 100) }}%"></div>
                                        </div>
                                        <span class="font-black text-sm {{ $product->stock < 5 ? 'text-rose-600' : 'text-[#41322A]' }}">{{ $product->stock }}</span>
                                    </div>
                                </td>
                                <td class="py-6 text-right">
                                    <div class="flex justify-end space-x-2">
                                        <a href="{{ route('products.edit', $product) }}" class="p-2 text-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                        <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus produk ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-rose-300 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-20 text-center">
                                    <div class="flex flex-col items-center opacity-30">
                                        <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                        <p class="font-black text-xl">Inventaris Kosong</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-10">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>