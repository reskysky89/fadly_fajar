@component('layouts.guest_market')

    <div class="max-w-screen-xl mx-auto px-4 pb-20 pt-6"
         x-data="keranjangApp()"
         @update-grand-total.window="grandTotalFormatted = $event.detail.total"
         @cart-error-status.window="updateErrorStatus($event.detail)">
        
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6 border-b border-gray-200 pb-4">
            Keranjang Belanja
        </h1>

        {{-- ALERT JIKA ADA STOK KURANG --}}
        <div x-show="hasError" x-transition class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded shadow-sm flex items-start gap-3">
            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <div>
                <p class="font-bold">Perhatian!</p>
                <p class="text-sm">Ada barang yang melebihi stok tersedia. Silakan kurangi jumlahnya.</p>
            </div>
        </div>

        @if($keranjang->count() > 0)
            <div class="flex flex-col lg:flex-row gap-8">
                
                {{-- KOLOM KIRI: LIST PRODUK --}}
                <div class="flex-1 bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                    <ul class="divide-y divide-gray-100">
                        @foreach($keranjang as $item)
                            <li class="p-4 flex items-center gap-4 transition relative"
                                :class="outOfStock ? 'bg-red-50' : 'hover:bg-gray-50'"
                                {{-- Inisialisasi Data Item --}}
                                x-data="cartItem(
                                    '{{ $item->id_keranjang }}', 
                                    '{{ $item->produk->id_produk }}', 
                                    {{ $item->jumlah }}, 
                                    {{ $item->nilai_konversi ?? 1 }} // Default 1 jika null
                                )">
                                
                                {{-- 1. GAMBAR (KIRI) --}}
                                <div class="w-20 h-20 flex-shrink-0 rounded-lg bg-gray-100 border border-gray-200 overflow-hidden relative">
                                    @if($item->produk->gambar)
                                        {{-- Gunakan asset() langsung (Metode Upload Public) --}}
                                        <img src="{{ asset($item->produk->gambar) }}" class="w-full h-full object-cover" :class="outOfStock ? 'opacity-50' : ''">
                                    @else
                                        <div class="flex items-center justify-center h-full text-gray-300"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg></div>
                                    @endif
                                    
                                    {{-- Overlay Merah Jika Stok Kurang --}}
                                    <div x-show="outOfStock" class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-10">
                                        <span class="bg-red-600 text-white text-[10px] font-bold px-2 py-1 rounded-full shadow">STOK KURANG</span>
                                    </div>
                                </div>

                                {{-- 2. INFO PRODUK (TENGAH) --}}
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-base font-bold text-gray-900 truncate">{{ $item->produk->nama_produk }}</h3>
                                    <p class="text-sm text-gray-500 mt-1">
                                        <span class="font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded text-xs mr-2">{{ $item->satuan }}</span>
                                        Rp {{ number_format($item->harga_saat_ini, 0, ',', '.') }}
                                    </p>
                                    
                                    {{-- Indikator Sisa Stok Real-time --}}
                                    <p class="text-xs mt-2 font-bold" 
                                       :class="outOfStock ? 'text-red-600' : 'text-green-600'">
                                        Tersedia: <span x-text="realMax">...</span> {{ $item->satuan }}
                                    </p>

                                    <form action="{{ route('keranjang.destroy', $item->id_keranjang) }}" method="POST" class="mt-2">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs text-red-500 hover:text-red-700 flex items-center gap-1 transition font-semibold">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            Hapus
                                        </button>
                                    </form>
                                </div>

                                {{-- 3. STEPPER JUMLAH (KANAN) --}}
                                <div class="flex flex-col items-end gap-2">
                                    <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden bg-white shadow-sm h-9"
                                         :class="outOfStock ? 'border-red-500 ring-1 ring-red-500' : ''">
                                        
                                        {{-- Tombol Kurang --}}
                                        <button type="button" @click="updateQty(qty - 1)" 
                                                :disabled="isLoading || qty <= 1" 
                                                class="w-8 h-full flex items-center justify-center bg-gray-50 hover:bg-gray-200 text-gray-600 font-bold transition disabled:opacity-50">
                                            -
                                        </button>
                                        
                                        {{-- Input Angka --}}
                                        <input type="text" readonly x-model="qty" 
                                               class="w-10 h-full text-center border-none p-0 text-sm font-bold text-gray-900 focus:ring-0 cursor-default">
                                        
                                        {{-- Tombol Tambah (MATI JIKA STOK MENTOK) --}}
                                        <button type="button" @click="updateQty(qty + 1)" 
                                                :disabled="isLoading || qty >= realMax" 
                                                class="w-8 h-full flex items-center justify-center font-bold transition"
                                                :class="(qty >= realMax) ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-gray-50 hover:bg-gray-200 text-gray-600'">
                                            +
                                        </button>
                                    </div>
                                    
                                    <div class="text-right">
                                        <span class="text-sm font-bold text-gray-900" x-text="subtotal || 'Rp {{ number_format($item->harga_saat_ini * $item->jumlah, 0, ',', '.') }}'"></span>
                                    </div>
                                </div>

                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- KOLOM KANAN: TOTAL --}}
                <div class="lg:w-80 flex-shrink-0">
                    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200 sticky top-24">
                        <h2 class="text-lg font-bold text-gray-800 mb-4">Ringkasan</h2>
                        <div class="flex justify-between items-center mb-6">
                            <span class="text-gray-600 font-medium">Total Belanja</span>
                            <span class="text-2xl font-extrabold text-blue-700" x-text="grandTotalFormatted"></span>
                        </div>
                        
                        <form action="{{ route('checkout.index') }}" method="GET">
                            {{-- Tombol Lanjut Mati Jika Ada Error Stok --}}
                            <button type="submit" 
                                    :disabled="hasError"
                                    class="w-full text-white font-bold py-3 rounded-lg shadow-md transition transform flex justify-center items-center gap-2 disabled:bg-gray-400 disabled:cursor-not-allowed disabled:transform-none"
                                    :class="hasError ? 'bg-gray-400' : 'bg-blue-600 hover:bg-blue-700 hover:scale-105'">
                                <span x-text="hasError ? 'Cek Stok Dulu' : 'Lanjut Pembayaran'"></span>
                                <svg x-show="!hasError" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </button>
                        </form>
                        
                        <a href="{{ url('/') }}" class="block text-center text-sm text-gray-500 mt-4 hover:underline">Tambah Barang Lain</a>
                    </div>
                </div>

            </div>
        @else
            <div class="text-center py-20 bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="bg-gray-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
                <h2 class="text-xl font-bold text-gray-800 mb-2">Keranjang Kosong</h2>
                <a href="{{ url('/') }}" class="inline-block bg-blue-600 text-white font-bold py-2 px-6 rounded-full hover:bg-blue-700 transition shadow">Mulai Belanja</a>
            </div>
        @endif
    </div>

    <script>
        function keranjangApp() {
            return {
                grandTotalFormatted: 'Rp {{ number_format($keranjang->sum(fn($i) => $i->harga_saat_ini * $i->jumlah), 0, ',', '.') }}',
                hasError: false,
                errorCount: 0,
                updateErrorStatus(detail) {
                    if(detail.isError) { this.errorCount++; } else { this.errorCount = Math.max(0, this.errorCount - 1); }
                    this.hasError = this.errorCount > 0;
                }
            }
        }

        function cartItem(id_keranjang, id_produk, initialQty, conversionRate) {
            return {
                id: id_keranjang, 
                qty: initialQty, 
                conversion: conversionRate,
                subtotal: null, 
                isLoading: false, 
                realMax: 9999, 
                outOfStock: false,

                init() {
                    this.checkStockRealtime();
                    setInterval(() => { this.checkStockRealtime() }, 4000); // Cek setiap 4 detik
                },

                async checkStockRealtime() {
                    try {
                        // Cek Stok Pusat (PCS)
                        const res = await fetch(`/api/cek-stok/${id_produk}`); 
                        const data = await res.json();
                        
                        // Hitung Max Stok untuk Satuan Ini (Floor division)
                        this.realMax = Math.floor(data.stok / this.conversion);

                        // Cek Error
                        const wasError = this.outOfStock;
                        this.outOfStock = (this.qty > this.realMax);

                        // Lapor ke Parent jika status error berubah
                        if (wasError !== this.outOfStock) {
                            window.dispatchEvent(new CustomEvent('cart-error-status', { 
                                detail: { id: this.id, isError: this.outOfStock } 
                            }));
                        }
                    } catch(e) { console.log('Sync error'); }
                },

                async updateQty(newQty) {
                    if (newQty < 1) return;
                    
                    // VALIDASI PENTING: Jangan update jika melebihi stok real
                    if (newQty > this.realMax) {
                        alert('Stok tidak cukup! Tersedia: ' + this.realMax);
                        this.checkStockRealtime(); // Refresh data biar akurat
                        return;
                    }

                    const oldQty = this.qty; 
                    this.qty = newQty; 
                    this.isLoading = true;

                    try {
                        // Pakai URL Helper yang disuntikkan akan lebih aman, tapi format ini juga oke jika rute standar
                        // Lebih aman pakai cara sebelumnya (kirim route dari blade), tapi ini singkatnya:
                        const response = await fetch(`/keranjang/${this.id}`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                            body: JSON.stringify({ _method: 'PATCH', jumlah: newQty })
                        });
                        const result = await response.json();
                        if (result.success) {
                            this.subtotal = 'Rp ' + result.subtotal;
                            window.dispatchEvent(new CustomEvent('update-grand-total', { detail: { total: 'Rp ' + result.grand_total } }));
                            window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: result.cart_count } }));
                            this.checkStockRealtime();
                        } else { 
                            this.qty = oldQty; alert('Gagal: ' + result.message); 
                        }
                    } catch (e) { console.error(e); this.qty = oldQty; }
                    this.isLoading = false;
                }
            }
        }
    </script>

@endcomponent