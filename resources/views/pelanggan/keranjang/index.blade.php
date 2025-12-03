@component('layouts.guest_market')

    <div class="max-w-screen-xl mx-auto px-4 pb-20 pt-6"
         x-data="{ 
             grandTotalFormatted: 'Rp {{ number_format($keranjang->sum(fn($i) => $i->harga_saat_ini * $i->jumlah), 0, ',', '.') }}'
         }"
         @update-grand-total.window="grandTotalFormatted = $event.detail.total">
        
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6 border-b border-gray-200 pb-4">
            Keranjang Belanja
        </h1>

        @if($keranjang->count() > 0)
            <div class="flex flex-col lg:flex-row gap-8">
                
                {{-- KOLOM KIRI --}}
                <div class="flex-1 bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                    <ul class="divide-y divide-gray-100">
                        @foreach($keranjang as $item)
                            <li class="p-4 flex items-center gap-4 hover:bg-gray-50 transition"
                                {{-- PERBAIKAN: Kirim URL Update langsung dari PHP --}}
                                x-data="cartItem('{{ route('keranjang.update', $item->id_keranjang) }}', {{ $item->jumlah }})">
                                
                                {{-- Gambar --}}
                                <div class="w-20 h-20 flex-shrink-0 rounded-lg bg-gray-100 border border-gray-200 overflow-hidden">
                                    @if($item->produk->gambar)
                                        <img src="{{ asset('storage/' . $item->produk->gambar) }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="flex items-center justify-center h-full text-gray-300">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                    @endif
                                </div>

                                {{-- Info --}}
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-base font-bold text-gray-900 truncate">{{ $item->produk->nama_produk }}</h3>
                                    <p class="text-sm text-gray-500 mt-1">
                                        <span class="font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded text-xs mr-2">{{ $item->satuan }}</span>
                                        Rp {{ number_format($item->harga_saat_ini, 0, ',', '.') }}
                                    </p>
                                    <form action="{{ route('keranjang.destroy', $item->id_keranjang) }}" method="POST" class="mt-2">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs text-red-500 hover:text-red-700 flex items-center gap-1 transition" onclick="return confirm('Hapus?')">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            Hapus
                                        </button>
                                    </form>
                                </div>

                                {{-- Stepper & Total --}}
                                <div class="flex flex-col items-end gap-2">
                                    <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden bg-white shadow-sm h-9">
                                        <button type="button" @click="updateQty(qty - 1)" :disabled="isLoading || qty <= 1" class="w-8 h-full flex items-center justify-center bg-gray-50 hover:bg-gray-200 text-gray-600 transition font-bold disabled:opacity-50">-</button>
                                        <input type="text" readonly x-model="qty" class="w-10 h-full text-center border-none p-0 text-sm font-bold text-gray-900 focus:ring-0">
                                        <button type="button" @click="updateQty(qty + 1)" :disabled="isLoading" class="w-8 h-full flex items-center justify-center bg-gray-50 hover:bg-gray-200 text-gray-600 transition font-bold">+</button>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-sm font-bold text-gray-900" x-text="subtotal || 'Rp {{ number_format($item->harga_saat_ini * $item->jumlah, 0, ',', '.') }}'"></span>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- KOLOM KANAN: RINGKASAN --}}
                <div class="lg:w-80 flex-shrink-0">
                    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200 sticky top-24">
                        <h2 class="text-lg font-bold text-gray-800 mb-4">Ringkasan</h2>
                        <div class="flex justify-between items-center mb-6">
                            <span class="text-gray-600 font-medium">Total Belanja</span>
                            <span class="text-2xl font-extrabold text-blue-700" x-text="grandTotalFormatted"></span>
                        </div>
                        <form action="{{ route('checkout.index') }}" method="GET">
                            <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg shadow-md transition transform hover:scale-105 flex justify-center items-center gap-2">
                                Lanjut Pembayaran
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </button>
                        </form>
                        <a href="{{ url('/') }}" class="block text-center text-sm text-gray-500 mt-4 hover:underline">Tambah Barang Lain</a>
                    </div>
                </div>
            </div>
        @else
            {{-- TAMPILAN KOSONG --}}
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
        function cartItem(url, initialQty) {
            return {
                url: url, // Simpan URL Update
                qty: initialQty,
                subtotal: null,
                isLoading: false,

                async updateQty(newQty) {
                    if (newQty < 1) return;
                    
                    // Simpan nilai lama
                    const oldQty = this.qty;
                    
                    // Update UI Optimistic
                    this.qty = newQty;
                    this.isLoading = true;

                    try {
                        const response = await fetch(this.url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ _method: 'PATCH', jumlah: newQty })
                        });

                        const result = await response.json();
                        if (result.success) {
                            // Sukses: Update Tampilan Harga
                            this.subtotal = 'Rp ' + result.subtotal;
                            
                            // Update Grand Total Global
                            window.dispatchEvent(new CustomEvent('update-grand-total', { detail: { total: 'Rp ' + result.grand_total } }));
                            
                            // Update Badge Navbar
                            window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: result.cart_count } }));
                        } else {
                            // Gagal Logika: Revert
                            this.qty = oldQty;
                            alert('Gagal: ' + (result.message || 'Gagal update'));
                        }
                    } catch (e) { 
                        // Gagal Koneksi: Revert
                        console.error(e); 
                        this.qty = oldQty;
                    }
                    this.isLoading = false;
                }
            }
        }
    </script>

@endcomponent