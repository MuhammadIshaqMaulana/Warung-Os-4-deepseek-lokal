<x-app-layout>
    <div class="max-w-7xl mx-auto">
        <!-- Top Stats Row -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
            <!-- Omzet -->
            <div class="bg-[#FAF6F0] p-6 rounded-[2.5rem] border border-[#E8E1D5] shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-4">
                    <p class="text-xs font-bold text-[#7A6A5E] uppercase tracking-widest">Omzet hari ini</p>
                    <svg class="w-4 h-4 text-[#7A6A5E] group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M7 17L17 7M17 7H7M17 7V17" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </div>
                <h3 class="text-3xl font-black text-[#41322A] mb-2">Rp {{ number_format($todaySales, 0, ',', '.') }}</h3>
                <p class="text-[10px] text-[#A39284] leading-relaxed">Akumulasi transaksi yang sudah masuk sejak pagi.</p>
            </div>

            <!-- Transaksi -->
            <div class="bg-[#E9F3E8] p-6 rounded-[2.5rem] border border-[#D5E1D5] shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-4">
                    <p class="text-xs font-bold text-[#5E7A5E] uppercase tracking-widest">Transaksi hari ini</p>
                    <svg class="w-4 h-4 text-[#5E7A5E] group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M7 17L17 7M17 7H7M17 7V17" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </div>
                <h3 class="text-3xl font-black text-[#2A412A] mb-2">{{ $todayTransactionsCount }} transaksi</h3>
                <p class="text-[10px] text-[#84A384] leading-relaxed">Ringkasan cepat untuk memantau ritme kasir.</p>
            </div>

            <!-- Stok -->
            <div class="bg-[#A35322] p-6 rounded-[2.5rem] border border-[#8C471D] shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow text-white">
                <div class="flex justify-between items-start mb-4">
                    <p class="text-xs font-bold text-white/70 uppercase tracking-widest">Stok menipis</p>
                    <svg class="w-4 h-4 text-white group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M7 17L17 7M17 7H7M17 7V17" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </div>
                <h3 class="text-3xl font-black mb-2">{{ $lowStockCount }} item</h3>
                <p class="text-[10px] text-white/60 leading-relaxed text-balance">Barang yang mulai rawan kosong dan sebaiknya segera dicek.</p>
            </div>

            <!-- Profit (Replcing Kasbon from image) -->
            <div class="bg-white p-6 rounded-[2.5rem] border border-[#E8E1D5] shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-4">
                    <p class="text-xs font-bold text-[#7A6A5E] uppercase tracking-widest">Estimasi Profit</p>
                    <svg class="w-4 h-4 text-[#7A6A5E] group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M7 17L17 7M17 7H7M17 7V17" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </div>
                <h3 class="text-3xl font-black text-[#41322A] mb-2">Rp {{ number_format($todayProfit, 0, ',', '.') }}</h3>
                <p class="text-[10px] text-[#A39284] leading-relaxed">Total margin keuntungan dari penjualan hari ini.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            <!-- Middle Activity Section -->
            <div class="lg:col-span-8">
                <h2 class="text-2xl font-black text-[#41322A] mb-2">Aktivitas terbaru</h2>
                <p class="text-sm text-[#A39284] mb-8">Ringkasan aktivitas transaksi Anda hari ini.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Latest Transaction Card (Large) -->
                    @php $latestTrx = $timelineTransactions->first(); @endphp
                    <div class="bg-[#41322A] text-white p-8 rounded-[3rem] shadow-2xl relative overflow-hidden">
                        <div class="flex items-center mb-8">
                            <div class="bg-[#A35322] p-2 rounded-xl mr-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                            </div>
                            <span class="text-sm font-bold opacity-70 tracking-widest uppercase">Transaksi terakhir</span>
                        </div>

                        @if($latestTrx)
                        <h4 class="text-4xl font-black mb-1">Rp {{ number_format($latestTrx->total_price, 0, ',', '.') }}</h4>
                        <p class="text-xs font-bold opacity-50 mb-10 uppercase tracking-widest">{{ strtoupper($latestTrx->details->first()->method ?? 'Tunai') }} • {{ $latestTrx->created_at->format('H.i') }}</p>

                        <div class="space-y-4">
                            @foreach($latestTrx->details as $item)
                            <div class="flex justify-between items-center text-sm">
                                <span class="font-medium opacity-80">{{ $item->product->name }} x{{ $item->quantity }}</span>
                                <span class="font-bold">Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</span>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="py-20 text-center opacity-30 font-bold italic">Belum ada transaksi</div>
                        @endif
                    </div>

                    <!-- Timeline Section -->
                    <div class="bg-white p-8 rounded-[3rem] border border-[#E8E1D5] shadow-sm">
                        <div class="flex items-center mb-8">
                            <div class="bg-[#F7F2E9] p-2 rounded-xl mr-3 text-[#A35322]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                            </div>
                            <span class="text-sm font-bold text-[#41322A] tracking-widest uppercase opacity-70">Timeline transaksi</span>
                        </div>

                        <div class="space-y-6 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                            @foreach($timelineTransactions->skip(1) as $trx)
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-lg font-black text-[#41322A]">Rp {{ number_format($trx->total_price, 0, ',', '.') }}</p>
                                    <p class="text-[10px] font-bold text-[#A39284] uppercase tracking-widest">
                                        {{ $trx->details->first()->quantity ?? 1 }} produk • {{ strtoupper($trx->details->first()->method ?? 'Tunai') }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] font-black text-[#7A6A5E] uppercase tracking-widest">{{ $trx->created_at->format('d M, H.i') }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Inventory Alert Section -->
            <div class="lg:col-span-4">
                <h2 class="text-2xl font-black text-[#41322A] mb-2">Stok perlu perhatian</h2>
                <p class="text-sm text-[#A39284] mb-8">Segera restok barang-barang berikut.</p>

                <div class="space-y-4">
                    @forelse($lowStockProducts as $product)
                    <div class="bg-white p-5 rounded-[2rem] border border-[#E8E1D5] shadow-sm flex items-center group hover:bg-[#FAF6F0] transition-colors">
                        <div class="w-12 h-12 bg-rose-50 rounded-2xl flex items-center justify-center mr-4 group-hover:bg-white transition-colors">
                            <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                        </div>
                        <div class="flex-1">
                            <h5 class="font-bold text-[#41322A] text-sm">{{ $product->name }}</h5>
                            <p class="text-[10px] text-[#A39284] font-bold uppercase tracking-widest">{{ $product->category ?? 'Umum' }}</p>
                        </div>
                        <div class="text-right">
                            <div class="inline-block px-3 py-1 bg-[#A35322] text-white text-[10px] font-black rounded-full shadow-sm">
                                {{ $product->stock }} / <span class="opacity-60 font-medium">min 5</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="bg-white/50 p-10 rounded-[2rem] border border-dashed border-[#E8E1D5] text-center">
                        <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                        </div>
                        <p class="text-sm font-bold text-[#41322A]">Stok barang aman</p>
                    </div>
                    @endforelse
                </div>

                <!-- Bottom Promo/Info Card -->
                <div class="mt-10 bg-gradient-to-br from-[#E9F3E8] to-[#FAF6F0] p-8 rounded-[3rem] border border-[#D5E1D5] relative overflow-hidden shadow-sm">
                    <div class="absolute -top-10 -right-10 w-32 h-32 bg-white/30 rounded-full blur-3xl"></div>
                    <h5 class="text-lg font-black text-[#2A412A] mb-2 leading-tight">Kelola warung jadi lebih mudah</h5>
                    <p class="text-xs text-[#5E7A5E] leading-relaxed opacity-80">Gunakan fitur kasir untuk pencatatan otomatis yang lebih akurat.</p>
                </div>
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #F7F2E9;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #E8E1D5;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #A39284;
        }
    </style>
</x-app-layout>