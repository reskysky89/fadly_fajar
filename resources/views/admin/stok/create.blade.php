<x-app-layout>
    {{-- CSS KHUSUS --}}
    <style>
        input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        input[type=number] { -moz-appearance: textfield; }
    </style>

    {{-- CDN SWEETALERT2 (Wajib untuk Pop-up Cantik) --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-800 dark:text-white leading-tight">
            {{ __('Input Stok Masuk') }}
        </h2>
    </x-slot>

    <div class="h-[calc(100vh-65px)] flex flex-col bg-gray-100 dark:bg-gray-900" 
         x-data="stokMasukForm()"
         @keydown.window="handleGlobalKey($event)">

        {{-- BAGIAN 1: HEADER INFO --}}
        <div class="bg-white dark:bg-gray-800 shadow p-4 flex-shrink-0 z-20 border-b border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-9 grid grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 uppercase font-bold">ID Transaksi</label>
                        <input type="text" value="[OTOMATIS]" readonly class="w-full font-mono font-bold bg-gray-100 border-gray-300 rounded text-gray-500 text-sm cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 uppercase font-bold mb-1">Supplier</label>
                        <select x-model="id_supplier" class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Pilih Supplier --</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id_supplier }}">{{ $supplier->nama_supplier }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 uppercase font-bold mb-1">Tanggal Masuk</label>
                        <input type="date" x-model="tanggal_masuk" class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 uppercase font-bold mb-1">Keterangan</label>
                        <input type="text" x-model="keterangan" placeholder="Opsional..." class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                <div class="col-span-3 bg-gray-900 text-green-400 font-mono font-bold flex flex-col justify-center items-end px-4 rounded shadow-inner border-2 border-gray-700">
                    <span class="text-xs text-gray-400 uppercase tracking-widest">Total Faktur</span>
                    <span class="text-3xl tracking-tight" x-text="formatRupiah(grandTotal)">0</span>
                </div>
            </div>
        </div>

        {{-- BAGIAN 2: TABEL INPUT --}}
        <div class="flex-1 overflow-hidden p-2 bg-gray-100 dark:bg-gray-900 relative">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg h-full flex flex-col border border-gray-300 dark:border-gray-700">
                <div class="overflow-y-auto flex-1">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 relative">
                        <thead class="bg-gray-200 dark:bg-gray-700 sticky top-0 z-10 shadow-sm">
                            <tr>
                                <th class="px-4 py-2 text-center text-xs font-bold text-gray-700 uppercase w-10">No</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-700 uppercase w-48">Kode Item (Scan)</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-700 uppercase">Nama Barang</th>
                                <th class="px-4 py-2 text-center text-xs font-bold text-gray-700 uppercase w-24">Qty</th>
                                <th class="px-4 py-2 text-center text-xs font-bold text-gray-700 uppercase w-32">Satuan</th>
                                <th class="px-4 py-2 text-right text-xs font-bold text-gray-700 uppercase w-40">Harga Beli</th>
                                <th class="px-4 py-2 text-right text-xs font-bold text-gray-700 uppercase w-48">Subtotal</th>
                                <th class="px-4 py-2 text-center w-10"></th>
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
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="text" x-model="baris.nama_barang" readonly 
                                               class="w-full bg-transparent border-transparent text-gray-600 text-sm cursor-not-allowed focus:ring-0 h-9">
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="text" inputmode="numeric" x-model="baris.qty" 
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
                                               class="w-full text-center border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 font-bold h-9"
                                               :disabled="!baris.id_produk_final">
                                    </td>
                                    <td class="px-4 py-2">
                                        <select x-model="baris.id_satuan" :id="'satuan_' + index"
                                                @focus="activeRow = index" @change="updateHarga(index)"
                                                @keydown.delete.prevent="hapusBaris(index)"
                                                @keydown.enter.prevent="fokusBawah(index, 'kode')"
                                                @keydown.arrow-down.prevent="fokusBawah(index, 'satuan')"
                                                @keydown.arrow-up.prevent="fokusAtas(index, 'satuan')"
                                                @keydown.arrow-left.prevent="fokusKiri(index, 'qty')"
                                                @keydown.arrow-right.prevent="fokusKanan(index, 'harga')"
                                                class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 h-9 py-1"
                                                :disabled="!baris.id_produk_final">
                                            <template x-for="opsi in baris.opsi_satuan" :key="opsi.id">
                                                <option :value="opsi.id" x-text="opsi.nama"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        <input type="text" inputmode="numeric" x-model="baris.harga" 
                                               :id="'harga_' + index"
                                               @focus="activeRow = index; $el.select()"
                                               @click="$el.select()"
                                               @input="sanitizeHarga(index, $el)"
                                               @keydown.delete="hapusBaris(index)"
                                               @keydown.enter.prevent="fokusBawah(index, 'kode')"
                                               @keydown.arrow-down.prevent="fokusBawah(index, 'harga')"
                                               @keydown.arrow-up.prevent="fokusAtas(index, 'harga')"
                                               @keydown.arrow-left.prevent="fokusKiri(index, 'satuan')"
                                               class="w-full text-right border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 font-mono h-9"
                                               :disabled="!baris.id_produk_final">
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        <div class="font-bold font-mono text-gray-900 dark:text-gray-100 text-lg pt-1" x-text="formatRupiah(baris.subtotal)"></div>
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <button type="button" :id="'hapus_' + index"
                                                @click="hapusBaris(index)" 
                                                @keydown.arrow-left.prevent="fokusKiri(index, 'harga')"
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

        {{-- FOOTER (Tombol Batal & Simpan) --}}
        <div class="bg-gray-200 dark:bg-gray-800 p-3 border-t border-gray-300 dark:border-gray-700 flex-shrink-0 z-20">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    Shortcut: <span class="font-bold">[F2]</span> Baris Baru, <span class="font-bold">[END]</span> Simpan
                </div>
                <div class="flex space-x-2">
                    {{-- TOMBOL BATAL (Trigger Pop-up) --}}
                    <button @click="konfirmasiBatal()" class="px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white font-bold rounded shadow flex items-center gap-2">
                        BATAL [ESC]
                    </button>
                    
                    {{-- TOMBOL SIMPAN (Trigger Pop-up Pilihan) --}}
                    <button @click="konfirmasiSimpan()" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xl rounded shadow-lg flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                        PROSES [END]
                    </button>
                </div>
            </div>
        </div>

        {{-- MODAL PENCARIAN --}}
        <div x-show="modalPencarianOpen" class="fixed inset-0 bg-gray-600 bg-opacity-75 z-50 flex items-center justify-center" x-transition x-cloak>
            <div class="relative mx-auto p-0 border w-full max-w-3xl shadow-lg rounded-md bg-white dark:bg-gray-800 max-h-[80vh] flex flex-col" @click.away="modalPencarianOpen = false">
                <div class="p-4 bg-blue-600 text-white rounded-t-md flex justify-between items-center"><h3 class="text-xl font-bold">Pilih Item (Enter)</h3><button @click="modalPencarianOpen = false" class="text-white hover:text-gray-200 text-2xl">&times;</button></div>
                {{-- FITUR BARU: SEARCH BAR DALAM MODAL --}}
                <div class="p-3 bg-gray-100 dark:bg-gray-700 border-b border-gray-300 dark:border-gray-600">
                    <input type="text" 
                           id="modal-search-input" 
                           x-model="searchQueryModal" 
                           @keydown.enter.stop.prevent="searchProdukInModal()" 
                           @keydown.arrow-down.prevent="$el.blur(); navigasiModal('bawah')"
                           @keydown.arrow-up.prevent="$el.blur(); navigasiModal('atas')"
                           class="w-full border-gray-300 dark:border-gray-600 rounded-md p-2 text-sm focus:ring-blue-500 focus:border-blue-500" 
                           placeholder="Cari Nama/Kode Item (Tekan Enter)...">
                </div>
                <div class="overflow-y-auto flex-1" id="modal-list-container">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0"><tr><th>Kode</th><th>Nama</th><th>Satuan</th><th>Stok</th><th>Modal</th></tr></thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <template x-for="(produk, index) in searchResultsModal" :key="produk.unique_id">
                                <tr :id="'modal-row-' + index" :class="activeModalIndex === index ? 'bg-blue-100 dark:bg-blue-900' : 'hover:bg-gray-50 dark:hover:bg-gray-700'" class="cursor-pointer transition-colors" @click="pilihProdukDariModal(produk)" @mouseover="activeModalIndex = index">
                                    <td class="px-4 py-3 text-sm font-mono text-gray-600" x-text="produk.id_produk"></td><td class="px-4 py-3 text-sm font-bold text-gray-800 dark:text-gray-200" x-text="produk.nama_produk"></td><td class="px-4 py-3 text-center"><span class="px-2 py-1 bg-gray-200 rounded text-xs font-bold" x-text="produk.nama_satuan"></span></td><td class="px-4 py-3 text-center text-sm text-gray-500" x-text="produk.stok_real"></td><td class="px-4 py-3 text-right font-mono text-blue-600 font-bold" x-text="formatRupiah(produk.harga_pokok)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-t bg-gray-50 text-right rounded-b-lg"><button @click="modalPencarianOpen = false" class="px-4 py-2 bg-gray-500 text-white rounded text-sm">Tutup [ESC]</button></div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function stokMasukForm() {
            return {
                id_supplier: '', tanggal_masuk: "{{ date('Y-m-d') }}", keterangan: '',
                barisTabel: [], activeRow: 0,
                modalPencarianOpen: false, searchResultsModal: [], activeModalIndex: 0, barisYangSedangDiisi: null,
                isProcessing: false,

                // --- TAMBAHAN: Variable Search Modal ---
                searchQueryModal: '', 

                init() { this.tambahBarisBaru(); },

                handleGlobalKey(e) {
                    if (this.modalPencarianOpen) {
                        if (e.key === 'ArrowDown') { e.preventDefault(); this.navigasiModal('bawah'); }
                        else if (e.key === 'ArrowUp') { e.preventDefault(); this.navigasiModal('atas'); }
                        else if (e.key === 'Enter') { e.preventDefault(); this.pilihProdukViaEnter(); }
                        else if (e.key === 'Escape') { this.modalPencarianOpen = false; }
                        return;
                    }
                    if (e.key === 'End') { e.preventDefault(); this.konfirmasiSimpan(); }
                    if (e.key === 'Escape') { this.konfirmasiBatal(); }
                    if (e.key === 'F2') { e.preventDefault(); this.tambahBarisBaru(); }
                },

                // --- TAMBAHAN: Fungsi Search Dalam Modal ---
                async searchProdukInModal() {
                    if (this.searchQueryModal.length < 2) return;
                    try {
                        const response = await fetch(`{{ route('admin.stok.cariProduk') }}?search=${this.searchQueryModal}`);
                        const data = await response.json();
                        this.searchResultsModal = data;
                        this.activeModalIndex = 0;
                    } catch (error) { console.error(error); }
                },

                // --- UPDATE: Scan Produk (Pre-fill Search & Focus) ---
                async scanProduk(index) {
                    if (this.modalPencarianOpen) return;
                    const baris = this.barisTabel[index]; const kode = baris.kode_item;
                    if (kode.length < 2) return; if (baris.id_produk_final) return;

                    try {
                        const response = await fetch(`{{ route('admin.stok.cariProduk') }}?search=${kode}`);
                        const data = await response.json();
                        if (data.length === 0) { baris.error = 'Produk tidak ditemukan!'; baris.nama_barang = ''; }
                        else if (data.length === 1) { this.isiBaris(index, data, data[0]); }
                        else { 
                            this.searchResultsModal = data; 
                            this.activeModalIndex = 0; 
                            this.barisYangSedangDiisi = index; 
                            
                            // Set Search Box & Buka Modal
                            this.searchQueryModal = kode; 
                            this.modalPencarianOpen = true; 
                            
                            // Fokus ke input search modal
                            this.$nextTick(() => {
                                document.getElementById('modal-search-input').focus();
                                document.getElementById('modal-search-input').select();
                            });
                        }
                    } catch (error) { console.error(error); }
                },

                // ... (Fungsi Lainnya Tetap Sama) ...
                tambahBarisBaru() { this.barisTabel.push({ id_temp: Date.now() + Math.random(), kode_item: '', id_produk_final: '', nama_barang: '', qty: '', id_satuan: '', harga: 0, subtotal: 0, opsi_satuan: [], error: '' }); this.activeRow = this.barisTabel.length - 1; this.$nextTick(() => { document.getElementById('kode_' + this.activeRow)?.focus(); }); },
                hapusBaris(index) { this.barisTabel.splice(index, 1); if (this.barisTabel.length === 0) this.tambahBarisBaru(); else { this.activeRow = Math.min(index, this.barisTabel.length - 1); this.$nextTick(() => document.getElementById('kode_' + this.activeRow)?.focus()); } },
                sanitizeQty(index, el) { let val = el.value.replace(/[^0-9]/g, ''); this.barisTabel[index].qty = val; this.hitungSubtotal(index); },
                sanitizeHarga(index, el) { let val = el.value.replace(/[^0-9]/g, ''); this.barisTabel[index].harga = val; this.hitungSubtotal(index); },
                
                isiBaris(index, semuaOpsi, produkTerpilih) {
                    const baris = this.barisTabel[index];
                    baris.id_produk_final = produkTerpilih.id_produk; baris.kode_item = produkTerpilih.id_produk; baris.nama_barang = produkTerpilih.nama_produk;
                    baris.opsi_satuan = semuaOpsi.filter(p => p.id_produk === produkTerpilih.id_produk).map(p => ({ id: p.id_satuan, nama: p.nama_satuan, harga: p.harga_pokok }));
                    baris.id_satuan = produkTerpilih.id_satuan; baris.harga = produkTerpilih.harga_pokok; baris.error = '';
                    baris.qty = 1; this.hitungSubtotal(index);
                    this.$nextTick(() => { document.getElementById('qty_' + index)?.focus(); });
                    if (index === this.barisTabel.length - 1) { this.tambahBarisBaru(); }
                },
                pilihProdukDariModal(produk) { if (this.barisYangSedangDiisi !== null) { this.isiBaris(this.barisYangSedangDiisi, this.searchResultsModal, produk); this.modalPencarianOpen = false; this.searchResultsModal = []; this.barisYangSedangDiisi = null; } },
                pilihProdukViaEnter() { if (this.searchResultsModal.length > 0) { this.pilihProdukDariModal(this.searchResultsModal[this.activeModalIndex]); } },
                navigasiModal(arah) { if (arah === 'bawah') { if (this.activeModalIndex < this.searchResultsModal.length - 1) this.activeModalIndex++; this.scrollToModalItem(); } else if (arah === 'atas') { if (this.activeModalIndex > 0) this.activeModalIndex--; this.scrollToModalItem(); } },
                scrollToModalItem() { const el = document.getElementById('modal-row-' + this.activeModalIndex); el?.scrollIntoView({ block: 'nearest' }); },

                updateHarga(index) { const baris = this.barisTabel[index]; const opsi = baris.opsi_satuan.find(o => o.id == baris.id_satuan); if (opsi) { baris.harga = opsi.harga; this.hitungSubtotal(index); } },
                hitungSubtotal(index) { const baris = this.barisTabel[index]; let qty = parseInt(baris.qty) || 0; let harga = parseInt(baris.harga) || 0; baris.subtotal = qty * harga; },
                get grandTotal() { return this.barisTabel.reduce((sum, baris) => sum + baris.subtotal, 0); },
                fokusBawah(index, colName) { if (this.modalPencarianOpen) return; const nextIndex = index + 1; if (nextIndex < this.barisTabel.length) { this.activeRow = nextIndex; document.getElementById(colName + '_' + nextIndex)?.focus(); } else { this.tambahBarisBaru(); } },
                fokusAtas(index, colName) { if (this.modalPencarianOpen) return; if (index > 0) { this.activeRow = index - 1; document.getElementById(colName + '_' + (index - 1))?.focus(); } },
                fokusKanan(index, nextCol) { if (this.modalPencarianOpen) return; document.getElementById(nextCol + '_' + index)?.focus(); },
                fokusKiri(index, prevCol) { if (this.modalPencarianOpen) return; document.getElementById(prevCol + '_' + index)?.focus(); },

                konfirmasiBatal() { Swal.fire({ title: 'Batalkan Input?', text: "Semua data yang diisi akan hilang.", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Ya, Batalkan', cancelButtonText: 'Tidak' }).then((result) => { if (result.isConfirmed) { window.location.href = "{{ route('admin.stok.index') }}"; } }); },
                konfirmasiSimpan() { if (this.barisTabel.filter(b => b.id_produk_final).length === 0) { Swal.fire('Kosong', 'Belum ada barang yang diinput.', 'warning'); return; } Swal.fire({ title: 'Proses Stok Masuk', text: "Pilih aksi penyimpanan:", icon: 'question', showDenyButton: true, showCancelButton: true, confirmButtonColor: '#3085d6', denyButtonColor: '#28a745', cancelButtonColor: '#6c757d', confirmButtonText: 'Simpan Saja', denyButtonText: 'Simpan & Cetak Bukti', cancelButtonText: 'Batal' }).then((result) => { if (result.isConfirmed) { this.simpanStok(false); } else if (result.isDenied) { this.simpanStok(true); } }); },

                async simpanStok(cetakBukti) { if (this.isProcessing) return; this.isProcessing = true; Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } }); const items = this.barisTabel.filter(b => b.id_produk_final).map(item => ({ id_produk: item.id_produk_final, jumlah: item.qty, id_satuan: item.id_satuan, nama_satuan: item.opsi_satuan.find(o => o.id == item.id_satuan)?.nama || 'PCS', harga_beli_satuan: item.harga })); if (items.length === 0) { alert('Belum ada barang!'); this.isProcessing = false; return; } const payload = { id_supplier: this.id_supplier, tanggal_masuk: this.tanggal_masuk, keterangan: this.keterangan, detail: items, cetak: cetakBukti }; try { const response = await fetch("{{ route('admin.stok.store') }}", { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify(payload) }); if (response.ok || response.redirected) { window.location.href = "{{ route('admin.stok.index') }}"; } else { const result = await response.json(); Swal.fire('Gagal', (result.message || 'Terjadi kesalahan validasi'), 'error'); this.isProcessing = false; } } catch (error) { window.location.href = "{{ route('admin.stok.index') }}"; } },

                formatRupiah(angka) { return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 }).format(angka); }
            }
        }
    </script>
    @endpush
</x-app-layout>