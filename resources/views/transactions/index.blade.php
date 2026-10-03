<x-app-layout>
    <x-slot name="title">Riwayat Transaksi - {{ config('app.name') }}</x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
            <div>
                <h2 class="font-black text-3xl text-[#41322A] leading-tight">Riwayat Transaksi</h2>
                <p class="text-sm text-[#A39284] mt-1">Semua penjualan, status bayar, dan struknya.</p>
            </div>
            <a href="{{ route('transactions.create') }}" class="inline-flex items-center px-6 py-3 bg-[#A35322] rounded-2xl font-black text-xs text-white uppercase tracking-widest hover:bg-[#8C471D] transition shadow-lg shadow-orange-100">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                Kasir / Jual Baru
            </a>
        </div>

        <!-- Ringkasan -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <div class="bg-white rounded-3xl border border-[#E8E1D5] p-5">
                <p class="text-[10px] font-black uppercase tracking-widest text-[#A39284]">Transaksi</p>
                <p class="text-2xl font-black text-[#41322A]">{{ $summary['count'] }}</p>
            </div>
            <div class="bg-white rounded-3xl border border-[#E8E1D5] p-5">
                <p class="text-[10px] font-black uppercase tracking-widest text-[#A39284]">Omzet lunas</p>
                <p class="text-2xl font-black text-[#41322A]">Rp {{ number_format($summary['revenue'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-3xl border border-[#E8E1D5] p-5">
                <p class="text-[10px] font-black uppercase tracking-widest text-[#A39284]">Halaman</p>
                <p class="text-2xl font-black text-[#41322A]">{{ $transactions->currentPage() }} / {{ $transactions->lastPage() }}</p>
            </div>
            <a href="{{ route('reports.index') }}" class="bg-[#41322A] rounded-3xl p-5 text-white block hover:bg-[#2D231C] transition">
                <p class="text-[10px] font-black uppercase tracking-widest text-white/60">Laporan lengkap</p>
                <p class="text-2xl font-black">&rarr;</p>
            </a>
        </div>

        <!-- Filter -->
        <form method="GET" action="{{ route('transactions.index') }}" class="flex flex-wrap items-end gap-3 mt-6 bg-white rounded-3xl border border-[#E8E1D5] p-4">
            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-[#A39284] mb-1">Dari tanggal</label>
                <input type="date" name="from" value="{{ $filters['from'] }}" class="rounded-xl border-[#E8E1D5] focus:ring-4 focus:ring-[#A35322]/10 text-sm font-bold text-[#41322A]">
            </div>
            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-[#A39284] mb-1">Sampai</label>
                <input type="date" name="to" value="{{ $filters['to'] }}" class="rounded-xl border-[#E8E1D5] focus:ring-4 focus:ring-[#A35322]/10 text-sm font-bold text-[#41322A]">
            </div>
            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-[#A39284] mb-1">Status</label>
                <select name="status" class="rounded-xl border-[#E8E1D5] focus:ring-4 focus:ring-[#A35322]/10 text-sm font-bold text-[#41322A]">
                    <option value="">Semua</option>
                    <option value="paid" {{ $filters['status'] === 'paid' ? 'selected' : '' }}>Lunas</option>
                    <option value="pending" {{ $filters['status'] === 'pending' ? 'selected' : '' }}>Menunggu Bayar</option>
                    <option value="failed" {{ $filters['status'] === 'failed' ? 'selected' : '' }}>Gagal</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-[#A39284] mb-1">Metode</label>
                <select name="method" class="rounded-xl border-[#E8E1D5] focus:ring-4 focus:ring-[#A35322]/10 text-sm font-bold text-[#41322A]">
                    <option value="">Semua</option>
                    <option value="cash" {{ $filters['method'] === 'cash' ? 'selected' : '' }}>Tunai</option>
                    <option value="qris" {{ $filters['method'] === 'qris' ? 'selected' : '' }}>QRIS</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-5 py-3 bg-[#41322A] text-white rounded-xl font-black text-xs uppercase tracking-widest hover:bg-[#2D231C] transition">Terapkan</button>
                <a href="{{ route('transactions.index') }}" class="px-5 py-3 bg-[#F7F2E9] rounded-xl font-black text-xs uppercase tracking-widest text-[#7A6A5E] hover:bg-[#F0EAE0] transition">Reset</a>
            </div>
        </form>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-[#E8E1D5] overflow-hidden">
            <div class="p-6 lg:p-8">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[#A39284] text-[10px] font-black uppercase tracking-widest border-b border-[#F7F2E9]">
                                <th class="pb-5">Tanggal & Waktu</th>
                                <th class="pb-5">Item Terjual</th>
                                <th class="pb-5">Metode</th>
                                <th class="pb-5">Total Harga</th>
                                <th class="pb-5">Status</th>
                                <th class="pb-5 text-right">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F7F2E9]">
                            @forelse($transactions as $trx)
                            <tr class="hover:bg-[#FAF6F0]/60 transition-colors">
                                <td class="py-5">
                                    <div class="text-sm font-black text-[#41322A]">{{ $trx->created_at->format('d M Y') }}</div>
                                    <div class="text-xs text-[#A39284] font-medium">{{ $trx->created_at->format('H:i') }}</div>
                                </td>
                                <td class="py-5">
                                    <div class="font-bold text-[#41322A] text-sm">
                                        @if($trx->details->count() > 1)
                                            {{ $trx->details->first()->product->name ?? 'Produk' }} (+{{ $trx->details->count() - 1 }} lainnya)
                                        @else
                                            {{ $trx->details->first()->product->name ?? 'Produk Dihapus' }}
                                        @endif
                                    </div>
                                    <div class="text-xs text-[#A39284]">{{ $trx->details->sum('quantity') }} total item</div>
                                </td>
                                <td class="py-5">
                                    <span class="inline-flex items-center px-3 py-1 bg-[#F7F2E9] rounded-lg text-[10px] font-black uppercase text-[#7A6A5E] tracking-wider">
                                        {{ $trx->methodLabel() }}
                                    </span>
                                </td>
                                <td class="py-5">
                                    <div class="font-black text-[#41322A] text-lg">Rp {{ number_format($trx->total_price, 0, ',', '.') }}</div>
                                </td>
                                <td class="py-5">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase
                                        @if($trx->isPaid()) bg-emerald-100 text-emerald-700 @elseif($trx->isPending()) bg-amber-100 text-amber-700 @else bg-rose-100 text-rose-700 @endif">
                                        {{ $trx->statusLabel() }}
                                    </span>
                                </td>
                                <td class="py-5 text-right">
                                    <a href="{{ route('transactions.show', $trx) }}" class="inline-flex items-center px-4 py-2 bg-[#41322A] text-white rounded-xl font-bold text-xs hover:bg-[#A35322] transition">
                                        Lihat Struk
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-16 h-16 text-[#E8E1D5] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <p class="font-black text-xl text-[#41322A]">Belum ada transaksi</p>
                                        <p class="text-[#A39284] text-sm">Ayo mulai jualan hari ini!</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-8">
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
