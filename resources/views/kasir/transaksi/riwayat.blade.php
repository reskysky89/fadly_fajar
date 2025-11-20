<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-gray-800 dark:text-white leading-tight">
                {{ __('Riwayat Penjualan') }}
            </h2>
        </div>
    </x-slot>

    {{-- X-DATA: Menggabungkan URL Scroll dan Logika Modal --}}
    <div class="h-[calc(100vh-65px)] flex flex-col bg-gray-100 dark:bg-gray-900" 
         x-data="riwayatApp('{{ $riwayat->nextPageUrl() }}')">
        
        {{-- BAGIAN 1: FILTER (Fixed di Atas) --}}
        <div class="bg-white dark:bg-gray-800 shadow p-4 flex-shrink-0 z-20 border-b border-gray-200 dark:border-gray-700">
            <form action="{{ route('kasir.riwayat.index') }}" method="GET">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div class="relative">
                        <x-input-label for="search" :value="__('Cari No. Transaksi')" />
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Contoh: 0001..." class="block mt-1 w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                    <div>
                        <x-input-label for="tanggal_mulai" :value="__('Dari Tanggal')" />
                        <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}" class="block mt-1 w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                    <div>
                        <x-input-label for="tanggal_akhir" :value="__('Sampai Tanggal')" />
                        <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}" class="block mt-1 w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                    <div class="flex space-x-2">
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md font-bold text-sm h-[38px] mt-6">Cari</button>
                        <a href="{{ route('kasir.riwayat.index') }}" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-md font-bold text-sm h-[38px] mt-6 flex items-center">Reset</a>
                    </div>
                </div>
            </form>
        </div>

        {{-- BAGIAN 2: TABEL RIWAYAT (Scrollable) --}}
        <div class="flex-1 overflow-hidden p-4 bg-gray-100 dark:bg-gray-900">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg h-full flex flex-col border border-gray-300 dark:border-gray-700">
                
                <div class="overflow-y-auto flex-1" id="scroll-container">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 relative">
                        <thead class="bg-gray-200 dark:bg-gray-700 sticky top-0 z-10 shadow-sm">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">No. Transaksi</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Tanggal & Waktu</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Pelanggan</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase pl-10">Kasir</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        
                        <tbody id="live-data-riwayat" class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                            @include('kasir.transaksi.riwayat_body')
                        </tbody>
                    </table>

                    {{-- SENSOR SCROLL --}}
                    <div x-show="nextUrl" class="p-4 text-center text-gray-500" id="load-more-trigger">
                        <span x-text="isLoading ? 'Memuat data...' : 'Scroll untuk melihat lebih banyak'"></span>
                    </div>
                    <div x-show="!nextUrl" class="p-4 text-center text-gray-400 text-sm italic border-t dark:border-gray-700">
                        -- Semua data telah ditampilkan --
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL DETAIL --}}
        <div x-show="modalDetailOpen" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" x-transition x-cloak>
            <div class="bg-white dark:bg-gray-800 w-full max-w-2xl p-0 rounded-lg shadow-2xl transform scale-100 transition-transform flex flex-col max-h-[90vh]" @click.away="modalDetailOpen = false">
                {{-- Header Modal --}}
                <div class="p-4 border-b border-gray-200 bg-gray-50 dark:bg-gray-700 rounded-t-lg flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Detail: <span x-text="detail.id_transaksi" class="text-blue-600"></span></h3>
                    <button @click="modalDetailOpen = false" class="text-gray-500 hover:text-red-500 font-bold text-xl">&times;</button>
                </div>
                {{-- Body Modal --}}
                <div class="p-6 overflow-y-auto flex-1">
                    <div class="grid grid-cols-2 gap-4 mb-6 text-sm text-gray-700 dark:text-gray-300">
                        <div><span class="text-gray-500">Tanggal:</span> <br><b x-text="formatDate(detail.waktu_transaksi)"></b></div>
                        <div><span class="text-gray-500">Kasir:</span> <br><b x-text="detail.nama_kasir"></b></div>
                        <div><span class="text-gray-500">Pelanggan:</span> <br><b x-text="detail.nama_pelanggan"></b></div>
                        <div><span class="text-gray-500">Status:</span> <br><span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold">Selesai</span></div>
                    </div>
                    <table class="w-full text-sm border-collapse">
                        <thead class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-200">
                            <tr><th class="py-2 px-2 text-left">Item</th><th class="py-2 px-2 text-center">Qty</th><th class="py-2 px-2 text-right">Harga</th><th class="py-2 px-2 text-right">Subtotal</th></tr>
                        </thead>
                        <tbody class="text-gray-800 dark:text-gray-200">
                            <template x-for="item in detail.details" :key="item.id_detail">
                                <tr class="border-b dark:border-gray-700">
                                    <td class="py-2 px-2">
                                        {{-- Tampilkan Nama Produk --}}
                                        <div x-text="item.produk ? item.produk.nama_produk : 'Produk Dihapus'" class="font-bold text-gray-800 dark:text-gray-200"></div>
                                        {{-- Kode Item kecil di bawahnya --}}
                                        <div x-text="item.id_produk" class="text-xs text-gray-500"></div>
                                    </td>
                                    <td class="py-2 px-2 text-center"><span x-text="item.jumlah"></span> <span x-text="item.satuan" class="text-xs bg-gray-200 px-1 rounded text-black"></span></td>
                                    <td class="py-2 px-2 text-right" x-text="formatRupiah(item.harga_satuan)"></td>
                                    <td class="py-2 px-2 text-right font-bold" x-text="formatRupiah(item.subtotal)"></td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot class="text-gray-800 dark:text-gray-200">
                            <tr class="text-lg"><td colspan="3" class="pt-4 text-right font-bold">Total:</td><td class="pt-4 px-2 text-right font-bold text-blue-600" x-text="formatRupiah(detail.total_harga)"></td></tr>
                            <tr><td colspan="3" class="text-right text-gray-500 text-xs">Bayar:</td><td class="text-right px-2 text-xs" x-text="formatRupiah(detail.bayar)"></td></tr>
                            <tr><td colspan="3" class="text-right text-gray-500 text-xs">Kembali:</td><td class="text-right px-2 text-xs font-bold text-green-600" x-text="formatRupiah(detail.kembalian)"></td></tr>
                        </tfoot>
                    </table>
                </div>
                {{-- Footer Modal --}}
                <div class="p-4 border-t border-gray-200 bg-gray-50 dark:bg-gray-700 rounded-b-lg flex justify-end space-x-2">
                    <button @click="modalDetailOpen = false" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Tutup</button>
                    <a :href="'{{ route('kasir.transaksi.cetak') }}?id=' + encodeURIComponent(detail.id_transaksi)" 
                   target="_blank" 
                   class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Cetak Struk Ulang
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPT GABUNGAN (Scroll + Modal) --}}
    <script>
        function riwayatApp(initialNextUrl) {
            return {
                // --- Logic Scroll ---
                nextUrl: initialNextUrl,
                isLoading: false,

                // --- Logic Modal ---
                modalDetailOpen: false,
                detail: { details: [] },

                init() {
                    // Init Observer untuk Infinite Scroll
                    const observer = new IntersectionObserver(entries => {
                        if (entries[0].isIntersecting && this.nextUrl && !this.isLoading) {
                            this.loadMore();
                        }
                    }, { rootMargin: '50px' });
                    observer.observe(document.getElementById('load-more-trigger'));
                },

                // Fungsi Load More (Scroll)
                async loadMore() {
                    this.isLoading = true;
                    try {
                        const res = await fetch(this.nextUrl, { headers: { "X-Requested-With": "XMLHttpRequest" } });
                        const data = await res.json();
                        document.getElementById('live-data-riwayat').insertAdjacentHTML('beforeend', data.html);
                        this.nextUrl = data.next_page_url;
                    } catch (error) { console.error('Gagal muat data:', error); }
                    this.isLoading = false;
                },

                // Fungsi Buka Modal Detail
                async bukaDetail(id) {
                    this.detail = { details: [], id_transaksi: 'Loading...' };
                    this.modalDetailOpen = true;
                    try {
                        // PERBAIKAN DI SINI: Gunakan ?id=... dan encodeURIComponent agar aman
                        const response = await fetch(`{{ route('kasir.transaksi.show') }}?id=` + encodeURIComponent(id));
                        
                        if (!response.ok) throw new Error('Gagal fetch');
                        
                        const data = await response.json();
                        this.detail = data;
                    } catch (error) {
                        console.error('Gagal ambil detail:', error);
                        alert('Gagal memuat detail transaksi.');
                        this.modalDetailOpen = false;
                    }
                },

                // Helpers
                formatRupiah(angka) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka || 0); },
                formatDate(dateString) { if(!dateString) return '-'; const date = new Date(dateString); return date.toLocaleDateString('id-ID') + ' ' + date.toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'}); }
            }
        }
    </script>
</x-app-layout>