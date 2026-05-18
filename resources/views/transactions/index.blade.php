<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-gray-800 leading-tight">
                {{ __('Riwayat Transaksi') }}
            </h2>
            <a href="{{ route('transactions.create') }}" class="inline-flex items-center px-6 py-3 bg-emerald-600 border border-transparent rounded-xl font-bold text-sm text-white uppercase tracking-widest hover:bg-emerald-700 transition shadow-lg shadow-emerald-100">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Kasir / Jual Baru
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl text-emerald-800 shadow-sm font-bold flex items-center">
                <svg class="w-6 h-6 mr-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('success') }}
            </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-8">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-gray-400 text-xs uppercase tracking-widest border-b border-gray-100">
                                    <th class="pb-4 font-bold">Tanggal & Waktu</th>
                                    <th class="pb-4 font-bold">Item Terjual</th>
                                    <th class="pb-4 font-bold">Metode</th>
                                    <th class="pb-4 font-bold">Total Harga</th>
                                    <th class="pb-4 font-bold">Status</th>
                                    <th class="pb-4 font-bold text-right">Detail</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($transactions as $trx)
                                @php $detail = $trx->details->first(); @endphp
                                <tr class="hover:bg-gray-50/50 transition-colors group">
                                    <td class="py-5">
                                        <div class="text-sm font-black text-gray-900">{{ $trx->created_at->format('d M Y') }}</div>
                                        <div class="text-xs text-gray-400 font-medium">{{ $trx->created_at->format('H:i:s') }}</div>
                                    </td>
                                    <td class="py-5">
                                        <div class="font-bold text-gray-800">{{ $detail->product->name ?? 'Produk Dihapus' }}</div>
                                        <div class="text-xs text-gray-400">{{ $detail->quantity ?? 0 }} item x Rp {{ number_format($detail->price ?? 0, 0, ',', '.') }}</div>
                                    </td>
                                    <td class="py-5">
                                        <span class="inline-flex items-center px-3 py-1 bg-gray-100 rounded-lg text-[10px] font-black uppercase text-gray-600 tracking-wider">
                                            {{ $detail->method ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="py-5">
                                        <div class="font-black text-gray-900 text-lg">Rp {{ number_format($trx->total_price, 0, ',', '.') }}</div>
                                    </td>
                                    <td class="py-5">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black {{ ($detail->status ?? '') === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                            {{ strtoupper($detail->status ?? 'Unknown') }}
                                        </span>
                                    </td>
                                    <td class="py-5 text-right">
                                        <a href="{{ route('transactions.show', $trx) }}" class="inline-flex items-center px-4 py-2 bg-indigo-50 text-indigo-700 rounded-xl font-bold text-xs hover:bg-indigo-100 transition">
                                            Lihat Struk
                                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-16 h-16 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            <p class="text-gray-500 font-bold text-xl">Belum ada transaksi</p>
                                            <p class="text-gray-400 text-sm">Ayo mulai jualan hari ini!</p>
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
    </div>
</x-app-layout>