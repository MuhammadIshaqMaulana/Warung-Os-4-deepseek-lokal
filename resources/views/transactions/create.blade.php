<x-app-layout>
    <div x-data="{
        allProducts: {{ $products->toJson() }},
        cart: [],
        search: '',
        category: 'Semua',
        paymentMethod: 'cash',
        
        get filteredProducts() {
            return this.allProducts.filter(p => {
                const matchesSearch = p.name.toLowerCase().includes(this.search.toLowerCase()) || 
                                    (p.category && p.category.toLowerCase().includes(this.search.toLowerCase()));
                const matchesCategory = this.category === 'Semua' || p.category === this.category;
                return matchesSearch && matchesCategory;
            });
        },
        
        addToCart(product) {
            const existing = this.cart.find(item => item.id === product.id);
            if (existing) {
                if (existing.quantity < product.stock) {
                    existing.quantity++;
                }
            } else {
                this.cart.push({
                    id: product.id,
                    name: product.name,
                    price: product.sell_price,
                    quantity: 1,
                    stock: product.stock
                });
            }
        },
        
        removeFromCart(productId) {
            this.cart = this.cart.filter(item => item.id !== productId);
        },
        
        updateQuantity(productId, delta) {
            const item = this.cart.find(i => i.id === productId);
            if (item) {
                const newQty = item.quantity + delta;
                if (newQty > 0 && newQty <= item.stock) {
                    item.quantity = newQty;
                }
            }
        },
        
        get totalItems() {
            return this.cart.reduce((sum, item) => sum + item.quantity, 0);
        },
        
        get totalPrice() {
            return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        },
        
        formatCurrency(num) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
        }
    }" class="max-w-[1600px] mx-auto">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            
            <!-- Left: Product Selection -->
            <div class="lg:col-span-8">
                <div class="flex flex-col md:flex-row md:justify-between md:items-end mb-8 gap-4">
                    <div>
                        <h2 class="text-3xl font-black text-[#41322A] mb-2">Produk siap jual</h2>
                        <p class="text-sm text-[#A39284]">Semua fokus kasir ada di sini: cari produk, tap item, lalu lanjut ke keranjang.</p>
                    </div>
                    
                    <div class="relative w-full md:w-80">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                        </span>
                        <input x-model="search" type="text" placeholder="Cari produk atau kategori" class="w-full pl-12 pr-4 py-4 bg-white border-transparent focus:ring-4 focus:ring-[#A35322]/10 rounded-2xl font-bold text-[#41322A] shadow-sm transition-all">
                    </div>
                </div>

                <!-- Categories -->
                <div class="flex flex-wrap gap-3 mb-10">
                    <template x-for="cat in ['Semua', 'Makanan', 'Minuman', 'Sembako', 'Harian', 'Lainnya']">
                        <button 
                            @click="category = cat"
                            :class="category === cat ? 'bg-[#A35322] text-white shadow-lg shadow-orange-100' : 'bg-white text-[#7A6A5E] hover:bg-[#F0EAE0]'"
                            class="px-6 py-2 rounded-full text-sm font-bold transition-all"
                            x-text="cat"
                        ></button>
                    </template>
                </div>

                <!-- Product Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-20">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <div class="bg-white p-6 rounded-[2.5rem] border border-[#E8E1D5] shadow-sm group hover:shadow-md transition-all relative overflow-hidden">
                            <div class="flex justify-between items-start mb-6">
                                <div class="w-12 h-12 bg-[#41322A] text-white rounded-2xl flex items-center justify-center">
                                    <template x-if="product.category === 'Minuman'">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                    </template>
                                    <template x-if="product.category !== 'Minuman'">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                    </template>
                                </div>
                                <span class="text-[10px] font-black px-3 py-1 bg-[#E9F3E8] text-[#5E7A5E] rounded-full uppercase tracking-widest" x-text="product.stock + ' stok'"></span>
                            </div>

                            <h3 class="text-xl font-black text-[#41322A] mb-1 truncate" x-text="product.name"></h3>
                            <p class="text-xs font-bold text-[#A39284] uppercase tracking-widest mb-6" x-text="product.category || 'Umum'"></p>

                            <div class="flex justify-between items-center mt-auto">
                                <span class="text-xl font-black text-[#41322A]" x-text="formatCurrency(product.sell_price)"></span>
                                <button @click="addToCart(product)" class="bg-[#41322A] text-white px-6 py-2 rounded-xl font-bold text-sm hover:bg-[#A35322] transition-colors shadow-lg">Tap</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Right: Active Cart (Fixed position) -->
            <div class="lg:col-span-4 relative">
                <div class="bg-white rounded-[3rem] border border-[#E8E1D5] shadow-xl p-8 lg:fixed lg:right-10 lg:top-10 lg:bottom-10 lg:w-[calc((100vw-350px)*0.33)] flex flex-col max-h-screen">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="text-2xl font-black text-[#41322A]">Keranjang aktif</h3>
                        <span class="bg-[#41322A] text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-widest" x-text="totalItems + ' item'"></span>
                    </div>
                    <p class="text-sm text-[#A39284] mb-8">Semua item yang sudah ditap akan muncul di sini.</p>

                    <!-- Cart Items -->
                    <div class="flex-1 overflow-y-auto space-y-6 mb-8 no-scrollbar">
                        <template x-if="cart.length === 0">
                            <div class="py-20 flex flex-col items-center justify-center opacity-30">
                                <svg class="w-20 h-20 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                <p class="font-bold text-center">Belum ada item<br><span class="text-xs font-medium">Tap produk di sisi kiri untuk mulai.</span></p>
                            </div>
                        </template>

                        <template x-for="item in cart" :key="item.id">
                            <div class="flex justify-between items-center group">
                                <div class="flex-1 min-w-0 pr-4">
                                    <h4 class="font-bold text-[#41322A] truncate text-sm" x-text="item.name"></h4>
                                    <p class="text-[10px] font-medium text-[#A39284]" x-text="formatCurrency(item.price)"></p>
                                </div>
                                <div class="flex items-center bg-[#F7F2E9] rounded-xl p-1">
                                    <button @click="updateQuantity(item.id, -1)" class="w-7 h-7 flex items-center justify-center text-[#41322A] hover:bg-white rounded-lg transition-colors">-</button>
                                    <span class="w-7 text-center font-black text-xs" x-text="item.quantity"></span>
                                    <button @click="updateQuantity(item.id, 1)" class="w-7 h-7 flex items-center justify-center text-[#41322A] hover:bg-white rounded-lg transition-colors">+</button>
                                </div>
                                <button @click="removeFromCart(item.id)" class="ml-2 text-rose-300 hover:text-rose-600 transition-colors opacity-0 group-hover:opacity-100">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                </button>
                            </div>
                        </template>
                    </div>

                    <!-- Payment Section -->
                    <div class="mt-auto border-t border-[#E8E1D5] pt-6">
                        <label class="block text-[10px] font-black text-[#A39284] uppercase tracking-widest mb-3">Metode pembayaran</label>
                        <div class="grid grid-cols-2 gap-3 mb-6">
                            <button 
                                @click="paymentMethod = 'cash'"
                                :class="paymentMethod === 'cash' ? 'bg-[#A35322] text-white shadow-lg shadow-orange-100' : 'bg-[#F7F2E9] text-[#7A6A5E]'"
                                class="flex items-center justify-center py-3 rounded-2xl font-bold transition-all gap-2 text-sm"
                            >
                                Tunai
                            </button>
                            <button 
                                @click="paymentMethod = 'qris'"
                                :class="paymentMethod === 'qris' ? 'bg-[#41322A] text-white shadow-lg' : 'bg-[#F7F2E9] text-[#7A6A5E]'"
                                class="flex items-center justify-center py-3 rounded-2xl font-bold transition-all gap-2 text-sm"
                            >
                                QRIS
                            </button>
                        </div>

                        <div class="mb-6">
                            <p class="text-[10px] font-bold text-[#A39284] uppercase tracking-widest mb-1">Total tagihan</p>
                            <h4 class="text-3xl font-black text-[#41322A]" x-text="formatCurrency(totalPrice)"></h4>
                        </div>

                        <form method="POST" action="{{ route('transactions.store') }}">
                            @csrf
                            <input type="hidden" name="method" :value="paymentMethod">
                            <template x-for="(item, index) in cart" :key="item.id">
                                <div>
                                    <input type="hidden" :name="'items['+index+'][id]'" :value="item.id">
                                    <input type="hidden" :name="'items['+index+'][quantity]'" :value="item.quantity">
                                </div>
                            </template>
                            
                            <button 
                                type="submit" 
                                :disabled="cart.length === 0"
                                :class="cart.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:scale-[1.02] shadow-xl shadow-orange-100'"
                                class="w-full bg-[#A35322] text-white py-4 rounded-3xl font-black text-lg transition-all flex items-center justify-center gap-3"
                            >
                                Selesaikan transaksi
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
