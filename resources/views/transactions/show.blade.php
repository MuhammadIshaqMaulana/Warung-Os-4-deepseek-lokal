<x-app-layout>
    <x-slot name="title">Struk #{{ str_pad($transaction->id, 5, '0', STR_PAD_LEFT) }} - {{ config('app.name') }}</x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
            <div>
                <h2 class="font-black text-3xl text-[#41322A] leading-tight">Struk Pembayaran</h2>
                <p class="text-sm text-[#A39284] mt-1">Transaksi #{{ str_pad($transaction->id, 5, '0', STR_PAD_LEFT) }} &middot; {{ $transaction->created_at->format('d M Y H:i') }}</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="button" onclick="window.print()" class="px-5 py-3 bg-white border border-[#E8E1D5] rounded-2xl font-black text-xs uppercase tracking-widest text-[#7A6A5E] hover:bg-[#FAF6F0] transition">
                    Cetak Struk
                </button>
                <a href="{{ route('transactions.index') }}" class="px-5 py-3 bg-[#41322A] text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-[#2D231C] transition">
                    Riwayat Transaksi
                </a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-3xl shadow-2xl border border-[#E8E1D5] overflow-hidden">
            <!-- Header struk -->
            <div class="bg-[#41322A] p-8 text-white text-center relative overflow-hidden">
                <div class="w-16 h-16 bg-[#A35322] rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h3 class="text-xl font-black">{{ config('app.name') }}</h3>
                <p class="text-xs text-white/60 font-bold uppercase tracking-widest mt-1">Terima Kasih Telah Belanja</p>
            </div>

            <div class="p-6 lg:p-8">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <p class="text-[10px] font-black text-[#A39284] uppercase tracking-widest mb-1">ID Transaksi</p>
                        <p class="text-sm font-black text-[#41322A]">#{{ str_pad($transaction->id, 5, '0', STR_PAD_LEFT) }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-black text-[#A39284] uppercase tracking-widest mb-1">Status</p>
                        <span id="status-badge"
                              class="inline-block px-3 py-1 rounded-full text-[10px] font-black uppercase
                              @if($transaction->isPaid()) bg-emerald-100 text-emerald-700 @elseif($transaction->isPending()) bg-amber-100 text-amber-700 @else bg-rose-100 text-rose-700 @endif">
                            {{ $transaction->statusLabel() }}
                        </span>
                    </div>
                </div>

                <!-- Item -->
                <div class="border-y border-dashed border-[#E8E1D5] py-6 mb-6 space-y-4">
                    @foreach($transaction->details as $detail)
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-bold text-[#41322A]">{{ $detail->product->name ?? 'Produk Dihapus' }}</span>
                            <span class="text-xs font-medium text-[#A39284]">{{ $detail->quantity }}x</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] text-[#A39284] uppercase tracking-widest">{{ $detail->product->category ?? 'Umum' }}</span>
                            <span class="text-sm font-bold text-[#41322A]">Rp {{ number_format($detail->price * $detail->quantity, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="flex justify-between items-center mb-4">
                    <span class="text-[#A39284] font-black uppercase text-xs tracking-widest">Total Bayar</span>
                    <span class="text-3xl font-black text-[#A35322]">Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</span>
                </div>

                <div class="bg-[#F7F2E9] rounded-2xl p-4 flex justify-between items-center mb-6">
                    <div class="flex items-center">
                        <div class="w-8 h-8 {{ $transaction->method === 'cash' ? 'bg-emerald-100 text-emerald-600' : 'bg-[#41322A] text-white' }} rounded-lg flex items-center justify-center mr-3">
                            @if($transaction->method === 'cash')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m-1-10v4m4-4v4m-4-4h4M6 7h2v4H6zm0 6h2m10-6h2v4h-2z"></path></svg>
                            @endif
                        </div>
                        <span class="text-sm font-black text-[#41322A] uppercase">{{ $transaction->methodLabel() }}</span>
                    </div>
                    <span id="paid-at" class="text-[10px] font-bold text-[#A39284]">{{ $transaction->paid_at?->format('d M Y H:i') ?: 'Belum dibayar' }}</span>
                </div>

                <!-- QRIS: QR + countdown + kontrol -->
                @if($transaction->method === 'qris')
                <div id="qris-panel" class="text-center {{ $transaction->isPaid() ? 'hidden' : '' }}">
                    <div class="bg-[#F7F2E9] rounded-3xl p-6 mb-6">
                        <p class="text-[10px] font-black text-[#A39284] uppercase tracking-widest mb-4">Pindai kode untuk membayar</p>

                        <div class="inline-block p-4 bg-white border-4 border-[#E8E1D5] rounded-3xl">
                            @if($qrUrl)
                            <img src="{{ $qrUrl }}" alt="QRIS"
                                 class="w-56 h-56 mx-auto"
                                 onerror="this.style.display='none'; document.getElementById('qr-fallback').classList.remove('hidden');">
                            @endif
                            <div id="qr-fallback" class="{{ $qrUrl ? 'hidden' : '' }} w-56 h-56 flex flex-col items-center justify-center text-center px-4">
                                <p class="text-xs font-bold text-[#7A6A5E] mb-2">QR tidak dapat dimuat. Bayar manual dengan data berikut:</p>
                                <p class="text-[10px] font-mono break-all text-[#41322A]">{{ $qrPayload }}</p>
                            </div>
                        </div>

                        <div class="mt-5 grid grid-cols-2 gap-3 text-left">
                            <div class="bg-white rounded-2xl p-3 border border-[#E8E1D5]">
                                <p class="text-[9px] font-black text-[#A39284] uppercase tracking-widest">Nominal</p>
                                <p class="text-sm font-black text-[#41322A]">Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</p>
                            </div>
                            <div class="bg-white rounded-2xl p-3 border border-[#E8E1D5]">
                                <p class="text-[9px] font-black text-[#A39284] uppercase tracking-widest">Referensi</p>
                                <p class="text-sm font-black text-[#41322A] break-all">{{ $transaction->external_id }}</p>
                            </div>
                        </div>

                        <div id="countdown-box" class="mt-5 {{ $transaction->isPending() ? '' : 'hidden' }}">
                            <p class="text-[10px] font-black text-[#A39284] uppercase tracking-widest mb-1">Berlaku hingga</p>
                            <p id="countdown" class="text-3xl font-black text-[#A35322] tabular-nums">--:--</p>
                            <p class="text-[10px] text-[#A39284] font-bold mt-1">Status diperiksa otomatis tiap 5 detik.</p>
                        </div>
                    </div>

                    <!-- Aksi pembayaran -->
                    <div class="space-y-3">
                        @if($transaction->isPending())
                        <form action="{{ route('transactions.pay', $transaction) }}" method="POST" class="print:hidden">
                            @csrf
                            <button type="submit" class="w-full bg-[#A35322] text-white py-4 rounded-2xl font-black hover:bg-[#8C471D] transition shadow-lg shadow-orange-100">
                                Bayar Sekarang (Simulasi Gateway)
                            </button>
                        </form>
                        <p class="text-[10px] text-[#A39284] font-bold">Gateway QRIS masih placeholder — tombol di atas mensimulasikan callback pembayaran sukses.</p>
                        @endif

                        <div id="expired-actions" class="{{ $transaction->isPending() ? 'hidden' : 'flex' }} flex-col sm:flex-row gap-3 print:hidden">
                            <form action="{{ route('transactions.retry', $transaction) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full bg-[#41322A] text-white py-4 rounded-2xl font-black hover:bg-[#2D231C] transition">
                                    Coba Bayar Lagi
                                </button>
                            </form>
                            <form action="{{ route('transactions.cancel', $transaction) }}" method="POST" class="flex-1" onsubmit="return confirm('Batalkan transaksi ini dan kembalikan stok?');">
                                @csrf
                                <button type="submit" class="w-full bg-white border border-[#E8E1D5] text-rose-500 py-4 rounded-2xl font-black hover:bg-rose-50 transition">
                                    Batalkan Transaksi
                                </button>
                            </form>
                        </div>

                        @if($transaction->isPaid())
                        <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-4">
                            <p class="text-sm font-black text-emerald-700">Pembayaran sudah lunas.</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <div class="text-center mt-8 print:hidden">
                    <a href="{{ route('transactions.index') }}" class="text-xs font-black text-[#A39284] hover:text-[#A35322] transition uppercase tracking-widest">Kembali ke Riwayat</a>
                </div>
            </div>

            <!-- Serration bawah -->
            <div class="flex justify-between px-1 mb-[-4px]">
                @for($i = 0; $i < 20; $i++)
                <div class="w-4 h-4 bg-[#F7F2E9] rounded-full"></div>
                @endfor
            </div>
        </div>
    </div>

    @if($transaction->method === 'qris')
    <script>
        (function () {
            const statusUrl = @json(route('transactions.status', $transaction));
            const badge = document.getElementById('status-badge');
            const countdownEl = document.getElementById('countdown');
            const countdownBox = document.getElementById('countdown-box');
            const expiredActions = document.getElementById('expired-actions');
            const qrisPanel = document.getElementById('qris-panel');
            const paidAt = document.getElementById('paid-at');

            let status = @json($statusPayload['status']);
            let expiresAt = @json($transaction->expires_at?->getTimestamp());

            const classes = {
                paid: ['bg-emerald-100', 'text-emerald-700'],
                pending: ['bg-amber-100', 'text-amber-700'],
                failed: ['bg-rose-100', 'text-rose-700']
            };

            function applyStatus(data) {
                status = data.status;

                if (badge) {
                    badge.className = 'inline-block px-3 py-1 rounded-full text-[10px] font-black uppercase ' + (classes[data.status] || classes.failed).join(' ');
                    badge.textContent = data.label;
                }

                if (data.paid_at && paidAt) {
                    const date = new Date(data.paid_at);
                    paidAt.textContent = date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' + date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                }

                if (data.status === 'paid') {
                    if (countdownBox) countdownBox.classList.add('hidden');
                    if (qrisPanel) { /* biarkan QR tetap terlihat */ }
                    if (expiredActions) expiredActions.classList.add('hidden');
                    setTimeout(function () { window.location.reload(); }, 900);
                }

                if (data.status === 'failed') {
                    if (countdownBox) countdownBox.classList.add('hidden');
                    if (expiredActions) expiredActions.classList.remove('hidden');
                    const payBtn = document.querySelector('#qris-panel form[action$="/pay"]');
                    if (payBtn) payBtn.parentElement.classList.add('hidden');
                }
            }

            async function checkStatus() {
                if (status !== 'pending') return;

                try {
                    const response = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
                    if (!response.ok) return;
                    const data = await response.json();
                    applyStatus(data);
                    if (data.expires_in !== null && data.expires_in !== undefined) {
                        expiresAt = Math.floor(Date.now() / 1000) + data.expires_in;
                    }
                } catch (error) {
                    // abaikan gangguan jaringan, coba lagi pada tick berikutnya
                }
            }

            function renderCountdown() {
                if (status !== 'pending' || !expiresAt || !countdownEl) return;

                const remaining = expiresAt - Math.floor(Date.now() / 1000);

                if (remaining <= 0) {
                    countdownEl.textContent = '00:00';
                    checkStatus();
                    return;
                }

                const minutes = String(Math.floor(remaining / 60)).padStart(2, '0');
                const seconds = String(remaining % 60).padStart(2, '0');
                countdownEl.textContent = minutes + ':' + seconds;
            }

            renderCountdown();
            setInterval(renderCountdown, 1000);
            setInterval(checkStatus, 5000);
            checkStatus();
        })();
    </script>
    @endif
</x-app-layout>
