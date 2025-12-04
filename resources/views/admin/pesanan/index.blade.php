<x-app-layout>
    <style>
        [x-cloak] { display: none !important; }
    </style>

    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-800 dark:text-white leading-tight">
            {{ __('Pesanan Online') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ activeTab: 'masuk' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- TAB NAVIGATION --}}
            <div class="flex space-x-4 mb-6 border-b border-gray-200 dark:border-gray-700">
                <button @click="activeTab = 'masuk'" 
                        :class="activeTab === 'masuk' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="py-2 px-4 border-b-2 font-bold text-sm focus:outline-none transition flex items-center gap-2">
                    Pesanan Masuk
                    @if($pesananBaru->count() > 0)
                        <span class="bg-red-600 text-white text-xs px-2 py-0.5 rounded-full">{{ $pesananBaru->count() }}</span>
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

            {{-- KONTEN TAB 1: PESANAN MASUK --}}
            <div x-show="activeTab === 'masuk'" x-transition>
                @if($pesananBaru->count() > 0)
                    <div class="grid gap-6">
                        @foreach($pesananBaru as $pesanan)
                            <div class="bg-white dark:bg-gray-800 border border-blue-200 rounded-xl p-6 shadow-sm hover:shadow-md transition" x-data="{ openPay: false }">
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

                                        {{-- List Barang --}}
                                        <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1 pl-4 list-disc">
                                            @foreach($pesanan->details as $item)
                                                <li>
                                                    <span class="font-bold">{{ $item->jumlah }} {{ $item->satuan }}</span> - {{ $item->produk->nama_produk }}
                                                    <span class="text-gray-400 text-xs">(@ Rp {{ number_format($item->harga_satuan, 0, ',', '.') }})</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>

                                    {{-- Info Kanan (Total & Aksi) --}}
                                    <div class="md:text-right flex flex-col items-start md:items-end gap-2 min-w-[200px]">
                                        <div class="text-sm text-gray-500">Total Tagihan</div>
                                        <div class="text-3xl font-extrabold text-blue-700 mb-2">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</div>
                                        
                                        <button @click="openPay = true" class="w-full px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg shadow transition flex items-center justify-center gap-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            Proses Selesai
                                        </button>
                                        
                                        <form action="{{ route('pesanan.batal', $pesanan->id_transaksi) }}" method="POST" class="w-full" onsubmit="return confirm('Yakin batalkan pesanan ini?')">
                                            @csrf @method('PUT')
                                            <button type="submit" class="w-full px-6 py-2 bg-gray-200 hover:bg-red-100 text-gray-600 hover:text-red-600 font-bold rounded-lg transition text-sm">
                                                Batalkan
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                {{-- MODAL PEMBAYARAN --}}
                                <div x-show="openPay" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" x-cloak>
                                    <div class="bg-white dark:bg-gray-800 w-full max-w-md p-6 rounded-xl shadow-2xl transform scale-100" @click.away="openPay = false">
                                        <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white border-b pb-2">Terima Pembayaran</h3>
                                        
                                        <form action="{{ route('pesanan.selesai', $pesanan->id_transaksi) }}" method="POST">
                                            @csrf @method('PUT')
                                            
                                            <div class="mb-4">
                                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Total Harus Dibayar</label>
                                                <input type="text" value="Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}" class="w-full bg-gray-100 border-gray-300 rounded-lg font-bold text-lg text-gray-700" disabled>
                                            </div>

                                            <div class="mb-6">
                                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Uang Diterima (Tunai/Transfer)</label>
                                                <input type="number" name="bayar" class="w-full border-blue-500 ring-2 ring-blue-100 rounded-lg font-bold text-xl p-2" placeholder="0" required autofocus>
                                                <p class="text-xs text-gray-500 mt-1">Masukkan nominal uang yang diterima dari pelanggan/bukti transfer.</p>
                                            </div>

                                            <div class="flex justify-end gap-2 pt-4 border-t">
                                                <button type="button" @click="openPay = false" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-bold hover:bg-gray-300">Batal</button>
                                                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 shadow-lg">Simpan & Selesai</button>
                                            </div>
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
                        <p class="text-sm text-gray-400">Pesanan online yang masuk akan muncul di sini.</p>
                    </div>
                @endif
            </div>

            {{-- KONTEN TAB 2: RIWAYAT SELESAI --}}
            <div x-show="activeTab === 'riwayat'" x-transition x-cloak>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Pelanggan</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase pl-8">Diproses Oleh</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
                                @foreach($riwayatSelesai as $trx)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono font-bold text-blue-600">{{ $trx->id_transaksi }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $trx->created_at->format('d/m/Y H:i') }}</td>
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
                    <div class="p-4 border-t">
                        {{ $riwayatSelesai->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>