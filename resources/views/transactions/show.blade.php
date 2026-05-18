<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-gray-800 leading-tight">
            {{ __('Struk Pembayaran') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl text-emerald-800 shadow-sm font-bold flex items-center animate-bounce">
                <svg class="w-6 h-6 mr-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('success') }}
            </div>
            @endif

            <!-- Thermal Receipt Design -->
            <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">
                <div class="bg-indigo-600 p-8 text-white text-center relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-white opacity-20 transform -translate-y-1"></div>
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4 backdrop-blur-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-black">Warung OS Digital</h3>
                    <p class="text-xs text-indigo-100 font-bold uppercase tracking-widest mt-1 opacity-80">Terima Kasih Telah Belanja</p>
                </div>

                @php $detail = $transaction->details->first(); @endphp
                
                <div class="p-8">
                    <div class="flex justify-between items-start mb-8">
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">ID Transaksi</p>
                            <p class="text-sm font-black text-gray-900">#{{ str_pad($transaction->id, 5, '0', STR_PAD_LEFT) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Waktu</p>
                            <p class="text-sm font-bold text-gray-900">{{ $transaction->created_at->format('d/m/y H:i') }}</p>
                        </div>
                    </div>

                    <div class="border-y border-dashed border-gray-200 py-6 mb-8 space-y-4">
                        @foreach($transaction->details as $detail)
                        <div class="group">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm font-bold text-gray-900">{{ $detail->product->name ?? 'Produk Dihapus' }}</span>
                                <span class="text-xs font-medium text-gray-400">{{ $detail->quantity }}x</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] text-gray-400 uppercase tracking-widest">{{ $detail->product->category ?? 'Umum' }}</span>
                                <span class="text-sm font-bold text-gray-900">Rp {{ number_format($detail->price * $detail->quantity, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="flex justify-between items-center mb-8">
                        <span class="text-gray-400 font-black uppercase text-xs tracking-widest">Total Bayar</span>
                        <span class="text-3xl font-black text-indigo-600">Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</span>
                    </div>

                    <div class="bg-gray-50 rounded-2xl p-4 flex justify-between items-center mb-8">
                        <div class="flex items-center">
                            <div class="w-8 h-8 {{ $detail->method === 'cash' ? 'bg-emerald-100 text-emerald-600' : 'bg-indigo-100 text-indigo-600' }} rounded-lg flex items-center justify-center mr-3">
                                @if($detail->method === 'cash')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zM17 21H7a2 2 0 01-2-2V5a2 2 0 012-2h10a2 2 0 012 2v14a2 2 0 01-2 2z"></path></svg>
                                @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                @endif
                            </div>
                            <span class="text-sm font-black text-gray-900 uppercase">{{ $detail->method }}</span>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase {{ ($detail->status ?? '') === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ $detail->status ?? 'Unknown' }}
                        </span>
                    </div>

                    @if($detail->method === 'qris' && $detail->status === 'pending')
                    <div class="text-center">
                        <div class="inline-block p-6 bg-white border-4 border-indigo-50 rounded-3xl mb-6">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ $detail->external_id }}" alt="QRIS" class="w-40 h-40 mx-auto">
                        </div>
                        
                        <form action="{{ route('transactions.pay', $transaction) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-black hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 mb-4">
                                Konfirmasi Bayar
                            </button>
                        </form>
                    </div>
                    @endif

                    <div class="text-center">
                        <a href="{{ route('transactions.index') }}" class="text-xs font-black text-gray-400 hover:text-indigo-600 transition uppercase tracking-widest">Kembali ke Riwayat</a>
                    </div>
                </div>
                
                <!-- Bottom Decorative Serration -->
                <div class="flex justify-between px-1 mb-[-4px]">
                    @for($i=0; $i<20; $i++)
                    <div class="w-4 h-4 bg-gray-50 rounded-full"></div>
                    @endfor
                </div>
            </div>
        </div>
    </div>
</x-app-layout>