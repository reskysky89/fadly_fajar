<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-gray-800 dark:text-white leading-tight">
                {{ __('Penjualan Kasir') }}
            </h2>
            <div class="text-2xl font-mono font-bold text-blue-600 bg-white px-4 py-2 rounded shadow" x-data x-init="setInterval(() => $el.innerText = new Date().toLocaleTimeString('id-ID'), 1000)">
                {{ date('H:i:s') }}
            </div>
        </div>
    </x-slot>

    <div class="h-[calc(100vh-140px)] flex flex-col" 
         x-data="transaksiKasir()"
         @keydown.window="handleGlobalKey($event)">
        
        {{-- HEADER --}}
        <div class="bg-white dark:bg-gray-800 shadow p-4 flex-shrink-0 z-20">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-8 grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 uppercase font-bold">No. Transaksi</label>
                        <div class="font-mono font-bold text-lg text-gray-800 dark:text-gray-200 bg-gray-50 p-2 rounded border">{{ $nextId }}</div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 uppercase font-bold">Kasir</label>
                        <div class="font-bold text-gray-800 dark:text-gray-200 mt-2">{{ Auth::user()->nama }}</div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 uppercase font-bold">Tanggal</label>
                        <div class="font-bold text-gray-800 dark:text-gray-200 mt-2">{{ date('d/m/Y') }}</div>
                    </div>
                </div>
                <div class="col-span-4 bg-black text-green-500 font-mono font-bold flex flex-col justify-center items-end px-4 rounded shadow-inner">
                    <span class="text-xs text-gray-400 uppercase">Total Bayar</span>
                    {{-- TOTAL BAYAR (Akan muncul otomatis) --}}
                    <span class="text-5xl" x-text="formatRupiah(grandTotal)">0</span>
                </div>
            </div>
        </div>

        {{-- TABEL TRANSAKSI --}}
        <div class="flex-1 overflow-hidden p-4 bg-gray-100 dark:bg-gray-900">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg h-full flex flex-col border border-gray-300 dark:border-gray-700">
                <div class="overflow-y-auto flex-1">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 relative">
                        <thead class="bg-gray-200 dark:bg-gray-700 sticky top-0 z-10 shadow-sm">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase w-12">No</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase w-48">Kode Item (Scan)</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Nama Barang</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase w-24">Qty</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase w-32">Satuan</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-700 uppercase w-32">Harga</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-700 uppercase w-40">Total</th>
                                <th class="px-4 py-3 text-center w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                            <template x-for="(baris, index) in barisTabel" :key="baris.id_temp">
                                <tr :class="activeRow === index ? 'bg-blue-100 dark:bg-blue-900' : 'hover:bg-gray-50 dark:hover:bg-gray-700'" 
                                    class="transition-colors cursor-pointer"
                                    @click="activeRow = index">
                                    
                                    <td class="px-4 py-2 text-center font-mono text-sm text-gray-500" x-text="index + 1"></td>

                                    <td class="px-4 py-2">
                                        <input type="text" x-model="baris.kode_item" :id="'kode_' + index"
                                               @focus="activeRow = index"
                                               @keydown.delete="hapusBaris(index)"
                                               @keydown.enter.prevent="scanProduk(index)"
                                               @keydown.tab="scanProduk(index)"
                                               @keydown.arrow-down.prevent="fokusBawah(index, 'kode')"
                                               @keydown.arrow-up.prevent="fokusAtas(index, 'kode')"
                                               @keydown.arrow-right.prevent="fokusKanan(index, 'qty')"
                                               @input="baris.id_produk_final = ''" 
                                               class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 font-mono text-sm font-bold text-blue-700 uppercase h-9"
                                               placeholder="Scan..." autofocus>
                                        <p x-show="baris.error" x-text="baris.error" class="text-xs text-red-500 mt-1 font-bold"></p>
                                    </td>

                                    <td class="px-4 py-2">
                                        <input type="text" x-model="baris.nama_barang" readonly @focus="activeRow = index"
                                               class="w-full bg-transparent border-transparent rounded-md text-gray-600 text-sm cursor-not-allowed focus:ring-0 h-9">
                                    </td>

                                    {{-- QTY (TEXT INPUT: Hanya Angka, Tanpa Spinner, Navigasi Aman) --}}
                                    <td class="px-4 py-2">
                                        <input type="text" inputmode="numeric" 
                                               :value="baris.qty"
                                               :id="'qty_' + index"
                                               @focus="activeRow = index; $el.select()"
                                               @click="$el.select()"
                                               @input="sanitizeQty(index, $el)"
                                               @keydown.delete="hapusBaris(index)" 
                                               @keydown.enter.prevent="fokusBawah(index, 'kode')"
                                               @keydown.arrow-down.prevent="fokusBawah(index, 'qty')"
                                               @keydown.arrow-up.prevent="fokusAtas(index, 'qty')"
                                               @keydown.arrow-left.prevent="fokusKiri(index, 'kode')"
                                               @keydown.arrow-right.prevent="fokusKanan(index, 'satuan')"
                                               class="w-full text-center border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 font-bold h-9">
                                    </td>

                                    <td class="px-4 py-2">
                                        <select x-model="baris.id_satuan" :id="'satuan_' + index"
                                                @focus="activeRow = index" @change="updateHarga(index)"
                                                @keydown.delete.prevent="hapusBaris(index)"
                                                @keydown.enter.prevent="fokusBawah(index, 'kode')"
                                                @keydown.arrow-down.prevent="fokusBawah(index, 'satuan')"
                                                @keydown.arrow-up.prevent="fokusAtas(index, 'satuan')"
                                                @keydown.arrow-left.prevent="fokusKiri(index, 'qty')"
                                                @keydown.arrow-right.prevent="fokusKanan(index, 'hapus')"
                                                class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 h-9 py-1"
                                                :disabled="!baris.id_produk_final">
                                            <template x-for="opsi in baris.opsi_satuan" :key="opsi.id">
                                                <option :value="opsi.id" x-text="opsi.nama"></option>
                                            </template>
                                        </select>
                                    </td>

                                    <td class="px-4 py-2 text-right">
                                        <input type="text" :value="formatRupiah(baris.harga)" readonly 
                                               class="w-full bg-transparent border-transparent rounded-md text-gray-600 text-right text-sm cursor-not-allowed focus:ring-0 font-mono h-9">
                                    </td>

                                    <td class="px-4 py-2 text-right">
                                        <div class="font-bold font-mono text-gray-900 dark:text-gray-100 text-lg pt-1" x-text="formatRupiah(baris.subtotal)"></div>
                                    </td>

                                    <td class="px-4 py-2 text-center">
                                        <button type="button" :id="'hapus_' + index"
                                                @click="hapusBaris(index)" 
                                                @keydown.arrow-left.prevent="fokusKiri(index, 'satuan')"
                                                @keydown.arrow-right.prevent="fokusBawah(index, 'kode')"
                                                x-show="barisTabel.length > 1" 
                                                class="text-gray-400 hover:text-red-600 transition-colors focus:outline-none focus:text-red-600 focus:ring-2 focus:ring-red-500 rounded" 
                                                tabindex="0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- FOOTER --}}
        <div class="bg-gray-200 dark:bg-gray-800 p-3 border-t border-gray-300 dark:border-gray-700 flex-shrink-0 z-20">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    Shortcut: <span class="font-bold">[F2]</span> Scan, <span class="font-bold">[END]</span> Bayar, <span class="font-bold">[DEL]</span> Hapus
                </div>
                <div class="flex space-x-2">
                    <button @click="resetForm()" class="px-6 py-3 bg-red-500 hover:bg-red-600 text-white font-bold rounded shadow flex items-center gap-2">BATAL [ESC]</button>
                    <button @click="bukaModalBayar()" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xl rounded shadow-lg flex items-center gap-2">BAYAR [END]</button>
                </div>
            </div>
        </div>

        {{-- MODAL PENCARIAN --}}
        <div x-show="modalPencarianOpen" class="fixed inset-0 bg-gray-600 bg-opacity-75 z-50 flex items-center justify-center" x-transition x-cloak>
            <div class="relative mx-auto p-0 border w-full max-w-3xl shadow-lg rounded-md bg-white dark:bg-gray-800 max-h-[80vh] flex flex-col" 
                 @click.away="modalPencarianOpen = false">
                
                <div class="p-4 bg-blue-600 text-white rounded-t-md flex justify-between items-center">
                    <h3 class="text-xl font-bold">Pilih Item (Enter)</h3>
                    <button @click="modalPencarianOpen = false" class="text-white hover:text-gray-200 text-2xl">&times;</button>
                </div>
                
                {{-- FITUR BARU: SEARCH BAR DALAM MODAL --}}
                <div class="p-3 bg-gray-100 dark:bg-gray-700 border-b border-gray-300 dark:border-gray-600">
                    <input type="text" 
                           x-model="searchQueryModal" 
                           @keydown.enter.prevent="searchProdukInModal()" {{-- <-- Panggil Fungsi Search Baru --}}
                           @keydown.arrow-down.prevent="$el.blur(); navigasiModal('bawah')"
                           @keydown.arrow-up.prevent="$el.blur(); navigasiModal('atas')"
                           class="w-full border-gray-300 dark:border-gray-600 rounded-md p-2 text-sm focus:ring-blue-500 focus:border-blue-500" 
                           placeholder="Cari Nama/Kode Item (Tekan Enter)..."
                           autofocus>
                </div>
                {{-- -------------------------------------- --}}

                <div class="overflow-y-auto flex-1" id="modal-list-container">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Kode</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Nama Barang</th>
                                <th class="px-4 py-2 text-center text-xs font-bold text-gray-500 uppercase">Satuan</th>
                                <th class="px-4 py-2 text-center text-xs font-bold text-gray-500 uppercase">Stok</th>
                                <th class="px-4 py-2 text-right text-xs font-bold text-gray-500 uppercase">Harga Jual</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            {{-- Ubah sumber data ke 'filteredModalItems' --}}
                            <template x-for="(produk, index) in searchResultsModal" :key="produk.unique_id">
                                <tr :id="'modal-row-' + index"
                                    :class="activeModalIndex === index ? 'bg-blue-100 dark:bg-blue-900' : 'hover:bg-gray-50 dark:hover:bg-gray-700'"
                                    class="cursor-pointer transition-colors" 
                                    @click="pilihProdukDariModal(produk)"
                                    @mouseover="activeModalIndex = index">
                                    
                                    <td class="px-4 py-3 text-sm font-mono text-gray-600" x-text="produk.id_produk"></td>
                                    <td class="px-4 py-3 text-sm font-bold text-gray-800 dark:text-gray-200" x-text="produk.nama_produk"></td>
                                    <td class="px-4 py-3 text-center"><span class="px-2 py-1 bg-gray-200 rounded text-xs font-bold" x-text="produk.nama_satuan"></span></td>
                                    <td class="px-4 py-3 text-center text-sm font-bold" :class="parseFloat(produk.stok_real.replace(',', '.')) > 0 ? 'text-green-600' : 'text-red-600'" x-text="produk.stok_real"></td>
                                    <td class="px-4 py-3 text-right font-mono text-blue-600 font-bold" x-text="formatRupiah(produk.harga_jual)"></td>
                                </tr>
                            </template>
                            
                            
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-t bg-gray-50 text-right rounded-b-lg flex justify-between items-center">
                    <span class="text-xs text-gray-500">Gunakan <span class="font-bold">↑ ↓</span> untuk memilih, <span class="font-bold">Enter</span> untuk OK</span>
                    <button @click="modalPencarianOpen = false" class="px-4 py-2 bg-gray-500 text-white rounded text-sm">Tutup [ESC]</button>
                </div>
            </div>
        </div>

        {{-- MODAL HAPUS --}}
        <div x-show="modalHapusOpen" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" x-transition x-cloak><div class="bg-white dark:bg-gray-800 w-full max-w-sm p-6 rounded-lg shadow-2xl transform scale-100 transition-transform"><h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-white">Hapus Item?</h3><p class="text-gray-600 dark:text-gray-300 mb-6">Hapus <span class="font-bold text-red-600" x-text="namaBarangHapus"></span>?</p><div class="flex justify-end space-x-3"><button id="btn-hapus-tidak" @click="modalHapusOpen = false" @keydown.arrow-right.prevent="document.getElementById('btn-hapus-ya').focus()" class="px-4 py-2 bg-gray-500 text-white rounded">Tidak</button><button id="btn-hapus-ya" @click="konfirmasiHapus()" @keydown.arrow-left.prevent="document.getElementById('btn-hapus-tidak').focus()" class="px-4 py-2 bg-red-600 text-white rounded font-bold">Ya, Hapus</button></div></div></div>

        {{-- MODAL BAYAR (3 TOMBOL) --}}
        <div x-show="modalBayarOpen" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" x-transition x-cloak>
            <div class="bg-white dark:bg-gray-800 w-full max-w-md p-6 rounded-lg shadow-2xl transform scale-100 transition-transform">
                <h2 class="text-2xl font-bold mb-4 text-gray-800 dark:text-white border-b pb-2">Pembayaran</h2>
                <div class="space-y-4">
                    <div class="flex justify-between text-xl"><span>Total Tagihan</span><span class="font-bold" x-text="formatRupiah(grandTotal)"></span></div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Uang Diterima</label>
                        <input type="number" x-model.number="uangDiterima" id="input-bayar" @input="hitungKembalian()" @keydown.enter.prevent="prosesBayar(true)" @keydown.escape="modalBayarOpen = false" class="w-full text-right text-3xl font-bold border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2" placeholder="0">
                    </div>
                    <div class="flex justify-between text-xl pt-2 border-t text-blue-600"><span>Kembalian</span><span class="font-bold" x-text="formatRupiah(kembalian)"></span></div>
                </div>
                
                {{-- FOOTER MODAL (3 TOMBOL) --}}
                <div class="mt-6 flex justify-end space-x-2">
                    {{-- 1. Batal --}}
                    <button @click="modalBayarOpen = false" class="px-4 py-2 bg-gray-500 text-white rounded font-bold">Batal</button>
                    
                    {{-- 2. Simpan Saja --}}
                    <button @click="prosesBayar(false)" 
                            :disabled="uangDiterima < grandTotal" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded"
                            :class="uangDiterima < grandTotal ? 'opacity-50 cursor-not-allowed' : ''">
                        Simpan
                    </button>
                    
                    {{-- 3. Simpan & Cetak --}}
                    <button @click="prosesBayar(true)" 
                            :disabled="uangDiterima < grandTotal" 
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-bold rounded flex items-center"
                            :class="uangDiterima < grandTotal ? 'opacity-50 cursor-not-allowed' : ''">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        Simpan & Cetak
                    </button>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function transaksiKasir() {
            return {
                barisTabel: [], activeRow: 0,
                modalBayarOpen: false, modalPencarianOpen: false, searchResultsModal: [], activeModalIndex: 0,
                barisYangSedangDiisi: null, uangDiterima: '', kembalian: 0, modalHapusOpen: false, indexBarisHapus: null, namaBarangHapus: '',
                isProcessing: false,

                searchQueryModal: '',

                init() {
                    this.tambahBarisBaru();
                },
                async searchProdukInModal() {
                    if (this.searchQueryModal.length < 2) return;
                    
                    // Tampilkan loading (opsional, bisa pakai text 'Mencari...')
                    this.searchResultsModal = []; 
                    
                    try {
                        // Panggil API yang SAMA dengan scan utama
                        const response = await fetch(`{{ route('kasir.transaksi.cariProduk') }}?search=${this.searchQueryModal}`);
                        const data = await response.json();
                        
                        // Update isi tabel modal dengan hasil baru dari database
                        this.searchResultsModal = data;
                        this.activeModalIndex = 0; // Reset pilihan ke paling atas

                        if (data.length === 0) {
                            // Opsional: Beri tahu jika kosong
                            // alert('Tidak ditemukan');
                        }
                    } catch (error) { 
                        console.error(error); 
                    }
                },
                

                handleGlobalKey(e) {
                    if (this.modalPencarianOpen) {
                        if (e.key === 'ArrowDown') { e.preventDefault(); this.navigasiModal('bawah'); }
                        else if (e.key === 'ArrowUp') { e.preventDefault(); this.navigasiModal('atas'); }
                        else if (e.key === 'Enter') { e.preventDefault(); this.pilihProdukViaEnter(); }
                        else if (e.key === 'Escape') { this.modalPencarianOpen = false; }
                        return;
                    }
                    if (this.modalBayarOpen) { 
                        if (e.key === 'Enter' && !this.isProcessing) { e.preventDefault(); this.prosesBayar(true); } 
                        else if (e.key === 'Escape') { this.modalBayarOpen = false; } 
                        return; 
                    }
                    if (this.modalHapusOpen) { if (e.key === 'ArrowRight') document.getElementById('btn-hapus-ya').focus(); else if (e.key === 'ArrowLeft') document.getElementById('btn-hapus-tidak').focus(); else if (e.key === 'Escape') this.modalHapusOpen = false; return; }
                    if (e.key === 'End') { e.preventDefault(); this.bukaModalBayar(); }
                    if (e.key === 'F2') { e.preventDefault(); this.tambahBarisBaru(); }
                },
                navigasiModal(arah) { 
                    // Gunakan filteredModalItems, bukan searchResultsModal
                    const items = this.filteredModalItems; 
                    if (arah === 'bawah') { 
                        if (this.activeModalIndex < items.length - 1) this.activeModalIndex++; 
                        this.scrollToModalItem(); 
                    } else if (arah === 'atas') { 
                        if (this.activeModalIndex > 0) this.activeModalIndex--; 
                        this.scrollToModalItem(); 
                    } 
                },

                sanitizeQty(index, el) {
                    let val = el.value.replace(/[^0-9]/g, '');
                    if (val === '') val = '';
                    
                    const baris = this.barisTabel[index];
                    const qtyInput = parseInt(val) || 0;
                    
                    // Validasi Stok
                    const opsi = baris.opsi_satuan.find(o => o.id == baris.id_satuan);
                    if (opsi) {
                        let stokStr = opsi.stok.toString(); 
                        let stokTersedia = parseFloat(stokStr.replace(/\./g, '').replace(',', '.'));

                        if (qtyInput > stokTersedia) {
                            alert(`Stok tidak cukup! Sisa hanya: ${stokStr} ${opsi.nama}`);
                            val = Math.floor(stokTersedia); 
                        }
                    }

                    this.barisTabel[index].qty = val;
                    this.hitungSubtotal(index);
                },

                hitungSubtotal(index) {
                    const baris = this.barisTabel[index];
                    let qty = parseInt(baris.qty) || 0;
                    baris.subtotal = qty * baris.harga;
                },

                // --- PERBAIKAN TOTAL BAYAR DI SINI ---
                get grandTotal() {
                    // Sebelumnya salah panggil variabel 'item', sekarang sudah benar 'baris'
                    return this.barisTabel.reduce((sum, baris) => sum + (baris.subtotal || 0), 0);
                },
                // -------------------------------------

                // ... (FUNGSI LAIN TETAP SAMA) ...
                fokusBawah(index, colName) { if (this.modalPencarianOpen) return; const nextIndex = index + 1; if (nextIndex < this.barisTabel.length) { this.activeRow = nextIndex; document.getElementById(colName + '_' + nextIndex)?.focus(); } else { this.tambahBarisBaru(); } },
                fokusAtas(index, colName) { if (this.modalPencarianOpen) return; if (index > 0) { this.activeRow = index - 1; document.getElementById(colName + '_' + (index - 1))?.focus(); } },
                fokusKanan(index, nextCol) { if (this.modalPencarianOpen) return; document.getElementById(nextCol + '_' + index)?.focus(); },
                fokusKiri(index, prevCol) { if (this.modalPencarianOpen) return; document.getElementById(prevCol + '_' + index)?.focus(); },
                navigasiModal(arah) { if (arah === 'bawah') { if (this.activeModalIndex < this.searchResultsModal.length - 1) this.activeModalIndex++; this.scrollToModalItem(); } else if (arah === 'atas') { if (this.activeModalIndex > 0) this.activeModalIndex--; this.scrollToModalItem(); } },
                scrollToModalItem() { const el = document.getElementById('modal-row-' + this.activeModalIndex); el?.scrollIntoView({ block: 'nearest' }); },
                pilihProdukViaEnter() { if (this.searchResultsModal.length > 0) { this.pilihProdukDariModal(this.searchResultsModal[this.activeModalIndex]); } },
                tambahBarisBaru() { this.barisTabel.push({ id_temp: Date.now() + Math.random(), kode_item: '', id_produk_final: '', nama_barang: '', qty: 1, id_satuan: '', harga: 0, subtotal: 0, opsi_satuan: [], error: '' }); this.activeRow = this.barisTabel.length - 1; this.$nextTick(() => { document.getElementById('kode_' + this.activeRow)?.focus(); }); },
                hapusBaris(index) { const baris = this.barisTabel[index]; if (!baris.nama_barang || baris.nama_barang === '') { this.barisTabel.splice(index, 1); if (this.barisTabel.length === 0) this.tambahBarisBaru(); if (this.activeRow >= this.barisTabel.length) this.activeRow = this.barisTabel.length - 1; this.$nextTick(() => document.getElementById('kode_' + this.activeRow)?.focus()); return; } this.indexBarisHapus = index; this.namaBarangHapus = baris.nama_barang; this.modalHapusOpen = true; this.$nextTick(() => document.getElementById('btn-hapus-tidak').focus()); },
                konfirmasiHapus() { if (this.indexBarisHapus !== null) { this.barisTabel.splice(this.indexBarisHapus, 1); if (this.barisTabel.length === 0) this.tambahBarisBaru(); if (this.activeRow >= this.barisTabel.length) this.activeRow = this.barisTabel.length - 1; this.$nextTick(() => document.getElementById('kode_' + this.activeRow)?.focus()); } this.modalHapusOpen = false; },
                
                async scanProduk(index) {
                    if (this.modalPencarianOpen || this.modalHapusOpen) return;
                    const baris = this.barisTabel[index];
                    const kode = baris.kode_item;
                    if (kode.length < 2) return;
                    if (baris.id_produk_final) return;

                    try {
                        const response = await fetch(`{{ route('kasir.transaksi.cariProduk') }}?search=${kode}`);
                        const data = await response.json();

                        if (data.length === 0) {
                            baris.error = 'Produk tidak ditemukan!';
                            baris.nama_barang = '';
                        } else if (data.length === 1) {
                            // Validasi Stok Scan Langsung
                            let stok = this.parseStokString(data[0].stok_real);
                            if (stok < 1) {
                                alert('Stok Tidak Cukup (Kurang dari 1)! Sisa: ' + data[0].stok_real);
                                baris.kode_item = ''; 
                                return;
                            }
                            this.isiBaris(index, data, data[0]);
                        } else {
                            this.searchResultsModal = data;
                            this.activeModalIndex = 0;
                            this.barisYangSedangDiisi = index;
                            this.modalPencarianOpen = true;
                        }
                    } catch (error) { console.error(error); }
                },

                isiBaris(index, semuaOpsi, produkTerpilih) {
                    const baris = this.barisTabel[index];
                    baris.id_produk_final = produkTerpilih.id_produk;
                    baris.kode_item = produkTerpilih.id_produk;
                    baris.nama_barang = produkTerpilih.nama_produk;
                    baris.opsi_satuan = semuaOpsi.filter(p => p.id_produk === produkTerpilih.id_produk).map(p => ({ 
                        id: p.id_satuan, nama: p.nama_satuan, harga: p.harga_jual, stok: p.stok_real 
                    }));
                    baris.id_satuan = produkTerpilih.id_satuan;
                    baris.harga = produkTerpilih.harga_jual;
                    baris.error = '';
                    this.hitungSubtotal(index);
                    this.$nextTick(() => { document.getElementById('qty_' + index)?.focus(); });
                    if (index === this.barisTabel.length - 1) { this.tambahBarisBaru(); }
                },

                pilihProdukDariModal(produk) {
                    // Validasi Stok Modal
                    let stok = this.parseStokString(produk.stok_real);
                    if (stok < 1) {
                        alert('Stok Tidak Cukup (Kurang dari 1)! Sisa: ' + produk.stok_real);
                        return; 
                    }

                    if (this.barisYangSedangDiisi !== null) {
                        this.isiBaris(this.barisYangSedangDiisi, this.searchResultsModal, produk);
                        this.modalPencarianOpen = false;
                        this.searchResultsModal = [];
                        this.barisYangSedangDiisi = null;
                    }
                },

                updateHarga(index) { const baris = this.barisTabel[index]; const opsi = baris.opsi_satuan.find(o => o.id == baris.id_satuan); if (opsi) { baris.harga = opsi.harga; this.hitungSubtotal(index); } },
                
                bukaModalBayar() { if (this.grandTotal <= 0) { alert('Keranjang kosong!'); return; } this.modalBayarOpen = true; this.uangDiterima = ''; this.kembalian = 0; this.$nextTick(() => document.getElementById('input-bayar').focus()); },
                hitungKembalian() { this.kembalian = (this.uangDiterima || 0) - this.grandTotal; },
                
                async prosesBayar(cetakStruk = false) {
                    if (this.isProcessing) return;
                    if (this.uangDiterima < this.grandTotal) { alert('Uang kurang!'); return; }
                    
                    this.isProcessing = true;
                    const payload = { total_harga: this.grandTotal, bayar: this.uangDiterima, kembalian: this.kembalian, cetak: cetakStruk, items: this.barisTabel.filter(b => b.id_produk_final).map(item => ({ id_produk: item.id_produk_final, qty: item.qty, satuan: item.opsi_satuan.find(o => o.id == item.id_satuan)?.nama || 'PCS', harga: item.harga, subtotal: item.subtotal })) };
                    
                    if (payload.items.length === 0) { alert('Belum ada barang!'); this.isProcessing = false; return; }
                    try {
                        const response = await fetch("{{ route('kasir.transaksi.store') }}", { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify(payload) });
                        const result = await response.json();
                        if (result.success) { 
                            let msg = 'Transaksi Berhasil!';
                            if (cetakStruk) msg += '\n(Mencetak Struk...)';
                            alert(msg); 
                            window.location.reload(); 
                        } else { 
                            alert('Gagal: ' + result.message); 
                            this.isProcessing = false; 
                        }
                    } catch (error) { console.error(error); alert('Terjadi kesalahan sistem.'); this.isProcessing = false; }
                },
                
                resetForm() { if(confirm('Hapus semua?')) { this.barisTabel = []; this.tambahBarisBaru(); } },
                formatRupiah(angka) { return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 }).format(angka); },
                parseStokString(str) { return parseFloat(str.toString().replace(/\./g, '').replace(',', '.')) || 0; }
            }
        }
    </script>
    @endpush
</x-app-layout> 