{{-- Panggil Layout Guest Market --}}
@component('layouts.guest_market')

    {{-- 1. BANNER --}}
    <div class="bg-gradient-to-r from-blue-700 to-blue-500 text-white py-12 mb-4 shadow-md">
        <div class="max-w-screen-xl mx-auto px-4 text-center">
            <h1 class="text-3xl md:text-5xl font-extrabold mb-4 tracking-tight">Belanja Grosir Lebih Mudah</h1>
            <p class="text-lg text-blue-100 mb-8 max-w-2xl mx-auto">Dapatkan harga terbaik, cek stok real-time, dan belanja kebutuhan Anda langsung dari sini.</p>
            
            {{-- Search Bar Mobile --}}
            <div class="md:hidden max-w-md mx-auto relative">
                <form action="{{ url('/') }}" method="GET" class="flex items-center">
                    <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="w-full py-3 pl-10 pr-12 text-gray-900 rounded-full border-none focus:ring-4 focus:ring-blue-300 shadow-lg" 
                           placeholder="Cari beras, minyak, gula...">
                    <button type="submit" class="absolute right-1 top-1 bg-blue-800 text-white p-2 rounded-full hover:bg-blue-900 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- 2. KATEGORI --}}
    <div class="max-w-screen-xl mx-auto px-4 mb-8">
        <div class="flex items-center space-x-3 overflow-x-auto pb-4 no-scrollbar">
            <a href="{{ url('/') }}" class="flex-shrink-0 px-5 py-2 rounded-full text-sm font-bold transition-colors {{ !request('kategori') ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">Semua</a>
            @foreach($kategoris as $kat)
                <a href="{{ url('/?kategori=' . $kat->id_kategori) }}" class="flex-shrink-0 px-5 py-2 rounded-full text-sm font-bold transition-colors {{ request('kategori') == $kat->id_kategori ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                    {{ $kat->nama_kategori }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- 3. KATALOG PRODUK --}}
    <div class="max-w-screen-xl mx-auto px-4 pb-12">
        <div class="flex justify-between items-end mb-6 border-b border-gray-200 pb-3">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">
                    @if(request('kategori')) Kategori: <span class="text-blue-600">{{ $kategoris->find(request('kategori'))->nama_kategori ?? 'Terpilih' }}</span>
                    @elseif(request('search')) Hasil Cari: <span class="text-blue-600">"{{ request('search') }}"</span>
                    @else Produk Terbaru @endif
                </h2>
                <p class="text-sm text-gray-500 mt-1">Menampilkan {{ $produks->count() }} barang tersedia</p>
            </div>
        </div>

        @if($produks->count() > 0)
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                @foreach($produks as $produk)
                    <div class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col overflow-hidden group"
                         x-data="{ 
                             units: {{ json_encode($produk->units_list) }},
                             selected: {{ json_encode($produk->units_list[0]) }},
                             rawStock: {{ $produk->stok_ready }}, // Stok awal (PCS)
                             isAdding: false,
                             inCart: {{ in_array($produk->id_produk, $cartProductIds ?? []) ? 'true' : 'false' }},

                             // --- FUNGSI AUTO UPDATE (CCTV) ---
                             init() {
                                 // Cek stok setiap 5 detik (biar tidak memberatkan server)
                                 setInterval(() => { this.syncStock() }, 5000);
                             },

                             async syncStock() {
                                 try {
                                     const res = await fetch('/api/cek-stok/{{ $produk->id_produk }}');
                                     const data = await res.json();
                                     this.rawStock = data.stok; // Update Stok Pusat
                                     this.calculateDisplay();   // Hitung ulang tampilan
                                 } catch(e) { console.log('Gagal sync stok'); }
                             },

                             // Hitung ulang stok berdasarkan satuan yang dipilih (Misal: Sisa 50 PCS -> Pilih DUS isi 10 -> Tampil 5)
                             calculateDisplay() {
                                 let konversi = this.selected.conversion || 1;
                                 // Update angka stok di tampilan
                                 this.selected.stock_display = Math.floor(this.rawStock / konversi);
                             },
                             
                             changeUnit(e) { 
                                 const idx = e.target.selectedIndex; 
                                 this.selected = this.units[idx]; 
                                 this.calculateDisplay(); // Hitung ulang saat ganti satuan
                             },
                             // --------------------------------

                             formatRupiah(angka) { return new Intl.NumberFormat('id-ID').format(angka); },

                             async addToCart(item) {
                                 // ... (Isi fungsi addToCart sama seperti sebelumnya, tidak berubah) ...
                                 this.isAdding = true;
                                 try {
                                     const response = await fetch('{{ route('keranjang.tambah') }}', {
                                         method: 'POST',
                                         headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                         body: JSON.stringify({ id_produk: '{{ $produk->id_produk }}', satuan: item.name, harga: item.price })
                                     });
                                     const result = await response.json();
                                     if (response.ok) {
                                         window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: result.cart_count } }));
                                         this.inCart = true; 
                                     } else { alert('Gagal: ' + result.message); }
                                 } catch (error) { console.error(error); }
                                 this.isAdding = false;
                             }
                         }">
                        
                        {{-- Gambar Produk --}}
                        <div class="h-48 w-full bg-gray-50 relative overflow-hidden">
                            
                            {{-- BADGE: SUDAH DI KERANJANG (Sekarang pakai x-show, jadi reaktif) --}}
                            <div x-show="inCart" x-transition class="absolute top-2 left-2 z-10">
                                <span class="bg-blue-600 text-white text-xs font-bold px-2 py-1 rounded shadow flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    Di Keranjang
                                </span>
                            </div>
                            {{-- -------------------------------------------------------------- --}}

                            @if($produk->gambar)
                                <img src="{{ asset('storage/' . $produk->gambar) }}" alt="{{ $produk->nama_produk }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300"><svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg></div>
                            @endif

                            <div class="absolute top-2 right-2">
                                <span x-show="selected.stock_display > 0" class="bg-green-500 text-white text-xs font-bold px-2 py-1 rounded shadow">Stok: <span x-text="selected.stock_display"></span> <span x-text="selected.name"></span></span>
                                <span x-show="selected.stock_display <= 0" class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded shadow">Habis</span>
                            </div>
                        </div>

                        {{-- Info Produk --}}
                        <div class="p-4 flex-1 flex flex-col">
                            <div class="text-xs text-gray-500 mb-1 uppercase tracking-wide font-semibold">{{ $produk->kategori->nama_kategori ?? 'Umum' }}</div>
                            <h3 class="text-gray-900 font-bold text-base leading-tight mb-3 line-clamp-2 h-10" title="{{ $produk->nama_produk }}">{{ $produk->nama_produk }}</h3>
                            <div class="mt-auto space-y-3">
                                <div class="flex items-baseline">
                                    <span class="text-xs font-medium text-gray-500 mr-1">Rp</span>
                                    <span class="text-xl font-extrabold text-blue-700" x-text="formatRupiah(selected.price)"></span>
                                    <span class="text-xs text-gray-400 ml-1" x-text="'/' + selected.name"></span>
                                </div>

                                <div x-show="units.length > 1" class="relative">
                                    <select @change="changeUnit($event)" class="block w-full py-1.5 px-2 text-xs text-gray-700 border border-gray-300 rounded bg-gray-50 focus:ring-blue-500 focus:border-blue-500 font-medium">
                                        <template x-for="unit in units" :key="unit.name"><option :value="unit.name" x-text="unit.name"></option></template>
                                    </select>
                                </div>

                                <div>
                                    <template x-if="selected.stock_display > 0">
                                        @auth
                                            <button type="button" @click="addToCart(selected)" :disabled="isAdding" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-bold rounded-lg text-sm px-5 py-2.5 text-center flex items-center justify-center gap-2 shadow-md transition-all" :class="{'opacity-75 cursor-wait': isAdding}">
                                                <svg x-show="isAdding" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                <svg x-show="!isAdding" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                                <span x-text="isAdding ? 'Menambahkan...' : 'Masuk Keranjang'"></span>
                                            </button>
                                        @else
                                            <a href="{{ route('login') }}" class="w-full text-blue-600 bg-white border border-blue-600 hover:bg-blue-50 font-bold rounded-lg text-sm px-4 py-2 block text-center transition">Login untuk Beli</a>
                                        @endauth
                                    </template>
                                    <template x-if="selected.stock_display <= 0">
                                        <button disabled class="w-full text-gray-400 bg-gray-100 border border-gray-200 cursor-not-allowed font-bold rounded-lg text-sm px-4 py-2">Stok Habis</button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-10">{{ $produks->links() }}</div>
        @else
            <div class="text-center py-20">
                <div class="bg-gray-100 rounded-full p-6 w-24 h-24 mx-auto mb-4 flex items-center justify-center">
                    <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Tidak ada produk.</h3>
                <p class="text-gray-500">Coba kategori lain atau reset filter.</p>
                <a href="{{ url('/') }}" class="inline-block mt-4 text-blue-600 hover:underline font-semibold">Lihat Semua</a>
            </div>
        @endif
    </div>
@endcomponent