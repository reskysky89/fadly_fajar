<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-gray-800 dark:text-white leading-tight">
                {{ __('Penjualan Kasir') }}
            </h2>
            {{-- Jam Digital --}}
            <div class="text-2xl font-mono font-bold text-blue-600 bg-white px-4 py-2 rounded shadow" x-data x-init="setInterval(() => $el.innerText = new Date().toLocaleTimeString('id-ID'), 1000)">
                {{ date('H:i:s') }}
            </div>
        </div>
    </x-slot>

    <div class="h-[calc(100vh-140px)] flex flex-col" x-data="transaksiKasir()">
        
        {{-- BAGIAN 1: HEADER & TOTAL (Tidak Berubah) --}}
        <div class="bg-white dark:bg-gray-800 shadow p-4 flex-shrink-0 z-20">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-8 grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 uppercase font-bold">No. Transaksi</label>
                        <div class="font-mono font-bold text-lg text-gray-800 dark:text-gray-200 bg-gray-50 p-2 rounded border">
                            {{ $nextId }}
                        </div>
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
                    <span class="text-5xl" x-text="formatRupiah(grandTotal)">0</span>
                </div>
            </div>
        </div>

        {{-- BAGIAN 2: TABEL TRANSAKSI (Tidak Berubah) --}}
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
                                        <p x-show="baris.error" x-text="baris.error" class="text-xs text-red-500 mt-1"></p>
                                    </td>

                                    <td class="px-4 py-2">
                                        <input type="text" x-model="baris.nama_barang" readonly @focus="activeRow = index"
                                               class="w-full bg-transparent border-transparent rounded-md text-gray-600 text-sm cursor-not-allowed focus:ring-0 h-9">
                                    </td>

                                    <td class="px-4 py-2">
                                        <input type="number" x-model.number="baris.qty" :id="'qty_' + index"
                                               @focus="activeRow = index" @input="hitungSubtotal(index)"
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
                                                class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 h-9 py-1">
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
                                        <button type="button" @click="hapusBaris(index)" x-show="barisTabel.length > 1" class="text-gray-400 hover:text-red-600 transition-colors" tabindex="-1">
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

        {{-- BAGIAN 3: FOOTER (Tidak Berubah) --}}
        <div class="bg-gray-200 dark:bg-gray-800 p-3 border-t border-gray-300 dark:border-gray-700 flex-shrink-0 z-20">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    Shortcut: <span class="font-bold">[F2]</span> Scan, <span class="font-bold">[END]</span> Bayar, <span class="font-bold">[DEL]</span> Hapus Baris
                </div>
                <div class="flex space-x-2">
                    <button @click="resetForm()" class="px-6 py-3 bg-red-500 hover:bg-red-600 text-white font-bold rounded shadow flex items-center gap-2">BATAL [ESC]</button>
                    <button @click="bukaModalBayar()" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xl rounded shadow-lg flex items-center gap-2">BAYAR [END]</button>
                </div>
            </div>
        </div>

        {{-- =================================================== --}}
        {{-- MODAL KONFIRMASI HAPUS (BARU) --}}
        {{-- =================================================== --}}
        <div x-show="modalHapusOpen" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" x-transition x-cloak>
            <div class="bg-white dark:bg-gray-800 w-full max-w-sm p-6 rounded-lg shadow-2xl transform scale-100 transition-transform">
                <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-white">Hapus Item?</h3>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    Apakah Anda ingin menghapus <span class="font-bold text-red-600" x-text="namaBarangHapus"></span> dari daftar?
                </p>
                <div class="flex justify-end space-x-3">
                    {{-- Tombol Tidak (Fokus Awal) --}}
                    <button id="btn-hapus-tidak" 
                            @click="modalHapusOpen = false" 
                            @keydown.arrow-left.prevent="document.getElementById('btn-hapus-ya').focus()"
                            @keydown.arrow-right.prevent="document.getElementById('btn-hapus-ya').focus()"
                            class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded focus:ring-2 focus:ring-gray-400">
                        Tidak
                    </button>
                    {{-- Tombol Ya --}}
                    <button id="btn-hapus-ya" 
                            @click="konfirmasiHapus()" 
                            @keydown.arrow-left.prevent="document.getElementById('btn-hapus-tidak').focus()"
                            @keydown.arrow-right.prevent="document.getElementById('btn-hapus-tidak').focus()"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded font-bold focus:ring-2 focus:ring-red-500">
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>

        {{-- MODAL PEMBAYARAN (Sama) --}}
        <div x-show="modalBayarOpen" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" x-transition x-cloak>
            <div class="bg-white dark:bg-gray-800 w-full max-w-md p-6 rounded-lg shadow-2xl transform scale-100 transition-transform">
                <h2 class="text-2xl font-bold mb-4 text-gray-800 dark:text-white border-b pb-2">Pembayaran</h2>
                <div class="space-y-4">
                    <div class="flex justify-between text-xl"><span>Total Tagihan</span><span class="font-bold" x-text="formatRupiah(grandTotal)"></span></div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Uang Diterima</label>
                        <input type="number" x-model.number="uangDiterima" id="input-bayar" @input="hitungKembalian()" @keydown.enter.prevent="prosesBayar()" @keydown.escape="modalBayarOpen = false" class="w-full text-right text-3xl font-bold border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2" placeholder="0">
                    </div>
                    <div class="flex justify-between text-xl pt-2 border-t text-blue-600"><span>Kembalian</span><span class="font-bold" x-text="formatRupiah(kembalian)"></span></div>
                </div>
                <div class="mt-6 flex justify-end space-x-2">
                    <button @click="modalBayarOpen = false" class="px-4 py-2 bg-gray-500 text-white rounded">Tutup</button>
                    <button @click="prosesBayar()" :disabled="uangDiterima < grandTotal" class="px-6 py-2 bg-blue-600 text-white font-bold rounded" :class="uangDiterima < grandTotal ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700'">PROSES (Enter)</button>
                </div>
            </div>
        </div>

        {{-- MODAL PENCARIAN (Sama) --}}
        <div x-show="modalPencarianOpen" class="fixed inset-0 bg-gray-600 bg-opacity-75 z-50 flex items-center justify-center" x-transition x-cloak>
            <div class="relative mx-auto p-6 border w-full max-w-2xl shadow-lg rounded-md bg-white dark:bg-gray-800">
                <h3 class="text-xl font-medium mb-4">Daftar Item Ditemukan</h3>
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700"><tr><th>Kode</th><th>Nama</th><th>Satuan</th><th>Harga</th><th>Aksi</th></tr></thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <template x-for="produk in searchResultsModal" :key="produk.unique_id">
                            <tr class="hover:bg-gray-100 dark:hover:bg-gray-600"><td class="px-6 py-4" x-text="produk.id_produk"></td><td class="px-6 py-4" x-text="produk.nama_produk"></td><td class="px-6 py-4 font-bold" x-text="produk.nama_satuan"></td><td class="px-6 py-4" x-text="formatRupiah(produk.harga_jual)"></td><td class="px-6 py-4"><button @click="pilihProdukDariModal(produk)" class="px-3 py-1 bg-blue-600 text-white rounded-md text-sm">Pilih</button></td></tr>
                        </template>
                    </tbody>
                </table>
                <div class="mt-4 flex justify-end"><button @click="modalPencarianOpen = false" class="px-4 py-2 bg-gray-500 text-white rounded">Tutup</button></div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function transaksiKasir() {
            return {
                barisTabel: [], activeRow: 0,
                modalBayarOpen: false, modalPencarianOpen: false, searchResultsModal: [], barisYangSedangDiisi: null,
                uangDiterima: '', kembalian: 0,
                
                // Variabel untuk Modal Hapus
                modalHapusOpen: false,
                indexBarisHapus: null,
                namaBarangHapus: '',

                init() {
                    this.tambahBarisBaru();
                    window.addEventListener('keydown', (e) => {
                        if (e.key === 'End') { e.preventDefault(); this.bukaModalBayar(); }
                        if (e.key === 'Escape') { 
                            this.modalBayarOpen = false; 
                            this.modalPencarianOpen = false; 
                            this.modalHapusOpen = false;
                        }
                        if (e.key === 'F2') { e.preventDefault(); this.tambahBarisBaru(); }
                    });
                },

                tambahBarisBaru() {
                    this.barisTabel.push({ id_temp: Date.now() + Math.random(), kode_item: '', id_produk_final: '', nama_barang: '', qty: 1, id_satuan: '', harga: 0, subtotal: 0, opsi_satuan: [], error: '' });
                    this.activeRow = this.barisTabel.length - 1;
                    this.$nextTick(() => { document.getElementById('kode_' + this.activeRow)?.focus(); });
                },

                // --- LOGIKA HAPUS DENGAN KONFIRMASI ---
                hapusBaris(index) {
                    const baris = this.barisTabel[index];
                    
                    // Jika baris kosong/belum ada nama barang, hapus langsung (tidak perlu tanya)
                    if (!baris.nama_barang || baris.nama_barang === '') {
                        this.barisTabel.splice(index, 1);
                        if (this.barisTabel.length === 0) this.tambahBarisBaru();
                        
                        // Reset fokus
                        if (this.activeRow >= this.barisTabel.length) this.activeRow = this.barisTabel.length - 1;
                        this.$nextTick(() => document.getElementById('kode_' + this.activeRow)?.focus());
                        return;
                    }

                    // Jika ada barangnya, TANYA DULU via Modal
                    this.indexBarisHapus = index;
                    this.namaBarangHapus = baris.nama_barang;
                    this.modalHapusOpen = true;

                    // Fokus ke tombol "Tidak" untuk keamanan
                    this.$nextTick(() => document.getElementById('btn-hapus-tidak').focus());
                },

                konfirmasiHapus() {
                    if (this.indexBarisHapus !== null) {
                        this.barisTabel.splice(this.indexBarisHapus, 1);
                        if (this.barisTabel.length === 0) this.tambahBarisBaru();
                        
                        // Adjust active row & Focus
                        if (this.activeRow >= this.barisTabel.length) this.activeRow = this.barisTabel.length - 1;
                        this.$nextTick(() => document.getElementById('kode_' + this.activeRow)?.focus());
                    }
                    this.modalHapusOpen = false;
                    this.indexBarisHapus = null;
                },
                // ---------------------------------------

                async scanProduk(index) {
                    if (this.modalPencarianOpen || this.modalHapusOpen) return; // Cegah scan saat modal terbuka
                    const baris = this.barisTabel[index];
                    const kode = baris.kode_item;
                    if (kode.length < 2) return;
                    if (baris.id_produk_final && baris.id_produk_final !== '') return;

                    try {
                        const response = await fetch(`{{ route('kasir.transaksi.cariProduk') }}?search=${kode}`);
                        const data = await response.json();
                        if (data.length === 0) { baris.error = 'Produk tidak ditemukan!'; baris.nama_barang = ''; } 
                        else if (data.length === 1) { this.isiBaris(index, data, data[0]); } 
                        else { this.searchResultsModal = data; this.barisYangSedangDiisi = index; this.modalPencarianOpen = true; }
                    } catch (error) { console.error(error); }
                },
                
                // ... (Fungsi isiBaris, pilihProdukDariModal, updateHarga, hitungSubtotal, grandTotal SAMA PERSIS) ...
                isiBaris(index, semuaOpsi, produkTerpilih) {
                    const baris = this.barisTabel[index];
                    baris.id_produk_final = produkTerpilih.id_produk; baris.kode_item = produkTerpilih.id_produk; baris.nama_barang = produkTerpilih.nama_produk;
                    baris.opsi_satuan = semuaOpsi.filter(p => p.id_produk === produkTerpilih.id_produk).map(p => ({ id: p.id_satuan, nama: p.nama_satuan, harga: p.harga_jual }));
                    baris.id_satuan = produkTerpilih.id_satuan; baris.harga = produkTerpilih.harga_jual; baris.error = '';
                    this.hitungSubtotal(index);
                    this.$nextTick(() => { document.getElementById('qty_' + index)?.focus(); });
                    if (index === this.barisTabel.length - 1) { this.tambahBarisBaru(); }
                },
                pilihProdukDariModal(produk) { if (this.barisYangSedangDiisi !== null) { this.isiBaris(this.barisYangSedangDiisi, this.searchResultsModal, produk); this.modalPencarianOpen = false; this.searchResultsModal = []; this.barisYangSedangDiisi = null; } },
                updateHarga(index) { const baris = this.barisTabel[index]; const opsi = baris.opsi_satuan.find(o => o.id == baris.id_satuan); if (opsi) { baris.harga = opsi.harga; this.hitungSubtotal(index); } },
                hitungSubtotal(index) { const baris = this.barisTabel[index]; baris.subtotal = baris.qty * baris.harga; },
                get grandTotal() { return this.barisTabel.reduce((sum, baris) => sum + baris.subtotal, 0); },

                // Navigasi Keyboard
                fokusBawah(index, colName) {
                    const nextIndex = index + 1;
                    if (nextIndex < this.barisTabel.length) { this.activeRow = nextIndex; document.getElementById(colName + '_' + nextIndex)?.focus(); } 
                    else { this.tambahBarisBaru(); }
                },
                fokusAtas(index, colName) { if (index > 0) { this.activeRow = index - 1; document.getElementById(colName + '_' + (index - 1))?.focus(); } },
                fokusKanan(index, nextCol) { document.getElementById(nextCol + '_' + index)?.focus(); },
                fokusKiri(index, prevCol) { document.getElementById(prevCol + '_' + index)?.focus(); },

                // Pembayaran (Sama)
                bukaModalBayar() { if (this.grandTotal <= 0) { alert('Keranjang kosong!'); return; } this.modalBayarOpen = true; this.uangDiterima = ''; this.kembalian = 0; this.$nextTick(() => document.getElementById('input-bayar').focus()); },
                hitungKembalian() { this.kembalian = (this.uangDiterima || 0) - this.grandTotal; },
                async prosesBayar() {
                    if (this.uangDiterima < this.grandTotal) { alert('Uang kurang!'); return; }
                    const payload = { id_transaksi: "{{ $nextId }}", total_harga: this.grandTotal, bayar: this.uangDiterima, kembalian: this.kembalian, items: this.barisTabel.filter(b => b.id_produk_final).map(item => ({ id_produk: item.id_produk_final, qty: item.qty, satuan: item.opsi_satuan.find(o => o.id == item.id_satuan)?.nama || 'PCS', harga: item.harga, subtotal: item.subtotal })) };
                    if (payload.items.length === 0) return;
                    try { const response = await fetch("{{ route('kasir.transaksi.store') }}", { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify(payload) }); const result = await response.json(); if (result.success) { alert('Transaksi Berhasil!'); window.location.reload(); } else { alert('Gagal: ' + result.message); } } catch (error) { console.error(error); alert('Terjadi kesalahan sistem.'); }
                },
                resetForm() { if(confirm('Hapus semua?')) { this.barisTabel = []; this.tambahBarisBaru(); } },
                formatRupiah(angka) { return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 }).format(angka); }
            }
        }
    </script>
    @endpush
</x-app-layout>