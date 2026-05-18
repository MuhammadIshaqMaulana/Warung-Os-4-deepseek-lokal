<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center">
            <h2 class="font-black text-2xl text-gray-800 leading-tight">
                {{ __('Katalog Produk') }}
            </h2>
            <a href="{{ route('products.create') }}" class="mt-4 md:mt-0 inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-xl font-bold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 transition ease-in-out duration-150 shadow-lg shadow-indigo-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Produk Baru
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
            <div class="mb-6 flex items-center p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl text-emerald-800 shadow-sm" role="alert">
                <svg class="w-6 h-6 mr-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span class="font-bold">{{ session('success') }}</span>
            </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-8">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-gray-400 text-xs uppercase tracking-widest border-b border-gray-100">
                                    <th class="pb-4 font-bold">Informasi Produk</th>
                                    <th class="pb-4 font-bold">Kategori</th>
                                    <th class="pb-4 font-bold">Harga Beli</th>
                                    <th class="pb-4 font-bold text-indigo-600">Harga Jual</th>
                                    <th class="pb-4 font-bold">Stok</th>
                                    <th class="pb-4 font-bold text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($products as $product)
                                <tr class="group hover:bg-gray-50/50 transition-colors">
                                    <td class="py-5">
                                        <div class="flex items-center">
                                            <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mr-4 text-indigo-600 font-black">
                                                {{ substr($product->name, 0, 1) }}
                                            </div>
                                            <div class="font-bold text-gray-900">{{ $product->name }}</div>
                                        </div>
                                    </td>
                                    <td class="py-5">
                                        <span class="px-3 py-1 bg-gray-100 rounded-full text-xs font-bold text-gray-600">{{ $product->category ?? 'General' }}</span>
                                    </td>
                                    <td class="py-5 text-gray-500 font-medium">Rp {{ number_format($product->buy_price, 0, ',', '.') }}</td>
                                    <td class="py-5 font-black text-gray-900">Rp {{ number_format($product->sell_price, 0, ',', '.') }}</td>
                                    <td class="py-5">
                                        <div class="flex items-center">
                                            <div class="w-full bg-gray-100 rounded-full h-2 w-16 mr-3">
                                                <div class="h-2 rounded-full {{ $product->stock < 5 ? 'bg-rose-500' : 'bg-emerald-500' }}" style="width: {{ min(($product->stock / 20) * 100, 100) }}%"></div>
                                            </div>
                                            <span class="font-black {{ $product->stock < 5 ? 'text-rose-600' : 'text-gray-900' }}">{{ $product->stock }}</span>
                                        </div>
                                    </td>
                                    <td class="py-5 text-right">
                                        <div class="flex justify-end space-x-2">
                                            <a href="{{ route('products.edit', $product) }}" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </a>
                                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus produk ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-16 h-16 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                            <p class="text-gray-500 font-bold">Belum ada produk terdaftar</p>
                                            <p class="text-gray-400 text-sm">Klik tombol tambah di atas untuk memulai</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-8">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>