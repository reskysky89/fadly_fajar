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
                            <div x-show="inCart" 
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 scale-50"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 class="absolute top-2 left-2 z-20">
                                <div class="bg-blue-600 text-white p-1.5 rounded-full shadow-lg border-2 border-white flex items-center justify-center" title="Barang ini ada di keranjang Anda">
                                    {{-- Ikon Keranjang + Centang --}}
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                            {{-- -------------------------------------------------------------- --}}

                            @if($produk->gambar)
                                <img src="{{ asset($produk->gambar) }}" alt="{{ $produk->nama_produk }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300"><svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg></div>
                            @endif

                            {{-- INFO STOK (Diperkecil sedikit fontnya untuk Mobile) --}}
                            <div class="absolute top-2 right-2 z-10 max-w-[70%] text-right">
                                <span x-show="selected.stock_display > 0" 
                                      class="inline-block bg-green-500 text-white text-[10px] md:text-xs font-bold px-2 py-1 rounded shadow-sm bg-opacity-90 backdrop-blur-sm">
                                    Stok: <span x-text="selected.stock_display"></span> <span x-text="selected.name"></span>
                                </span>
                                <span x-show="selected.stock_display <= 0" 
                                      class="inline-block bg-red-500 text-white text-[10px] md:text-xs font-bold px-2 py-1 rounded shadow-sm bg-opacity-90 backdrop-blur-sm">
                                    Habis
                                </span>
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
    
    @if($ulasanTerbaru->count() > 0)
        <div class="mt-16 mb-8">
            <div class="text-center mb-10">
                <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900">Apa Kata Mereka?</h2>
                <p class="text-gray-500 mt-2">Pengalaman belanja asli dari pelanggan setia Toko Fadly Fajar.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($ulasanTerbaru as $review)
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition duration-300 flex flex-col h-full">
                        
                        {{-- Header: User & Rating --}}
                        <div class="flex items-center gap-4 mb-4">
                            {{-- Avatar Inisial --}}
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                                {{ substr($review->user->nama, 0, 1) }}
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 text-sm">{{ $review->user->nama }}</h4>
                                <div class="flex text-yellow-400 text-xs mt-0.5">
                                    @for($i=1; $i<=5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= $review->rating ? 'fill-current' : 'text-gray-200 fill-current' }}" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    @endfor
                                </div>
                            </div>
                        </div>

                        {{-- Isi Komentar --}}
                        <div class="flex-1">
                            <svg class="w-8 h-8 text-gray-200 mb-2 opacity-50" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21L14.017 18C14.017 16.8954 14.9124 16 16.017 16H19.017C19.5693 16 20.017 15.5523 20.017 15V9C20.017 8.44772 19.5693 8 19.017 8H15.017C14.4647 8 14.017 8.44772 14.017 9V11C14.017 11.5523 13.5693 12 13.017 12H12.017V5H22.017V15C22.017 16.6569 20.6739 18 19.017 18H16.017C14.9124 18 14.017 18.8954 14.017 20V21zM5.0166 21L5.0166 18C5.0166 16.8954 5.91203 16 7.0166 16H10.0166C10.5689 16 11.0166 15.5523 11.0166 15V9C11.0166 8.44772 10.5689 8 10.0166 8H6.0166C5.46432 8 5.0166 8.44772 5.0166 9V11C5.0166 11.5523 4.56889 12 4.0166 12H3.0166V5H13.0166V15C13.0166 16.6569 11.6735 18 10.0166 18H7.0166C5.91203 18 5.0166 18.8954 5.0166 20V21z"/></svg>
                            <p class="text-gray-600 text-sm italic leading-relaxed">
                                "{{ $review->komentar ?? 'Barang bagus, pengiriman cepat!' }}"
                            </p>
                        </div>

                        {{-- Footer: Produk & Tanggal --}}
                        <div class="mt-4 pt-4 border-t border-gray-50 flex justify-between items-center text-xs">
                            <span class="text-gray-400">{{ $review->created_at->diffForHumans() }}</span>
                            {{-- Kita tampilkan ID Transaksi karena ini review transaksi --}}
                            <span class="text-blue-600 font-bold bg-blue-50 px-2 py-1 rounded">
                                #{{ $review->id_transaksi }}
                            </span>
                        </div>

                    </div>
                @endforeach
            </div>
        </div>
    @endif

@endcomponent