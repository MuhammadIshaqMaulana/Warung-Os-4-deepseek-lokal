<x-app-layout>
    <x-slot name="title">Inventaris Barang - {{ config('app.name') }}</x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
            <div>
                <h2 class="font-black text-3xl text-[#41322A] leading-tight">Inventaris Barang</h2>
                <p class="text-sm text-[#A39284] mt-1">Kelola produk, harga beli, harga jual, dan stok.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('stocks.index') }}" class="inline-flex items-center px-6 py-3 bg-white border border-[#E8E1D5] rounded-2xl font-black text-xs text-[#7A6A5E] uppercase tracking-widest hover:bg-[#FAF6F0] transition">
                    Riwayat Stok
                </a>
                <a href="{{ route('products.create') }}" class="inline-flex items-center px-6 py-3 bg-[#A35322] rounded-2xl font-black text-xs text-white uppercase tracking-widest hover:bg-[#8C471D] transition shadow-lg shadow-orange-100">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Item Baru
                </a>
            </div>
        </div>
    </x-slot>

    @php $threshold = (int) config('qris.low_stock_threshold', 5); @endphp

    <div class="max-w-7xl mx-auto">
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
                                    <div class="flex items-center gap-3">
                                        <div class="w-16 bg-[#F7F2E9] rounded-full h-1.5">
                                            <div class="h-1.5 rounded-full {{ $product->stock <= $threshold ? 'bg-rose-500' : 'bg-emerald-500' }}" style="width: {{ min(($product->stock / 20) * 100, 100) }}%"></div>
                                        </div>
                                        <span class="font-black text-sm {{ $product->stock <= $threshold ? 'text-rose-600' : 'text-[#41322A]' }}">{{ $product->stock }}</span>
                                        @if($product->stock <= 0)
                                        <span class="px-2 py-1 bg-rose-100 text-rose-700 rounded-full text-[9px] font-black uppercase">Habis</span>
                                        @elseif($product->stock <= $threshold)
                                        <span class="px-2 py-1 bg-amber-100 text-amber-700 rounded-full text-[9px] font-black uppercase">Menipis</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-6 text-right">
                                    <div class="flex justify-end space-x-2">
                                        <a href="{{ route('stocks.index', ['product_id' => $product->id]) }}" title="Update stok" class="p-2 text-emerald-500 hover:text-emerald-700 hover:bg-emerald-50 rounded-xl transition-all">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                        </a>
                                        <a href="{{ route('products.edit', $product) }}" title="Edit produk" class="p-2 text-[#A35322] hover:text-[#8C471D] hover:bg-[#F7F2E9] rounded-xl transition-all">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                        <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus produk ini? Riwayat penjualan tetap tersimpan.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus produk" class="p-2 text-rose-300 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all">
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