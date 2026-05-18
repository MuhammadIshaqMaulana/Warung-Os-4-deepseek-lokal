<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-gray-800 leading-tight">
            {{ __('Terminal Kasir') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                <!-- Form Input -->
                <div class="lg:col-span-3">
                    <div class="bg-white rounded-3xl shadow-xl shadow-gray-100 border border-gray-100 overflow-hidden">
                        <div class="p-8">
                            <h3 class="text-xl font-black text-gray-900 mb-8 flex items-center">
                                <span class="w-8 h-8 bg-indigo-600 text-white rounded-lg flex items-center justify-center mr-3 text-sm">1</span>
                                Input Penjualan
                            </h3>

                            <form method="POST" action="{{ route('transactions.store') }}">
                                @csrf

                                @if ($errors->any())
                                <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl text-rose-700 text-sm">
                                    <ul class="list-disc list-inside">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif

                                <div class="mb-6">
                                    <label for="product_id" class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Pilih Produk</label>
                                    <select id="product_id" name="product_id" class="block w-full bg-gray-50 border-transparent focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-100 rounded-2xl transition-all duration-200 py-4 font-bold text-gray-800" required autofocus onchange="updatePrice()">
                                        <option value="">-- Cari Produk --</option>
                                        @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-price="{{ $product->sell_price }}">{{ $product->name }} (Rp {{ number_format($product->sell_price, 0, ',', '.') }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-6 mb-8">
                                    <div>
                                        <label for="quantity" class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Jumlah</label>
                                        <input id="quantity" type="number" name="quantity" class="block w-full bg-gray-50 border-transparent focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-100 rounded-2xl transition-all duration-200 py-4 font-black text-gray-900" value="{{ old('quantity', 1) }}" min="1" required oninput="updatePrice()" />
                                    </div>
                                    <div>
                                        <label for="method" class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Metode</label>
                                        <select id="method" name="method" class="block w-full bg-gray-50 border-transparent focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-100 rounded-2xl transition-all duration-200 py-4 font-bold text-gray-800" required>
                                            <option value="cash">💵 Cash</option>
                                            <option value="qris">📱 QRIS</option>
                                        </select>
                                    </div>
                                </div>

                                <button type="submit" class="w-full bg-indigo-600 text-white py-5 rounded-2xl font-black text-xl hover:bg-indigo-700 transition transform active:scale-95 shadow-xl shadow-indigo-100 flex items-center justify-center">
                                    Proses Sekarang
                                    <svg class="w-6 h-6 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7-7 7"></path></svg>
                                </button>
                                
                                <div class="mt-6 text-center">
                                    <a href="{{ route('transactions.index') }}" class="text-sm font-bold text-gray-400 hover:text-gray-600 transition">Batal dan Kembali</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Receipt Preview -->
                <div class="lg:col-span-2">
                    <div class="bg-gray-900 rounded-3xl shadow-2xl p-8 text-white relative overflow-hidden h-fit sticky top-8">
                        <div class="absolute top-0 right-0 p-8 opacity-10">
                            <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path><path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"></path></svg>
                        </div>
                        
                        <h3 class="text-xs font-black text-indigo-400 uppercase tracking-widest mb-6 italic">Ringkasan Pembayaran</h3>
                        
                        <div class="space-y-4 mb-8">
                            <div class="flex justify-between border-b border-gray-800 pb-4">
                                <span class="text-gray-400 text-sm">Item Terpilih</span>
                                <span class="font-bold truncate max-w-[150px]" id="preview_name">-</span>
                            </div>
                            <div class="flex justify-between border-b border-gray-800 pb-4">
                                <span class="text-gray-400 text-sm">Harga Satuan</span>
                                <span class="font-bold" id="preview_price">Rp 0</span>
                            </div>
                            <div class="flex justify-between border-b border-gray-800 pb-4">
                                <span class="text-gray-400 text-sm">Jumlah</span>
                                <span class="font-bold" id="preview_qty">0</span>
                            </div>
                        </div>

                        <div class="mb-4 text-center">
                            <p class="text-xs text-gray-500 uppercase tracking-widest mb-2">Total Yang Harus Dibayar</p>
                            <h4 class="text-4xl font-black text-white" id="total_price_display">Rp 0</h4>
                        </div>

                        <div class="bg-gray-800/50 rounded-2xl p-4 mt-8 flex items-center justify-center text-xs text-gray-400 text-center font-medium italic">
                            Pastikan nominal sudah sesuai <br>sebelum menekan tombol proses.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updatePrice() {
            const select = document.getElementById('product_id');
            const qty = document.getElementById('quantity').value || 0;
            const priceDisplay = document.getElementById('total_price_display');
            
            const previewName = document.getElementById('preview_name');
            const previewPrice = document.getElementById('preview_price');
            const previewQty = document.getElementById('preview_qty');
            
            if (select.selectedIndex > 0) {
                const price = select.options[select.selectedIndex].getAttribute('data-price');
                const name = select.options[select.selectedIndex].text.split(' (')[0];
                const total = price * qty;
                
                const formattedTotal = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
                const formattedPrice = 'Rp ' + new Intl.NumberFormat('id-ID').format(price);
                
                priceDisplay.textContent = formattedTotal;
                previewName.textContent = name;
                previewPrice.textContent = formattedPrice;
                previewQty.textContent = qty;
            } else {
                priceDisplay.textContent = 'Rp 0';
                previewName.textContent = '-';
                previewPrice.textContent = 'Rp 0';
                previewQty.textContent = '0';
            }
        }
    </script>
</x-app-layout>