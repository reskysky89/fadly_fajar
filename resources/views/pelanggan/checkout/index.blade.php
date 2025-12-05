@component('layouts.guest_market')

    <div class="max-w-screen-xl mx-auto px-4 py-10">
        
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="{{ route('keranjang.index') }}" class="hover:text-blue-600">Keranjang</a>
            <span>/</span>
            <span class="font-bold text-gray-900">Checkout</span>
        </div>
        
        <h1 class="text-3xl font-extrabold text-gray-900 mb-8">Pengiriman & Pembayaran</h1>

        {{-- PENAMPIL ERROR --}}
        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded shadow-sm">
                <p class="font-bold">Gagal Memproses Pesanan:</p>
                <ul class="list-disc list-inside text-sm mt-1">
                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('checkout.store') }}" method="POST" x-data="{ deliveryMethod: 'diantar' }">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- KOLOM KIRI: FORM DATA --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    {{-- 1. Metode Pengiriman --}}
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Metode Pengambilan Barang
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="cursor-pointer">
                                <input type="radio" name="metode_pengiriman" value="diantar" x-model="deliveryMethod" class="peer sr-only">
                                <div class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-blue-600 peer-checked:bg-blue-50 hover:bg-gray-50 transition">
                                    <div class="flex items-center gap-3">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                                        <div>
                                            <span class="font-bold text-gray-900 block">Diantar Kurir Toko</span>
                                            <span class="text-xs text-gray-500">Barang dikirim ke alamat Anda</span>
                                        </div>
                                    </div>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="metode_pengiriman" value="ambil_sendiri" x-model="deliveryMethod" class="peer sr-only">
                                <div class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-green-600 peer-checked:bg-green-50 hover:bg-gray-50 transition">
                                    <div class="flex items-center gap-3">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                        <div>
                                            <span class="font-bold text-gray-900 block">Ambil di Toko (Pick Up)</span>
                                            <span class="text-xs text-gray-500">Saya akan datang mengambil barang</span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- 2. ALAMAT --}}
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        
                        {{-- Form Alamat (Muncul jika Diantar) --}}
                        <div x-show="deliveryMethod === 'diantar'" x-transition>
                            <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                Alamat Tujuan <span class="text-red-500">*</span>
                            </h2>
                            <div class="mb-4">
                                {{-- 
                                    INI BAGIAN PENTINGNYA: 
                                    Isi textarea dengan {{ Auth::user()->alamat }} 
                                --}}
                                <textarea name="alamat_pengiriman" rows="3" 
                                          :required="deliveryMethod === 'diantar'"
                                          class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm"
                                          placeholder="Jalan, Nomor Rumah, RT/RW, Patokan...">{{ Auth::user()->alamat }}</textarea>

                                @if(!Auth::user()->alamat)
                                    <p class="text-xs text-orange-500 mt-2 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Tips: Isi alamat di menu Profil agar otomatis muncul di sini.
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- Info Ambil Sendiri --}}
                        <div x-show="deliveryMethod === 'ambil_sendiri'" x-transition class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">
                            <strong>📍 Lokasi Pengambilan:</strong><br>
                            Toko Grosir Fadly Fajar<br>
                            Jl. Contoh No. 123, Kota Parepare<br>
                        </div>

                        {{-- Data Diri (Readonly) --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Penerima</label>
                                <input type="text" value="{{ Auth::user()->nama }}" class="w-full bg-gray-50 border-gray-300 rounded-lg text-sm" disabled>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">No. WhatsApp</label>
                                <input type="text" value="{{ Auth::user()->kontak ?? '-' }}" class="w-full bg-gray-50 border-gray-300 rounded-lg text-sm" disabled>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Tambahan (Opsional)</label>
                            <input type="text" name="catatan" 
                                   class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm"
                                   placeholder="Contoh: Titip di pos satpam">
                        </div>
                    </div>

                    {{-- 3. Metode Pembayaran --}}
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        <h2 class="text-lg font-bold text-gray-800 mb-4">Metode Pembayaran</h2>
                        <div class="space-y-3">
                            <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-blue-50 transition has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50">
                                <input type="radio" name="metode_bayar" value="cash" class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500" checked>
                                <div class="ml-3">
                                    <span class="block text-sm font-bold text-gray-900">Bayar di Toko / COD</span>
                                    <span class="block text-xs text-gray-500" x-text="deliveryMethod === 'diantar' ? 'Bayar saat barang sampai.' : 'Bayar saat ambil barang.'"></span>
                                </div>
                            </label>
                            <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-blue-50 transition has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50">
                                <input type="radio" name="metode_bayar" value="transfer" class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500">
                                <div class="ml-3">
                                    <span class="block text-sm font-bold text-gray-900">Transfer Bank</span>
                                    <span class="block text-xs text-gray-500">Kirim bukti transfer setelah checkout.</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- KOLOM KANAN: RINGKASAN --}}
                <div class="lg:col-span-1">
                    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200 sticky top-24">
                        <h2 class="text-lg font-bold text-gray-800 mb-4">Ringkasan Pesanan</h2>
                        <div class="space-y-3 mb-6 max-h-60 overflow-y-auto pr-1 custom-scrollbar">
                            @foreach($keranjang as $item)
                                <div class="flex justify-between text-sm">
                                    <div>
                                        <span class="font-bold">{{ $item->jumlah }}x</span> {{ $item->produk->nama_produk }}
                                        <div class="text-xs text-gray-500">{{ $item->satuan }}</div>
                                    </div>
                                    <div class="font-medium">
                                        Rp {{ number_format($item->harga_saat_ini * $item->jumlah, 0, ',', '.') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="border-t border-dashed border-gray-300 my-4 pt-4">
                            <div class="flex justify-between items-center text-lg font-extrabold text-gray-900">
                                <span>Total Bayar</span>
                                <span class="text-blue-700">Rp {{ number_format($keranjang->sum(fn($i)=>$i->harga_saat_ini*$i->jumlah), 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <button type="submit" class="w-full mt-6 bg-blue-700 hover:bg-blue-800 text-white font-bold py-3.5 rounded-lg shadow-lg transition transform hover:-translate-y-1 flex justify-center items-center gap-2">
                            Buat Pesanan
                        </button>
                    </div>
                </div>

            </div>
        </form>
    </div>

@endcomponent