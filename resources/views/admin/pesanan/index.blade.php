<x-app-layout>
    <style>
        [x-cloak] { display: none !important; }
        /* Custom Scrollbar halus */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
    </style>

    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-800 dark:text-white leading-tight">
            {{ __('Pesanan Online') }}
        </h2>
    </x-slot>

    {{-- WRAPPER FULL HEIGHT --}}
    <div class="h-[calc(100vh-65px)] flex flex-col bg-gray-100 dark:bg-gray-900" x-data="{ activeTab: 'masuk' }">
        
        {{-- BAGIAN ATAS (TIDAK DI-SCROLL) --}}
        <div class="flex-shrink-0 px-4 sm:px-6 lg:px-8 pt-6">
            
            {{-- TAB NAVIGATION --}}
            <div class="flex space-x-4 border-b border-gray-200 dark:border-gray-700 mb-4">
                <button @click="activeTab = 'masuk'" 
                        :class="activeTab === 'masuk' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="py-2 px-4 border-b-2 font-bold text-sm focus:outline-none transition flex items-center gap-2">
                    Pesanan Masuk
                    @if($pesananBaru->count() > 0)
                        <span class="bg-red-600 text-white text-xs px-2 py-0.5 rounded-full animate-pulse">{{ $pesananBaru->count() }}</span>
                    @endif
                </button>
                <button @click="activeTab = 'riwayat'" 
                        :class="activeTab === 'riwayat' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="py-2 px-4 border-b-2 font-bold text-sm focus:outline-none transition">
                    Riwayat Selesai
                </button>
            </div>

            {{-- NOTIFIKASI --}}
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg shadow-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg shadow-sm">{{ session('error') }}</div>
            @endif
        </div>

        {{-- BAGIAN TENGAH (SCROLLABLE CONTENT) --}}
        <div class="flex-1 overflow-hidden px-4 sm:px-6 lg:px-8 pb-6">
            
            {{-- KONTEN TAB 1: PESANAN MASUK (CARD LIST - SCROLLABLE) --}}
            <div x-show="activeTab === 'masuk'" x-transition class="h-full overflow-y-auto custom-scrollbar pr-2">
                @if($pesananBaru->count() > 0)
                    <div class="grid gap-6 pb-10">
                        @foreach($pesananBaru as $pesanan)
                            <div class="bg-white dark:bg-gray-800 border border-blue-200 rounded-xl p-6 shadow-sm hover:shadow-md transition">
                                <div class="flex flex-col md:flex-row justify-between items-start gap-4">
                                    {{-- Info Kiri --}}
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <span class="font-mono font-bold text-blue-600 text-lg">{{ $pesanan->id_transaksi }}</span>
                                            <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded font-bold uppercase border border-yellow-200">
                                                {{ $pesanan->metode_bayar == 'transfer' ? 'Transfer Bank' : 'Bayar Tunai/COD' }}
                                            </span>
                                            @if(str_contains($pesanan->keterangan, 'AMBIL SENDIRI'))
                                                <span class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded font-bold uppercase border border-purple-200">Ambil Sendiri</span>
                                            @else
                                                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded font-bold uppercase border border-blue-200">Diantar</span>
                                            @endif
                                        </div>
                                        
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1">{{ $pesanan->nama_pelanggan }}</h3>
                                        <div class="text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 p-3 rounded-lg mb-3">
                                            {{ $pesanan->keterangan }}
                                        </div>

                                        <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1 pl-4 list-disc">
                                            @foreach($pesanan->details as $item)
                                                <li>
                                                    <span class="font-bold">{{ $item->jumlah }} {{ $item->satuan }}</span> - {{ $item->produk->nama_produk }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>

                                    {{-- Info Kanan --}}
                                    <div class="md:text-right flex flex-col items-start md:items-end gap-2 min-w-[200px]">
                                        <div class="text-sm text-gray-500">Total Tagihan</div>
                                        <div class="text-3xl font-extrabold text-blue-700 mb-2">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</div>
                                        <a href="{{ route('pesanan.picking', ['id' => $pesanan->id_transaksi]) }}" target="_blank"
                                           class="w-full px-6 py-2 bg-yellow-400 hover:bg-yellow-500 text-blue-900 font-bold rounded-lg shadow transition flex items-center justify-center gap-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                            Cetak Daftar Ambil
                                        </a>
                                        
                                        <a href="{{ route('kasir.transaksi.index', ['id' => $pesanan->id_transaksi]) }}" 
                                           class="w-full px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg shadow transition flex items-center justify-center gap-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 3m5.25-3l.75 3 1 3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                            Proses di Kasir
                                        </a>
                                        
                                        <form action="{{ route('pesanan.batal', ['id' => $pesanan->id_transaksi]) }}" method="POST" class="w-full" onsubmit="return confirm('Yakin batalkan pesanan ini?')">
                                            @csrf @method('PUT')
                                            <button type="submit" class="w-full px-6 py-2 bg-gray-200 hover:bg-red-100 text-gray-600 hover:text-red-600 font-bold rounded-lg transition text-sm">Batalkan</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-20 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 border-dashed">
                        <div class="bg-gray-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-500">Tidak ada pesanan baru</h3>
                    </div>
                @endif
            </div>

            {{-- KONTEN TAB 2: RIWAYAT SELESAI (TABLE FULL HEIGHT & STICKY HEADER) --}}
            <div x-show="activeTab === 'riwayat'" x-transition x-cloak class="h-full flex flex-col bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                
                <div class="overflow-y-auto flex-1 custom-scrollbar">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        {{-- STICKY HEADER --}}
                        <thead class="bg-gray-100 dark:bg-gray-700 sticky top-0 z-10 shadow-sm">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider w-32">ID Transaksi</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider w-40">Waktu Selesai</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Pelanggan</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider pl-8">Diproses Oleh</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
                            @foreach($riwayatSelesai as $trx)
                                <tr class="hover:bg-blue-50 dark:hover:bg-gray-700 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono font-bold text-blue-600">{{ $trx->id_transaksi }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($trx->waktu_transaksi)->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">{{ $trx->nama_pelanggan }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-right">Rp {{ number_format($trx->total_harga, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 pl-8">{{ $trx->nama_kasir }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($trx->status_pesanan == 'selesai')
                                            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full font-bold">Selesai</span>
                                        @else
                                            <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full font-bold">Batal</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <a href="{{ route('kasir.transaksi.cetak', ['id' => $trx->id_transaksi]) }}" target="_blank" class="text-blue-600 hover:text-blue-900 font-bold text-sm underline">
                                            Cetak Struk
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                {{-- TOTAL FOOTER --}}
                <div class="p-3 bg-gray-50 border-t text-right text-xs text-gray-500 font-semibold">
                    Menampilkan {{ $riwayatSelesai->count() }} riwayat transaksi terakhir
                </div>
            </div>

        </div>
    </div>
</x-app-layout>