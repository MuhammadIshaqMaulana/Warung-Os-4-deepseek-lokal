<x-app-layout>
    <x-slot name="title">Laporan Keuangan - {{ config('app.name') }}</x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
            <div>
                <h2 class="font-black text-3xl text-[#41322A] leading-tight">Laporan Keuangan</h2>
                <p class="text-sm text-[#A39284] mt-1">Ringkasan omzet & keuntungan {{ $label }}.</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('reports.index', array_merge(request()->query(), ['type' => 'daily'])) }}"
                   class="px-5 py-3 rounded-2xl font-black text-xs uppercase tracking-widest transition-all {{ $type === 'daily' ? 'bg-[#A35322] text-white shadow-lg shadow-orange-100' : 'bg-white text-[#7A6A5E] hover:bg-[#F0EAE0]' }}">
                    Harian
                </a>
                <a href="{{ route('reports.index', array_merge(request()->query(), ['type' => 'monthly'])) }}"
                   class="px-5 py-3 rounded-2xl font-black text-xs uppercase tracking-widest transition-all {{ $type === 'monthly' ? 'bg-[#41322A] text-white shadow-lg' : 'bg-white text-[#7A6A5E] hover:bg-[#F0EAE0]' }}">
                    Bulanan
                </a>

                <form method="GET" action="{{ route('reports.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="type" value="{{ $type }}">
                    <input type="{{ $type === 'monthly' ? 'month' : 'date' }}"
                           name="{{ $type === 'monthly' ? 'month' : 'date' }}"
                           value="{{ $type === 'monthly' ? $start->format('Y-m') : $start->format('Y-m-d') }}"
                           class="bg-white border-transparent focus:ring-4 focus:ring-[#A35322]/10 rounded-2xl py-3 px-4 font-bold text-sm text-[#41322A] shadow-sm">
                    <button type="submit" class="px-5 py-3 bg-[#41322A] text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-[#2D231C] transition">Terapkan</button>
                </form>
            </div>
        </div>

        <div class="flex items-center gap-2 mt-4">
            @php
                $prevQuery = request()->query();
                $nextQuery = request()->query();
                if ($type === 'monthly') {
                    $prevQuery['month'] = $start->copy()->subMonthNoOverflow()->format('Y-m');
                    $nextQuery['month'] = $start->copy()->addMonthNoOverflow()->format('Y-m');
                } else {
                    $prevQuery['date'] = $start->copy()->subDay()->format('Y-m-d');
                    $nextQuery['date'] = $start->copy()->addDay()->format('Y-m-d');
                }
                $prevQuery['type'] = $type;
                $nextQuery['type'] = $type;
            @endphp
            <a href="{{ route('reports.index', $prevQuery) }}" class="px-4 py-2 bg-white border border-[#E8E1D5] rounded-xl text-xs font-black text-[#7A6A5E] hover:bg-[#FAF6F0] transition">&larr; Sebelumnya</a>
            <a href="{{ route('reports.index', $nextQuery) }}" class="px-4 py-2 bg-white border border-[#E8E1D5] rounded-xl text-xs font-black text-[#7A6A5E] hover:bg-[#FAF6F0] transition">Berikutnya &rarr;</a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <!-- Ringkasan utama -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
            <div class="bg-[#FAF6F0] p-6 rounded-[2.5rem] border border-[#E8E1D5] shadow-sm">
                <p class="text-xs font-bold text-[#7A6A5E] uppercase tracking-widest mb-3">Total Omzet</p>
                <h3 class="text-3xl font-black text-[#41322A]">Rp {{ number_format($stats['revenue'], 0, ',', '.') }}</h3>
                <p class="text-[10px] text-[#A39284] mt-2">Dari {{ $stats['transactions'] }} transaksi lunas</p>
            </div>

            <div class="bg-[#E9F3E8] p-6 rounded-[2.5rem] border border-[#D5E1D5] shadow-sm">
                <p class="text-xs font-bold text-[#5E7A5E] uppercase tracking-widest mb-3">Total Profit</p>
                <h3 class="text-3xl font-black text-[#2A412A]">Rp {{ number_format($stats['profit'], 0, ',', '.') }}</h3>
                <p class="text-[10px] text-[#84A384] mt-2">Harga jual dikurangi harga beli</p>
            </div>

            <div class="bg-white p-6 rounded-[2.5rem] border border-[#E8E1D5] shadow-sm">
                <p class="text-xs font-bold text-[#7A6A5E] uppercase tracking-widest mb-3">Item Terjual</p>
                <h3 class="text-3xl font-black text-[#41322A]">{{ number_format($stats['items']) }}</h3>
                <p class="text-[10px] text-[#A39284] mt-2">Rata-rata Rp {{ number_format($stats['avg'], 0, ',', '.') }} / transaksi</p>
            </div>

            <div class="bg-[#A35322] p-6 rounded-[2.5rem] border border-[#8C471D] shadow-sm text-white">
                <p class="text-xs font-bold text-white/70 uppercase tracking-widest mb-3">Menunggu Bayar</p>
                <h3 class="text-3xl font-black">Rp {{ number_format($stats['pending'], 0, ',', '.') }}</h3>
                <p class="text-[10px] text-white/60 mt-2">Transaksi QRIS belum lunas</p>
            </div>
        </div>

        <!-- Chart -->
        <div class="bg-white p-6 lg:p-8 rounded-[3rem] border border-[#E8E1D5] shadow-sm mb-8">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
                <div>
                    <h3 class="text-xl font-black text-[#41322A]">Grafik omzet {{ $type === 'daily' ? 'per jam' : 'per hari' }}</h3>
                    <p class="text-xs text-[#A39284] mt-1">{{ $label }}</p>
                </div>
                <div class="flex items-center gap-4 text-[10px] font-black uppercase tracking-widest">
                    <span class="flex items-center text-[#7A6A5E]"><span class="w-3 h-3 bg-[#A35322] rounded-sm mr-2"></span>Omzet</span>
                    <span class="flex items-center text-[#7A6A5E]"><span class="w-3 h-3 bg-[#5E7A5E] rounded-sm mr-2"></span>Profit</span>
                </div>
            </div>

            @php
                $maxRevenue = max(1, $series->max(fn ($row) => $row['revenue']));
            @endphp
            <div class="flex items-end gap-1 h-56 overflow-x-auto pb-2">
                @foreach($series as $row)
                @php
                    $height = ($row['revenue'] / $maxRevenue) * 100;
                    $profitHeight = ($row['profit'] / $maxRevenue) * 100;
                @endphp
                <div class="flex flex-col items-center justify-end h-full min-w-[10px] flex-1 group relative">
                    <div class="w-full flex items-end justify-center gap-[2px] h-full">
                        <div class="w-1/2 bg-[#A35322] rounded-t-md transition-all {{ $row['revenue'] > 0 ? 'opacity-100' : 'opacity-20' }}"
                             style="height: {{ max($row['revenue'] > 0 ? 4 : 1, $height) }}%"></div>
                        <div class="w-1/2 bg-[#5E7A5E] rounded-t-md transition-all {{ $row['profit'] > 0 ? 'opacity-100' : 'opacity-20' }}"
                             style="height: {{ max($row['profit'] > 0 ? 4 : 1, $profitHeight) }}%"></div>
                    </div>

                    <div class="absolute -top-2 left-1/2 -translate-x-1/2 hidden group-hover:block bg-[#41322A] text-white text-[10px] font-bold px-3 py-2 rounded-xl whitespace-nowrap z-10 shadow-lg">
                        {{ $row['label'] }}<br>
                        Omzet Rp {{ number_format($row['revenue'], 0, ',', '.') }}<br>
                        Profit Rp {{ number_format($row['profit'], 0, ',', '.') }}<br>
                        {{ $row['count'] }} transaksi
                    </div>

                    @if($type === 'monthly' || $loop->index % 3 === 0)
                    <span class="text-[9px] font-bold text-[#A39284] mt-2">{{ $row['label'] }}</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Metode pembayaran -->
            <div class="bg-[#FAF6F0] p-6 lg:p-8 rounded-[3rem] border border-[#E8E1D5] shadow-sm">
                <h3 class="text-xl font-black text-[#41322A] mb-6">Metode pembayaran</h3>
                <div class="grid grid-cols-2 gap-6">
                    <div class="bg-white rounded-3xl p-6 border border-[#E8E1D5]">
                        <p class="text-[10px] font-black uppercase tracking-widest text-[#A39284] mb-2">Tunai</p>
                        <p class="text-2xl font-black text-[#41322A]">Rp {{ number_format($stats['cash']->amount ?? 0, 0, ',', '.') }}</p>
                        <p class="text-xs text-[#A39284] font-bold mt-1">{{ $stats['cash']->total ?? 0 }} transaksi</p>
                    </div>
                    <div class="bg-white rounded-3xl p-6 border border-[#E8E1D5]">
                        <p class="text-[10px] font-black uppercase tracking-widest text-[#A39284] mb-2">QRIS</p>
                        <p class="text-2xl font-black text-[#41322A]">Rp {{ number_format($stats['qris']->amount ?? 0, 0, ',', '.') }}</p>
                        <p class="text-xs text-[#A39284] font-bold mt-1">{{ $stats['qris']->total ?? 0 }} transaksi</p>
                    </div>
                </div>
                <div class="mt-6 bg-white rounded-3xl p-6 border border-[#E8E1D5] flex justify-between items-center">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-[#A39284]">Transaksi gagal / dibatalkan</p>
                        <p class="text-xs text-[#A39284] font-bold mt-1">Stok otomatis dikembalikan</p>
                    </div>
                    <span class="text-2xl font-black text-rose-500">{{ $stats['failed'] }}</span>
                </div>
            </div>

            <!-- Produk terlaris -->
            <div class="bg-white p-6 lg:p-8 rounded-[3rem] border border-[#E8E1D5] shadow-sm">
                <h3 class="text-xl font-black text-[#41322A] mb-6">Produk terlaris</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[#A39284] text-[10px] font-black uppercase tracking-widest border-b border-[#F7F2E9]">
                                <th class="pb-3">Produk</th>
                                <th class="pb-3 text-right">Terjual</th>
                                <th class="pb-3 text-right">Omzet</th>
                                <th class="pb-3 text-right">Profit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F7F2E9]">
                            @forelse($bestSellers as $row)
                            <tr>
                                <td class="py-4">
                                    <div class="font-bold text-[#41322A] text-sm">{{ $row->name }}</div>
                                    <div class="text-[10px] text-[#A39284] uppercase tracking-widest">{{ $row->category ?? 'Umum' }}</div>
                                </td>
                                <td class="py-4 text-right font-black text-[#41322A]">{{ $row->qty }}</td>
                                <td class="py-4 text-right font-bold text-sm text-[#7A6A5E]">Rp {{ number_format($row->revenue, 0, ',', '.') }}</td>
                                <td class="py-4 text-right font-black text-emerald-600 text-sm">Rp {{ number_format($row->profit, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="py-10 text-center text-sm font-bold text-[#A39284]">Belum ada penjualan pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Transaksi terakhir -->
        <div class="bg-white p-6 lg:p-8 rounded-[3rem] border border-[#E8E1D5] shadow-sm">
            <h3 class="text-xl font-black text-[#41322A] mb-6">Transaksi pada periode ini</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-[#A39284] text-[10px] font-black uppercase tracking-widest border-b border-[#F7F2E9]">
                            <th class="pb-4">Waktu</th>
                            <th class="pb-4">Item</th>
                            <th class="pb-4">Metode</th>
                            <th class="pb-4">Status</th>
                            <th class="pb-4 text-right">Total</th>
                            <th class="pb-4 text-right">Profit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F7F2E9]">
                        @forelse($recent as $trx)
                        <tr class="hover:bg-[#FAF6F0]/60 transition-colors">
                            <td class="py-4 text-sm font-bold text-[#41322A]">{{ $trx->created_at->format('d M, H:i') }}</td>
                            <td class="py-4 text-sm text-[#7A6A5E]">{{ $trx->details->sum('quantity') }} item</td>
                            <td class="py-4"><span class="px-3 py-1 bg-[#F7F2E9] rounded-lg text-[10px] font-black text-[#7A6A5E] uppercase">{{ $trx->methodLabel() }}</span></td>
                            <td class="py-4">
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase
                                    @if($trx->isPaid()) bg-emerald-100 text-emerald-700 @elseif($trx->isPending()) bg-amber-100 text-amber-700 @else bg-rose-100 text-rose-700 @endif">
                                    {{ $trx->statusLabel() }}
                                </span>
                            </td>
                            <td class="py-4 text-right font-black text-[#41322A]">Rp {{ number_format($trx->total_price, 0, ',', '.') }}</td>
                            <td class="py-4 text-right font-bold text-sm {{ $trx->profit() >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">Rp {{ number_format($trx->profit(), 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="py-10 text-center text-sm font-bold text-[#A39284]">Belum ada transaksi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
