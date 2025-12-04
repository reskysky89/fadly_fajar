@component('layouts.guest_market')

    <div class="max-w-screen-xl mx-auto px-4 py-10 min-h-screen">
        
        <h1 class="text-2xl font-extrabold text-gray-900 mb-8 flex items-center gap-3">
            <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            Riwayat Pesanan Saya
        </h1>

        @if(session('success'))
            <div class="p-4 mb-6 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200 flex items-center gap-2 shadow-sm">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        @if($riwayat->count() > 0)
            <div class="grid gap-6">
                @foreach($riwayat as $trx)
                    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition duration-300">
                        
                        {{-- HEADER: ID & STATUS (Simple) --}}
                        <div class="bg-gray-50 px-6 py-3 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-3">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-gray-500">#{{ $trx->id_transaksi }}</span>
                                <span class="text-xs text-gray-400">• {{ \Carbon\Carbon::parse($trx->waktu_transaksi)->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}</span>
                            </div>
                            
                            {{-- STATUS BADGE --}}
                            <div>
                                @if($trx->status_pesanan == 'diproses')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700">
                                        <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full animate-pulse"></span> Diproses
                                    </span>
                                @elseif($trx->status_pesanan == 'selesai')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        Selesai
                                    </span>
                                @elseif($trx->status_pesanan == 'batal')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">Dibatalkan</span>
                                @endif
                            </div>
                        </div>

                        <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-8">
                            
                            {{-- KOLOM 1: BARANG --}}
                            <div class="lg:col-span-2">
                                <ul class="divide-y divide-gray-50">
                                    @foreach($trx->details as $item)
                                        <li class="py-3 first:pt-0 last:pb-0 flex items-start gap-4">
                                            {{-- Gambar Kecil --}}
                                            <div class="w-12 h-12 bg-gray-100 rounded-md overflow-hidden flex-shrink-0 border border-gray-200">
                                                @if($item->produk->gambar)
                                                    <img src="{{ asset('storage/' . $item->produk->gambar) }}" class="w-full h-full object-cover">
                                                @else
                                                    <div class="flex items-center justify-center h-full text-gray-300"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg></div>
                                                @endif
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-sm font-bold text-gray-800 line-clamp-1">{{ $item->produk->nama_produk }}</p>
                                                <p class="text-xs text-gray-500">{{ $item->jumlah }} {{ $item->satuan }} x Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</p>
                                            </div>
                                            <p class="text-sm font-bold text-gray-700">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="mt-4 pt-3 border-t border-gray-100 flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-500">Total Belanja</span>
                                    <span class="text-lg font-extrabold text-blue-700">Rp {{ number_format($trx->total_harga, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            {{-- KOLOM 2: INFO PENGIRIMAN & BAYAR (Clean & Elegant) --}}
                            <div class="flex flex-col gap-4 text-sm">
                                
                                {{-- INFO PENGIRIMAN --}}
                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                                    @if($trx->metode_pengiriman == 'diantar')
                                        <div class="flex items-start gap-3">
                                            <div class="bg-purple-100 text-purple-600 p-1.5 rounded-md">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-800">Diantar Kurir</p>
                                                {{-- Parsing Alamat --}}
                                                @php
                                                    $parts = explode('|', $trx->keterangan);
                                                    $alamat = str_replace('Alamat:', '', $parts[0] ?? '-');
                                                @endphp
                                                <p class="text-xs text-gray-500 mt-1 leading-relaxed">{{ $alamat }}</p>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex items-start gap-3">
                                            <div class="bg-orange-100 text-orange-600 p-1.5 rounded-md">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-800">Ambil di Toko</p>
                                                <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                                                    Silakan ambil barang di:<br>
                                                    <strong class="text-gray-700">Toko Fadly Fajar</strong><br>
                                                    Jl. Contoh No. 123, Parepare
                                                </p>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- INFO PEMBAYARAN --}}
                                <div class="flex items-center justify-between px-4 py-3 bg-white border border-gray-200 rounded-lg">
                                    <span class="text-gray-500">Metode Bayar</span>
                                    @if($trx->metode_bayar == 'cash')
                                        <span class="font-bold text-green-700 bg-green-50 px-2 py-1 rounded text-xs">COD / Tunai</span>
                                    @else
                                        <span class="font-bold text-blue-700 bg-blue-50 px-2 py-1 rounded text-xs">Transfer Bank</span>
                                    @endif
                                </div>

                                {{-- TOMBOL STRUK --}}
                                @if($trx->status_pesanan == 'selesai')
                                    <a href="{{ route('kasir.transaksi.cetak', ['id' => $trx->id_transaksi]) }}" target="_blank" 
                                       class="flex items-center justify-center gap-2 w-full bg-gray-900 hover:bg-black text-white font-bold py-2.5 rounded-lg transition text-sm shadow-md">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        Cetak Struk
                                    </a>
                                @endif

                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $riwayat->links() }}
            </div>
        @else
            <div class="text-center py-20">
                <div class="bg-gray-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">Belum Ada Pesanan</h3>
                <a href="{{ url('/') }}" class="text-blue-600 font-bold hover:underline">Mulai Belanja</a>
            </div>
        @endif
    </div>

@endcomponent