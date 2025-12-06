@component('layouts.guest_market')

    <div class="max-w-screen-xl mx-auto px-4 py-10 min-h-screen" 
         x-data="{ activeTab: 'diproses', reviewModalOpen: false, trxId: null, rating: 5 }">

        {{-- TOMBOL KEMBALI --}}
        <div class="mb-6">
            <a href="{{ route('home') }}" class="inline-flex items-center text-gray-500 hover:text-blue-600 font-bold transition duration-200 group text-sm">
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
        <div class="flex space-x-2 mb-8 overflow-x-auto pb-2 border-b border-gray-200 no-scrollbar">
            <button @click="activeTab = 'diproses'" 
                    :class="activeTab === 'diproses' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200'"
                    class="px-5 py-2.5 rounded-full font-bold text-sm transition-all flex items-center gap-2 whitespace-nowrap">
                <span class="w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></span>
                Diproses
            </button>
            <button @click="activeTab = 'selesai'" 
                    :class="activeTab === 'selesai' ? 'bg-green-600 text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200'"
                    class="px-5 py-2.5 rounded-full font-bold text-sm transition-all flex items-center gap-2 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Selesai
            </button>
            <button @click="activeTab = 'batal'" 
                    :class="activeTab === 'batal' ? 'bg-red-600 text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200'"
                    class="px-5 py-2.5 rounded-full font-bold text-sm transition-all flex items-center gap-2 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                Dibatalkan
            </button>
        </div>

        {{-- KONTEN DAFTAR PESANAN --}}
        <div class="space-y-6">
            @forelse($riwayat as $trx)
                <div x-show="activeTab === '{{ $trx->status_pesanan }}'" x-transition.opacity.duration.300ms>
                    
                    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition duration-300">
                        
                        {{-- HEADER --}}
                        <div class="bg-gray-50 px-6 py-3 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-3">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-gray-600">#{{ $trx->id_transaksi }}</span>
                                <span class="text-xs text-gray-400 hidden sm:inline">• {{ $trx->created_at->format('d M Y, H:i') }} WITA</span>
                            </div>
                            <div>
                                @if($trx->status_pesanan == 'diproses')
                                    <span class="text-xs font-bold text-yellow-600 bg-yellow-50 px-2 py-1 rounded border border-yellow-200">Menunggu Konfirmasi</span>
                                @elseif($trx->status_pesanan == 'selesai')
                                    <span class="text-xs font-bold text-green-600 bg-green-50 px-2 py-1 rounded border border-green-200">Selesai</span>
                                @elseif($trx->status_pesanan == 'batal')
                                    <span class="text-xs font-bold text-red-600 bg-red-50 px-2 py-1 rounded border border-red-200">Dibatalkan</span>
                                @endif
                            </div>
                        </div>

                        <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-8">
                            
                            {{-- KOLOM 1: DAFTAR BARANG (FIX IMAGE) --}}
                            <div class="lg:col-span-2">
                                <ul class="divide-y divide-gray-50">
                                    @foreach($trx->details as $item)
                                        <li class="py-3 first:pt-0 last:pb-0 flex items-start gap-4">
                                            <div class="w-12 h-12 bg-gray-100 rounded-md overflow-hidden flex-shrink-0 border border-gray-200">
                                                @if($item->produk && $item->produk->gambar)
                                                    {{-- PERBAIKAN IMAGE: asset() langsung ke path public/uploads --}}
                                                    <img src="{{ asset($item->produk->gambar) }}" class="w-full h-full object-cover">
                                                @else
                                                    <div class="flex items-center justify-center h-full text-gray-300"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg></div>
                                                @endif
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-sm font-bold text-gray-800 line-clamp-1">{{ $item->produk->nama_produk ?? 'Item Dihapus' }}</p>
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

                            {{-- KOLOM 2: INFO & AKSI --}}
                            <div class="flex flex-col gap-4 text-sm">
                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                                    <p class="font-bold text-gray-800 mb-1">{{ $trx->metode_pengiriman == 'diantar' ? 'Diantar Kurir' : 'Ambil di Toko' }}</p>
                                    <p class="text-xs text-gray-500 italic">{{ $trx->metode_bayar == 'cash' ? 'Tunai/COD' : 'Transfer Bank' }}</p>
                                </div>

                                {{-- AKSI JIKA DIPROSES --}}
                                @if($trx->status_pesanan == 'diproses')
                                    <form action="{{ route('pelanggan.riwayat.batal', $trx->id_transaksi) }}" method="POST" onsubmit="return confirm('Yakin ingin membatalkan pesanan ini?')">
                                        @csrf @method('PUT')
                                        <button type="submit" class="w-full bg-white border border-red-200 text-red-600 hover:bg-red-50 font-bold py-2.5 rounded-lg transition text-sm shadow-sm flex items-center justify-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            Batalkan Pesanan
                                        </button>
                                    </form>
                                    <p class="text-center text-xs text-gray-400 mt-1">Menunggu konfirmasi toko</p>

                                {{-- AKSI JIKA SELESAI --}}
                                @elseif($trx->status_pesanan == 'selesai')
                                    
                                    {{-- TOMBOL CETAK --}}
                                    <a href="{{ route('kasir.transaksi.cetak', ['id' => $trx->id_transaksi]) }}" target="_blank" 
                                       class="w-full flex items-center justify-center gap-2 bg-gray-900 hover:bg-black text-white font-bold py-2.5 rounded-lg transition text-sm shadow-md">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        Download Nota
                                    </a>

                                    {{-- TOMBOL ULASAN --}}
                                    @php
                                        $sudahUlas = \App\Models\Ulasan::where('id_transaksi', $trx->id_transaksi)->exists();
                                    @endphp
                                    
                                    @if(!$sudahUlas)
                                        <button @click="reviewModalOpen = true; trxId = '{{ $trx->id_transaksi }}'" 
                                                class="w-full flex items-center justify-center gap-2 bg-yellow-400 hover:bg-yellow-500 text-blue-900 font-bold py-2.5 rounded-lg transition text-sm shadow-md">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            Beri Ulasan
                                        </button>
                                    @else
                                        <div class="text-center text-xs text-green-600 font-bold bg-green-50 py-2 rounded border border-green-100 flex items-center justify-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                            Sudah Diulas
                                        </div>
                                    @endif

                                @endif
                            </div>

                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-20 text-gray-400">Belum ada riwayat pesanan.</div>
            @endforelse
        </div>

        {{-- MODAL ULASAN (Sama Seperti Sebelumnya) --}}
        <div x-show="reviewModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 px-4" x-transition x-cloak>
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden" @click.away="reviewModalOpen = false">
                <form action="{{ route('ulasan.store') }}" method="POST">
                    @csrf
                    <div class="p-6 text-center">
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Beri Ulasan Transaksi</h3>
                        <p class="text-sm text-gray-500 mb-6">Bagaimana pengalaman belanja Anda untuk pesanan <span class="font-mono font-bold text-blue-600" x-text="trxId"></span>?</p>
                        
                        <input type="hidden" name="id_transaksi" :value="trxId">

                        <div class="flex justify-center gap-2 mb-6">
                            <template x-for="i in 5">
                                <button type="button" @click="rating = i" class="focus:outline-none transform transition hover:scale-110">
                                    <svg class="w-10 h-10 transition-colors duration-200" :class="i <= rating ? 'text-yellow-400' : 'text-gray-200'" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </button>
                            </template>
                            <input type="hidden" name="rating" x-model="rating">
                        </div>

                        <textarea name="komentar" rows="3" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm p-3 mb-4" placeholder="Tulis saran atau pujian Anda (opsional)..."></textarea>

                        <div class="flex gap-3">
                            <button type="button" @click="reviewModalOpen = false" class="flex-1 py-2.5 bg-gray-100 text-gray-700 font-bold rounded-lg hover:bg-gray-200 transition">Batal</button>
                            <button type="submit" class="flex-1 py-2.5 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition shadow">Kirim Ulasan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>

@endcomponent