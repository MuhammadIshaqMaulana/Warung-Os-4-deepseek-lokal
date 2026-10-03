<x-app-layout>
    <x-slot name="title">Riwayat Stok - {{ config('app.name') }}</x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
            <div>
                <h2 class="font-black text-3xl text-[#41322A] leading-tight">Riwayat Stok</h2>
                <p class="text-sm text-[#A39284] mt-1">Pantau perubahan stok masuk & keluar beserta pemicunya.</p>
            </div>
            <a href="{{ route('products.index') }}" class="inline-flex items-center px-6 py-3 bg-[#A35322] rounded-2xl font-black text-xs text-white uppercase tracking-widest hover:bg-[#8C471D] transition shadow-lg shadow-orange-100">
                Kelola Inventaris
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Stok menipis + form restock -->
            <div class="lg:col-span-4 space-y-8">
                <div class="bg-white p-6 rounded-[2.5rem] border border-[#E8E1D5] shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-black text-[#41322A]">Stok menipis</h3>
                        <span class="px-3 py-1 bg-[#A35322] text-white text-[10px] font-black rounded-full uppercase">≤ {{ $threshold }}</span>
                    </div>

                    <div class="space-y-3 max-h-72 overflow-y-auto no-scrollbar pr-1">
                        @forelse($lowStock as $product)
                        <div class="flex items-center justify-between bg-[#F7F2E9] rounded-2xl p-4">
                            <div class="min-w-0 pr-3">
                                <p class="font-bold text-sm text-[#41322A] truncate">{{ $product->name }}</p>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-[#A39284]">{{ $product->category ?? 'Umum' }}</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-black {{ $product->stock <= 0 ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $product->stock <= 0 ? 'HABIS' : $product->stock }}
                            </span>
                        </div>
                        @empty
                        <p class="text-sm font-bold text-[#A39284] text-center py-6">Semua stok aman.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-[#41322A] text-white p-6 rounded-[2.5rem] shadow-xl">
                    <h3 class="text-lg font-black mb-1">Update stok cepat</h3>
                    <p class="text-xs text-white/50 mb-6">Isi minus untuk stok keluar, plus untuk restok.</p>

                    <form method="POST" action="{{ route('stocks.restock') }}" id="restock-form">
                        @csrf
                        <label class="block text-[10px] font-black uppercase tracking-widest text-white/50 mb-2">Produk</label>
                        <select name="product_id" id="restock-product" class="w-full bg-white/10 border-transparent focus:bg-white/20 focus:ring-4 focus:ring-white/10 rounded-2xl py-3 px-4 font-bold text-sm text-white mb-4" required>
                            <option value="">Pilih produk...</option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}" class="text-[#41322A]" {{ (string) request('product_id') === (string) $product->id ? 'selected' : '' }} data-stock="{{ $product->stock }}">{{ $product->name }} (stok {{ $product->stock }})</option>
                            @endforeach
                        </select>

                        <label class="block text-[10px] font-black uppercase tracking-widest text-white/50 mb-2">Jumlah (+/-)</label>
                        <input type="number" name="quantity" class="w-full bg-white/10 border-transparent focus:bg-white/20 focus:ring-4 focus:ring-white/10 rounded-2xl py-3 px-4 font-bold text-sm text-white mb-4" placeholder="contoh: 20 atau -3" required>

                        <label class="block text-[10px] font-black uppercase tracking-widest text-white/50 mb-2">Keterangan</label>
                        <input type="text" name="note" maxlength="255" class="w-full bg-white/10 border-transparent focus:bg-white/20 focus:ring-4 focus:ring-white/10 rounded-2xl py-3 px-4 font-bold text-sm text-white mb-6" placeholder="Restok dari supplier">

                        @error('quantity')
                        <p class="text-rose-300 text-xs font-bold mb-4">{{ $message }}</p>
                        @enderror
                        @error('product_id')
                        <p class="text-rose-300 text-xs font-bold mb-4">{{ $message }}</p>
                        @enderror

                        <button type="submit" class="w-full bg-[#A35322] text-white py-3 rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-[#8C471D] transition">Simpan Perubahan</button>
                    </form>
                </div>
            </div>

            <!-- Tabel riwayat -->
            <div class="lg:col-span-8">
                <form method="GET" action="{{ route('stocks.index') }}" class="flex flex-wrap items-center gap-3 mb-6">
                    <select name="type" class="bg-white border-transparent focus:ring-4 focus:ring-[#A35322]/10 rounded-2xl py-3 px-4 font-bold text-sm text-[#41322A] shadow-sm">
                        <option value="">Semua jenis</option>
                        <option value="in" {{ $filters['type'] === 'in' ? 'selected' : '' }}>Masuk</option>
                        <option value="out" {{ $filters['type'] === 'out' ? 'selected' : '' }}>Keluar</option>
                    </select>

                    <select name="product_id" class="bg-white border-transparent focus:ring-4 focus:ring-[#A35322]/10 rounded-2xl py-3 px-4 font-bold text-sm text-[#41322A] shadow-sm">
                        <option value="">Semua produk</option>
                        @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ (string) $filters['product_id'] === (string) $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="px-5 py-3 bg-[#41322A] text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-[#2D231C] transition">Filter</button>
                    <a href="{{ route('stocks.index') }}" class="px-5 py-3 bg-white border border-[#E8E1D5] rounded-2xl font-black text-xs uppercase tracking-widest text-[#7A6A5E] hover:bg-[#FAF6F0] transition">Reset</a>
                </form>

                <div class="bg-white rounded-[2.5rem] border border-[#E8E1D5] shadow-sm overflow-hidden">
                    <div class="p-6 lg:p-8 overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-[#A39284] text-[10px] font-black uppercase tracking-widest border-b border-[#F7F2E9]">
                                    <th class="pb-4">Waktu</th>
                                    <th class="pb-4">Produk</th>
                                    <th class="pb-4">Jenis</th>
                                    <th class="pb-4 text-right">Jumlah</th>
                                    <th class="pb-4">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#F7F2E9]">
                                @forelse($logs as $log)
                                <tr class="hover:bg-[#FAF6F0]/60 transition-colors">
                                    <td class="py-4 text-sm font-bold text-[#41322A]">{{ $log->created_at->format('d M Y') }}<div class="text-[10px] text-[#A39284] font-bold">{{ $log->created_at->format('H:i') }}</div></td>
                                    <td class="py-4 text-sm font-bold text-[#41322A]">{{ $log->product->name ?? 'Produk dihapus' }}</td>
                                    <td class="py-4">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase {{ $log->change_type === 'in' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                            {{ $log->directionLabel() }}
                                        </span>
                                    </td>
                                    <td class="py-4 text-right font-black {{ $log->change_type === 'in' ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $log->change_type === 'in' ? '+' : '-' }}{{ $log->quantity }}
                                    </td>
                                    <td class="py-4 text-xs text-[#7A6A5E] font-medium">{{ $log->note ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="py-14 text-center">
                                        <p class="font-black text-[#41322A]">Belum ada riwayat stok</p>
                                        <p class="text-sm text-[#A39284]">Riwayat akan muncul setiap kali stok berubah.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 lg:px-8 pb-8">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('restock-form');
            const select = document.getElementById('restock-product');

            form.addEventListener('submit', function (event) {
                if (!select.value) {
                    event.preventDefault();
                    alert('Pilih produk terlebih dahulu.');
                }
            });
        });
    </script>
</x-app-layout>
