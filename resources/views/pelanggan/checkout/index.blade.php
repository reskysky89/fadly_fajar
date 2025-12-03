@component('layouts.guest_market')

    <div class="max-w-screen-xl mx-auto px-4">
        
        {{-- Breadcrumb Sederhana --}}
        <nav class="flex text-sm text-gray-500 mb-6">
            <a href="{{ route('keranjang.index') }}" class="hover:text-blue-600">Keranjang</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900 font-bold">Checkout</span>
        </nav>

        <h1 class="text-3xl font-bold text-gray-900 mb-8">Penyelesaian Pesanan</h1>

        <form action="{{ route('checkout.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- KOLOM KIRI: FORM PENGIRIMAN --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    {{-- Informasi Kontak --}}
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Informasi Pengiriman</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Penerima</label>
                                <input type="text" value="{{ Auth::user()->nama }}" class="w-full bg-gray-100 border-gray-300 rounded-lg text-sm cursor-not-allowed" disabled>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">No. WhatsApp</label>
                                <input type="text" value="{{ Auth::user()->kontak }}" class="w-full bg-gray-100 border-gray-300 rounded-lg text-sm cursor-not-allowed" disabled>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Alamat Lengkap</label>
                            <textarea name="alamat_pengiriman" rows="3" required
                                    class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm"
                                    placeholder="Nama Jalan, No. Rumah, Patokan, Kecamatan..."></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Tambahan (Opsional)</label>
                            <input type="text" name="catatan" 
                                   class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm"
                                   placeholder="Misal: Tolong dipacking kardus tebal...">
                        </div>
                    </div>

                    {{-- Metode Pembayaran --}}
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Metode Pembayaran</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Opsi Transfer --}}
                            <label class="cursor-pointer">
                                <input type="radio" name="metode_bayar" value="transfer" class="peer sr-only" required>
                                <div class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-blue-600 peer-checked:bg-blue-50 hover:bg-gray-50 transition">
                                    <div class="flex items-center gap-3">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                        <span class="font-bold text-gray-800">Transfer Bank</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1 ml-9">Kirim bukti transfer via WhatsApp setelah pesan.</p>
                                </div>
                            </label>

                            {{-- Opsi Cash/COD --}}
                            <label class="cursor-pointer">
                                <input type="radio" name="metode_bayar" value="cash" class="peer sr-only">
                                <div class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-green-600 peer-checked:bg-green-50 hover:bg-gray-50 transition">
                                    <div class="flex items-center gap-3">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        <span class="font-bold text-gray-800">Bayar di Toko / COD</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1 ml-9">Bayar tunai saat barang diterima/diambil.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                </div>

                {{-- KOLOM KANAN: RINGKASAN PESANAN --}}
                <div class="lg:col-span-1">
                    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200 sticky top-24">
                        <h2 class="text-lg font-bold text-gray-800 mb-4">Ringkasan Pesanan</h2>
                        
                        <div class="space-y-3 mb-6 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                            @foreach($keranjang as $item)
                                <div class="flex justify-between text-sm">
                                    <div>
                                        <span class="font-bold text-gray-700">{{ $item->jumlah }} x</span> {{ $item->produk->nama_produk }}
                                        <div class="text-xs text-gray-500">{{ $item->satuan }}</div>
                                    </div>
                                    <div class="font-semibold text-gray-900">
                                        Rp {{ number_format($item->harga_saat_ini * $item->jumlah, 0, ',', '.') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="border-t border-gray-200 pt-4">
                            <div class="flex justify-between items-center text-lg font-bold text-gray-900">
                                <span>Total Bayar</span>
                                <span class="text-blue-700">Rp {{ number_format($keranjang->sum(fn($i)=>$i->harga_saat_ini*$i->jumlah), 0, ',', '.') }}</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1 text-right">*Belum termasuk ongkir (jika ada)</p>
                        </div>

                        <button type="submit" class="w-full mt-6 bg-blue-700 hover:bg-blue-800 text-white font-bold py-3.5 rounded-lg shadow-lg transition transform hover:-translate-y-1 flex justify-center items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Buat Pesanan Sekarang
                        </button>

                        <p class="text-xs text-gray-400 mt-4 text-center">
                            Dengan mengklik tombol di atas, Anda setuju dengan syarat & ketentuan kami.
                        </p>
                    </div>
                </div>

            </div>
        </form>
    </div>

@endcomponent