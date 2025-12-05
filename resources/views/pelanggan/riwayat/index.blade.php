@component('layouts.guest_market')

    <div class="max-w-screen-xl mx-auto px-4 py-10 min-h-screen" 
    
         x-data="{ activeTab: 'diproses' }"> {{-- Default Tab: Diproses --}}
        {{-- BUMBU PELENGKAP: TOMBOL KEMBALI --}}
        <div class="mb-6">
            <a href="{{ route('home') }}" class="inline-flex items-center text-gray-500 hover:text-blue-600 font-bold transition duration-200 group text-sm">
                {{-- Ikon Panah dalam Lingkaran --}}
                <div class="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center mr-2 shadow-sm group-hover:bg-blue-50 group-hover:border-blue-200 group-hover:shadow transition-all">
                    <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </div>
                Kembali ke Beranda
            </a>
        </div>
        
        <h1 class="text-2xl font-extrabold text-gray-900 mb-6 flex items-center gap-3">
            <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            Riwayat Pesanan Saya
        </h1>

        @if(session('success'))
            <div class="p-4 mb-6 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200 shadow-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="p-4 mb-6 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200 shadow-sm">{{ session('error') }}</div>
        @endif

        {{-- TAB NAVIGATION --}}
        <div class="flex space-x-2 mb-6 overflow-x-auto pb-2 border-b border-gray-200">
            <button @click="activeTab = 'diproses'" 
                    :class="activeTab === 'diproses' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-50'"
                    class="px-6 py-2.5 rounded-full font-bold text-sm transition-all flex items-center gap-2 whitespace-nowrap">
                <span class="w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></span>
                Diproses
            </button>
            
            <button @click="activeTab = 'selesai'" 
                    :class="activeTab === 'selesai' ? 'bg-green-600 text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-50'"
                    class="px-6 py-2.5 rounded-full font-bold text-sm transition-all flex items-center gap-2 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Selesai
            </button>

            <button @click="activeTab = 'batal'" 
                    :class="activeTab === 'batal' ? 'bg-red-600 text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-50'"
                    class="px-6 py-2.5 rounded-full font-bold text-sm transition-all flex items-center gap-2 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                Dibatalkan
            </button>
        </div>

        {{-- KONTEN RIWAYAT --}}
        <div class="space-y-6">
            
            {{-- LOOPING DATA --}}
            @foreach($riwayat as $trx)
                {{-- Tentukan Tab mana item ini muncul --}}
                <div x-show="activeTab === '{{ $trx->status_pesanan }}'" x-transition.opacity.duration.300ms>
                    
                    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition duration-300">
                        
                        {{-- Header Kartu --}}
                        <div class="bg-gray-50 px-6 py-3 border-b border-gray-100 flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-gray-500">#{{ $trx->id_transaksi }}</span>
                                <span class="text-xs text-gray-400 hidden md:inline">• {{ \Carbon\Carbon::parse($trx->waktu_transaksi)->setTimezone('Asia/Makassar')->format('d M Y, H:i') }} WITA</span>
                            </div>
                            <div class="font-extrabold text-blue-700">Rp {{ number_format($trx->total_harga, 0, ',', '.') }}</div>
                        </div>

                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            {{-- Kiri: Barang --}}
                            <div>
                                <h4 class="text-xs font-bold text-gray-400 uppercase mb-3">Barang Dipesan</h4>
                                <ul class="space-y-2">
                                    @foreach($trx->details as $item)
                                        <li class="flex items-start gap-3 text-sm">
                                            <div class="w-10 h-10 bg-gray-100 rounded flex-shrink-0 overflow-hidden">
                                                @if($item->produk && $item->produk->gambar)
                                                    <img src="{{ asset('storage/' . $item->produk->gambar) }}" class="w-full h-full object-cover">
                                                @endif
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-800 line-clamp-1">{{ $item->produk->nama_produk ?? 'Produk Dihapus' }}</p>
                                                <p class="text-xs text-gray-500">{{ $item->jumlah }} {{ $item->satuan }}</p>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            {{-- Kanan: Info & Aksi --}}
                            <div class="flex flex-col justify-between items-start md:items-end">
                                <div class="mb-4 text-left md:text-right">
                                    @if($trx->metode_pengiriman == 'ambil_sendiri')
                                        <span class="text-xs font-bold text-orange-600 bg-orange-50 px-2 py-1 rounded border border-orange-100">Ambil di Toko</span>
                                    @else
                                        <span class="text-xs font-bold text-purple-600 bg-purple-50 px-2 py-1 rounded border border-purple-100">Diantar Kurir</span>
                                    @endif
                                    <p class="text-xs text-gray-400 mt-1">{{ $trx->metode_bayar == 'cash' ? 'Bayar Tunai (COD)' : 'Transfer Bank' }}</p>
                                </div>

                                {{-- TOMBOL AKSI BERDASARKAN STATUS --}}
                                <div class="w-full md:w-auto">
                                    
                                    @if($trx->status_pesanan == 'diproses')
                                        {{-- Tombol Batal (Hanya di Tab Diproses) --}}
                                        <form action="{{ route('pelanggan.riwayat.batal', $trx->id_transaksi) }}" method="POST" onsubmit="return confirm('Yakin ingin membatalkan pesanan ini?')">
                                            @csrf @method('PUT')
                                            <button type="submit" class="w-full md:w-auto px-4 py-2 bg-white border border-red-200 text-red-600 text-sm font-bold rounded-lg hover:bg-red-50 transition flex items-center justify-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                Batalkan Pesanan
                                            </button>
                                        </form>
                                        <p class="text-xs text-gray-400 mt-2 text-center md:text-right">Menunggu konfirmasi Admin.</p>
                                    
                                    @elseif($trx->status_pesanan == 'selesai')
                                        {{-- Tombol Struk (Hanya di Tab Selesai) --}}
                                        <a href="{{ route('kasir.transaksi.cetak', ['id' => $trx->id_transaksi]) }}" target="_blank" 
                                           class="w-full md:w-auto px-5 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-lg hover:bg-blue-700 transition flex items-center justify-center gap-2 shadow-md">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                            Cetak Nota
                                        </a>
                                    
                                    @elseif($trx->status_pesanan == 'batal')
                                        <span class="text-sm font-bold text-gray-400">Pesanan telah dibatalkan.</span>
                                    @endif

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach

            {{-- PESAN JIKA KOSONG --}}
            @if($riwayat->where('status_pesanan', 'diproses')->isEmpty())
                <div x-show="activeTab === 'diproses'" class="text-center py-10 text-gray-400">Tidak ada pesanan sedang diproses.</div>
            @endif
            @if($riwayat->where('status_pesanan', 'selesai')->isEmpty())
                <div x-show="activeTab === 'selesai'" class="text-center py-10 text-gray-400">Belum ada pesanan selesai.</div>
            @endif
            @if($riwayat->where('status_pesanan', 'batal')->isEmpty())
                <div x-show="activeTab === 'batal'" class="text-center py-10 text-gray-400">Tidak ada riwayat pembatalan.</div>
            @endif
            
        </div>

    </div>

@endcomponent