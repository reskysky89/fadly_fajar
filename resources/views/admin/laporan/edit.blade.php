<x-app-layout>
    <style>
        input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        input[type=number] { -moz-appearance: textfield; }
        [x-cloak] { display: none !important; }
    </style>

    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-800 dark:text-white leading-tight">
            Edit Transaksi: {{ $transaksi->id_transaksi }}
        </h2>
    </x-slot>

    {{-- LOGIKA PHP: SIAPKAN DATA --}}
    @php
        $initialItems = $transaksi->details->map(function($item) {
            $opsiSatuan = [];
            
            // 1. Satuan Dasar
            $opsiSatuan[] = [
                'nama' => $item->produk->satuanDasar->nama_satuan ?? 'PCS',
                'harga' => $item->produk->harga_jual_dasar
            ];
            
            // 2. Satuan Konversi
            foreach($item->produk->produkKonversis as $konv) {
                $opsiSatuan[] = [
                    'nama' => $konv->satuan->nama_satuan,
                    'harga' => $konv->harga_jual_konversi
                ];
            }

            // 3. Force Match Satuan
            $satuanTersimpan = trim($item->satuan);
            $isFound = false;
            foreach ($opsiSatuan as $opt) {
                if (strtoupper($opt['nama']) == strtoupper($satuanTersimpan)) {
                    $isFound = true;
                    $satuanTersimpan = $opt['nama'];
                    break;
                }
            }
            if (!$isFound) {
                $opsiSatuan[] = [
                    'nama' => $satuanTersimpan,
                    'harga' => $item->harga_satuan
                ];
            }

            return [
                'id_temp' => rand(1000,9999),
                'id_produk_final' => $item->id_produk,
                'kode_item' => $item->id_produk,
                'nama_barang' => $item->produk->nama_produk ?? 'Item Dihapus',
                'qty' => $item->jumlah,
                'satuan' => $satuanTersimpan,
                'harga' => $item->harga_satuan,
                'subtotal' => $item->subtotal,
                'opsi_satuan' => $opsiSatuan,
                'error' => ''
            ];
        });
    @endphp

    <script>
        window.transaksiItems = {!! json_encode($initialItems) !!};
    </script>

    {{-- WRAPPER FULL SCREEN --}}
    <div class="h-[calc(100vh-65px)] flex flex-col bg-gray-100 dark:bg-gray-900" 
         x-data="transaksiEdit()"
         @keydown.window="handleGlobalKey($event)">
        
        {{-- BAGIAN 1: HEADER INFO (Sticky) --}}
        <div class="bg-white dark:bg-gray-800 shadow p-4 flex-shrink-0 z-20 border-b border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-9 grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 uppercase font-bold">Pelanggan</label>
                        <div class="font-bold text-gray-800 text-lg">{{ $transaksi->nama_pelanggan }}</div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 uppercase font-bold">Kasir Awal</label>
                        <div class="font-bold text-gray-800">{{ $transaksi->nama_kasir }}</div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 uppercase font-bold">Waktu Awal</label>
                        <div class="font-bold text-gray-800">{{ $transaksi->waktu_transaksi }}</div>
                    </div>
                </div>
                <div class="col-span-3 bg-blue-600 text-white font-mono font-bold flex flex-col justify-center items-end px-4 rounded shadow-inner">
                    <span class="text-xs text-blue-200 uppercase tracking-widest">Total Baru</span>
                    <span class="text-4xl tracking-tight" x-text="formatRupiah(grandTotal)">0</span>
                </div>
            </div>
        </div>

        {{-- BAGIAN 2: TABEL ITEM --}}
        <div class="flex-1 overflow-hidden p-2 bg-gray-100 dark:bg-gray-900 relative">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg h-full flex flex-col border border-gray-300 dark:border-gray-700">
                <div class="overflow-y-auto flex-1">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 relative">
                        <thead class="bg-gray-200 dark:bg-gray-700 sticky top-0 z-10 shadow-sm">
                            <tr>
                                <th class="px-4 py-2 text-center text-xs font-bold text-gray-700 uppercase w-10">No</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-700 uppercase w-48">Kode Item</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-700 uppercase">Nama Barang</th>
                                <th class="px-4 py-2 text-center text-xs font-bold text-gray-700 uppercase w-24">Qty</th>
                                <th class="px-4 py-2 text-center text-xs font-bold text-gray-700 uppercase w-32">Satuan</th>
                                <th class="px-4 py-2 text-right text-xs font-bold text-gray-700 uppercase w-40">Harga</th>
                                <th class="px-4 py-2 text-right text-xs font-bold text-gray-700 uppercase w-48">Total</th>
                                <th class="px-4 py-2 text-center w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                            <template x-for="(item, index) in items" :key="item.id_temp">
                                <tr :class="activeRow === index ? 'bg-blue-100 dark:bg-blue-900' : 'hover:bg-gray-50 dark:hover:bg-gray-700'" 
                                    class="transition-colors cursor-pointer" @click="activeRow = index">
                                    
                                    <td class="px-4 py-2 text-center font-mono text-sm text-gray-500" x-text="index + 1"></td>

                                    {{-- KODE ITEM --}}
                                    <td class="px-4 py-2">
                                        <input type="text" x-model="item.kode_item" :id="'kode_' + index"
                                               @focus="activeRow = index"
                                               @keydown.delete="hapusBaris(index)"
                                               @keydown.enter.prevent="scanProduk(index)"
                                               @keydown.tab="scanProduk(index)"
                                               @keydown.arrow-down.prevent="fokusBawah(index, 'kode')"
                                               @keydown.arrow-up.prevent="fokusAtas(index, 'kode')"
                                               @keydown.arrow-right.prevent="fokusKanan(index, 'qty')"
                                               @input="item.id_produk_final = ''" 
                                               class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 font-mono text-sm font-bold text-blue-700 uppercase h-9"
                                               placeholder="Scan...">
                                    </td>

                                    <td class="px-4 py-2">
                                        <input type="text" :value="item.nama_barang" readonly 
                                               class="w-full bg-transparent border-transparent text-gray-600 text-sm cursor-not-allowed focus:ring-0 h-9">
                                    </td>

                                    {{-- QTY --}}
                                    <td class="px-4 py-2">
                                        <input type="number" x-model.number="item.qty" :id="'qty_' + index"
                                               @focus="activeRow = index; $el.select()" 
                                               @input="hitungSubtotal(index)" 
                                               @keydown.delete="hapusBaris(index)"
                                               @keydown.enter.prevent="fokusBawah(index, 'kode')"
                                               @keydown.arrow-down.prevent="fokusBawah(index, 'qty')"
                                               @keydown.arrow-up.prevent="fokusAtas(index, 'qty')"
                                               @keydown.arrow-left.prevent="fokusKiri(index, 'kode')"
                                               @keydown.arrow-right.prevent="fokusKanan(index, 'satuan')"
                                               class="w-full text-center border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 font-bold h-9"
                                               :disabled="!item.id_produk_final">
                                    </td>

                                    {{-- SATUAN --}}
                                    <td class="px-4 py-2">
                                        <select x-model="item.satuan" :id="'satuan_' + index"
                                                @focus="activeRow = index" @change="gantiSatuan(index)"
                                                @keydown.delete.prevent="hapusBaris(index)"
                                                @keydown.enter.prevent="fokusBawah(index, 'kode')"
                                                @keydown.arrow-down.prevent="fokusBawah(index, 'satuan')"
                                                @keydown.arrow-up.prevent="fokusAtas(index, 'satuan')"
                                                @keydown.arrow-left.prevent="fokusKiri(index, 'qty')"
                                                @keydown.arrow-right.prevent="fokusKanan(index, 'harga')"
                                                class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 h-9 py-1"
                                                :disabled="!item.id_produk_final">
                                            <template x-for="opsi in item.opsi_satuan" :key="opsi.nama">
                                                <option :value="opsi.nama" x-text="opsi.nama"></option>
                                            </template>
                                        </select>
                                    </td>

                                    {{-- HARGA --}}
                                    <td class="px-4 py-2">
                                        <input type="number" x-model.number="item.harga" :id="'harga_' + index"
                                               @focus="activeRow = index; $el.select()" @click="$el.select()"
                                               @input="hitungSubtotal(index)"
                                               @keydown.delete="hapusBaris(index)"
                                               @keydown.enter.prevent="fokusBawah(index, 'kode')"
                                               @keydown.arrow-down.prevent="fokusBawah(index, 'harga')"
                                               @keydown.arrow-up.prevent="fokusAtas(index, 'harga')"
                                               @keydown.arrow-left.prevent="fokusKiri(index, 'satuan')"
                                               class="w-full text-right border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 font-mono h-9"
                                               :disabled="!item.id_produk_final">
                                    </td>

                                    <td class="px-4 py-2 text-right">
                                        <div class="font-bold font-mono text-gray-900 dark:text-gray-100 text-lg pt-1" x-text="formatRupiah(item.subtotal)"></div>
                                    </td>

                                    <td class="px-4 py-2 text-center">
                                        <button type="button" @click="hapusBaris(index)" class="text-gray-400 hover:text-red-600 transition-colors focus:outline-none" tabindex="-1">
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
                    Shortcut: <b>[F2]</b> Scan, <b>[END]</b> Simpan
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.laporan.index') }}" class="px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white font-bold rounded shadow flex items-center gap-2">BATAL [ESC]</a>
                    <button @click="bukaModalBayar()" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xl rounded shadow-lg flex items-center gap-2">
                        SIMPAN PERUBAHAN [END]
                    </button>
                </div>
            </div>
        </div>

        {{-- MODAL UPDATE --}}
        <div x-show="modalBayarOpen" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" x-transition x-cloak>
            <div class="bg-white w-full max-w-md p-6 rounded-lg shadow-2xl transform scale-100 transition-transform">
                <h2 class="text-2xl font-bold mb-4 text-gray-800 border-b pb-2">Update Transaksi</h2>
                
                <div class="space-y-4">
                    <div class="flex justify-between text-xl">
                        <span>Total Baru</span>
                        <span class="font-bold" x-text="formatRupiah(grandTotal)"></span>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Uang Diterima (Update)</label>
                        <input type="number" x-model.number="uangDiterima" id="input-bayar" 
                               @input="hitungKembalian()" 
                               @keydown.enter.prevent="prosesBayar(false)" 
                               @keydown.escape="modalBayarOpen = false" 
                               class="w-full text-right text-3xl font-bold border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2 text-blue-700" 
                               placeholder="0">
                    </div>
                    
                    <div class="flex justify-between text-xl pt-2 border-t text-blue-600">
                        <span>Kembalian</span>
                        <span class="font-bold" x-text="formatRupiah(kembalian)"></span>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-2">
                    <button @click="modalBayarOpen = false" class="px-4 py-2 bg-gray-500 text-white rounded">Batal</button>
                    {{-- TOMBOL 1: SIMPAN SAJA --}}
                    <button @click="prosesBayar(false)" class="px-4 py-2 bg-blue-600 text-white font-bold rounded shadow hover:bg-blue-700">
                        SIMPAN
                    </button>
                    {{-- TOMBOL 2: SIMPAN & CETAK --}}
                    <button @click="prosesBayar(true)" class="px-4 py-2 bg-green-600 text-white font-bold rounded shadow hover:bg-green-700 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        SIMPAN & CETAK
                    </button>
                </div>
            </div>
        </div>

        {{-- MODAL PENCARIAN (Sama Persis Kasir) --}}
        <div x-show="modalPencarianOpen" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" x-transition x-cloak>
            <div class="relative mx-auto p-0 border w-full max-w-3xl shadow-lg rounded-md bg-white dark:bg-gray-800 max-h-[80vh] flex flex-col" @click.away="modalPencarianOpen = false">
                <div class="p-4 bg-blue-600 text-white rounded-t-md flex justify-between items-center"><h3 class="text-xl font-bold">Pilih Item (Enter)</h3><button @click="modalPencarianOpen = false" class="text-white hover:text-gray-200 text-2xl">&times;</button></div>
                
                <div class="p-3 bg-gray-100 dark:bg-gray-700 border-b border-gray-300 dark:border-gray-600">
                    <input type="text" id="modal-search-input" x-model="searchQueryModal" @keydown.enter.stop.prevent="searchProdukInModal()" @keydown.arrow-down.prevent="$el.blur(); navigasiModal('bawah')" @keydown.arrow-up.prevent="$el.blur(); navigasiModal('atas')" class="w-full border-gray-300 dark:border-gray-600 rounded-md p-2 text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Cari Nama/Kode Item (Tekan Enter)...">
                </div>

                <div class="overflow-y-auto flex-1" id="modal-list-container">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0"><tr><th>Kode</th><th>Nama</th><th>Satuan</th><th>Stok</th><th>Harga</th></tr></thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <template x-for="(produk, index) in searchResultsModal" :key="produk.unique_id">
                                <tr :id="'modal-row-' + index" :class="activeModalIndex === index ? 'bg-blue-100 dark:bg-blue-900' : 'hover:bg-gray-50 dark:hover:bg-gray-700'" class="cursor-pointer transition-colors" @click="pilihProdukDariModal(produk)" @mouseover="activeModalIndex = index">
                                    <td class="px-4 py-3 text-sm font-mono text-gray-600" x-text="produk.id_produk"></td><td class="px-4 py-3 text-sm font-bold text-gray-800 dark:text-gray-200" x-text="produk.nama_produk"></td><td class="px-4 py-3 text-center"><span class="bg-gray-200 px-2 py-1 rounded text-xs font-bold" x-text="produk.nama_satuan"></span></td><td class="px-4 py-3 text-center text-sm font-bold" :class="parseFloat(produk.stok_real.replace(',', '.')) > 0 ? 'text-green-600' : 'text-red-600'" x-text="produk.stok_real"></td><td class="px-4 py-3 text-right font-mono text-blue-600 font-bold" x-text="formatRupiah(produk.harga_jual)"></td>
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
        function transaksiEdit() {
            return {
                items: window.transaksiItems || [],
                activeRow: 0,
                modalBayarOpen: false,
                uangDiterima: {{ $transaksi->bayar }},
                kembalian: {{ $transaksi->kembalian }},
                isProcessing: false,
                modalPencarianOpen: false,
                searchResultsModal: [],
                activeModalIndex: 0,
                searchQueryModal: '',

                init() {
                    if(this.items.length === 0) this.tambahBarisBaru();
                    this.items.forEach(item => this.ensureReorderAfterFill(item));
                },

                // LOGIKA ANTI NaN
                hitungSubtotal(index) {
                    const item = this.items[index];
                    let qty = parseFloat(item.qty) || 0;
                    let harga = parseFloat(item.harga) || 0;
                    item.subtotal = qty * harga;
                },

                get grandTotal() { 
                    return this.items.reduce((sum, item) => sum + (parseFloat(item.subtotal) || 0), 0); 
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
                        if (e.key === 'Enter' && !this.isProcessing) { e.preventDefault(); this.prosesBayar(false); } 
                        else if (e.key === 'Escape') { this.modalBayarOpen = false; } 
                        return; 
                    }
                    if (e.key === 'End') { e.preventDefault(); this.bukaModalBayar(); }
                    if (e.key === 'F2') { e.preventDefault(); this.tambahBarisBaru(); }
                    if (e.key === 'Escape') { window.location.href = "{{ route('admin.laporan.index') }}"; }
                },

                reorderSatuanOptions(item) {
                    if (!item || !Array.isArray(item.opsi_satuan)) return;
                    const selectedName = item.satuan;
                    if (!selectedName) return;
                    const idx = item.opsi_satuan.findIndex(o => o.nama === selectedName);
                    if (idx > 0) {
                        const [sel] = item.opsi_satuan.splice(idx, 1);
                        item.opsi_satuan.unshift(sel);
                    }
                },
                ensureReorderAfterFill(item) { if (item.satuan) this.reorderSatuanOptions(item); },

                gantiSatuan(index) {
                    const item = this.items[index];
                    const opsi = item.opsi_satuan.find(o => o.nama === item.satuan);
                    if (opsi) {
                        item.harga = opsi.harga;
                        this.hitungSubtotal(index);
                    }
                },
                hapusBaris(index) {
                    this.items.splice(index, 1);
                    if(this.items.length === 0) this.tambahBarisBaru();
                },

                tambahBarisBaru() { this.items.push({ id_temp: Date.now() + Math.random(), kode_item: '', id_produk_final: '', nama_barang: '', qty: 1, satuan: '', harga: 0, subtotal: 0, opsi_satuan: [], error: '' }); this.activeRow = this.items.length - 1; this.$nextTick(() => { document.getElementById('kode_' + this.activeRow)?.focus(); }); },

                bukaModalBayar() { 
                    if (this.grandTotal <= 0) { alert('Pesanan kosong!'); return; } 
                    this.modalBayarOpen = true; 
                    this.hitungKembalian(); 
                    this.$nextTick(() => document.getElementById('input-bayar').focus()); 
                },
                hitungKembalian() { this.kembalian = (this.uangDiterima || 0) - this.grandTotal; },

                async prosesBayar(cetakStruk) {
                    if (this.isProcessing) return;
                    if (this.uangDiterima < this.grandTotal) { alert('Uang kurang!'); return; }
                    
                    this.isProcessing = true;
                    const payload = { 
                        total_harga: this.grandTotal, 
                        bayar: this.uangDiterima, 
                        kembalian: this.kembalian, 
                        items: this.items.filter(b => b.id_produk_final).map(item => ({ 
                            id_produk: item.id_produk_final, 
                            qty: item.qty, 
                            satuan: item.satuan, 
                            harga: item.harga, 
                            subtotal: item.subtotal 
                        })) 
                    };

                    try {
                        const urlUpdate = "{{ route('admin.laporan.update') }}?id=" + encodeURIComponent("{{ $transaksi->id_transaksi }}");
                        const response = await fetch(urlUpdate, { 
                            method: 'PUT', 
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, 
                            body: JSON.stringify(payload) 
                        });
                        const result = await response.json();
                        if (result.success) { 
                            alert('Transaksi Berhasil Diupdate!'); 
                            
                            // JIKA TOMBOL CETAK DITEKAN
                            if (cetakStruk) {
                                let urlStruk = "{{ route('kasir.transaksi.cetak', ['id' => $transaksi->id_transaksi]) }}";
                                window.open(urlStruk, '_blank');
                            }

                            // REDIRECT KE LAPORAN INDEX
                            window.location.href = "{{ route('admin.laporan.index') }}"; 
                        } else { 
                            alert('Gagal: ' + result.message); 
                            this.isProcessing = false; 
                        }
                    } catch (error) { console.error(error); alert('Terjadi kesalahan sistem.'); this.isProcessing = false; }
                },

                fokusBawah(index, colName) { const nextIndex = index + 1; if (nextIndex < this.items.length) { this.activeRow = nextIndex; document.getElementById(colName + '_' + nextIndex)?.focus(); } else { this.tambahBarisBaru(); } },
                fokusAtas(index, colName) { if (index > 0) { this.activeRow = index - 1; document.getElementById(colName + '_' + (index - 1))?.focus(); } },
                fokusKanan(index, nextCol) { document.getElementById(nextCol + '_' + index)?.focus(); },
                fokusKiri(index, prevCol) { document.getElementById(prevCol + '_' + index)?.focus(); },
                
                async scanProduk(index) { if (this.modalPencarianOpen) return; const item = this.items[index]; const kode = item.kode_item; if (kode.length < 2) return; if (item.id_produk_final) return; try { const response = await fetch(`{{ route('kasir.transaksi.cariProduk') }}?search=${kode}`); const data = await response.json(); if (data.length === 0) { item.error = 'Produk tidak ditemukan!'; item.nama_barang = ''; } else if (data.length === 1) { this.isiBaris(index, data, data[0]); } else { this.searchResultsModal = data; this.activeModalIndex = 0; this.searchQueryModal = kode; this.barisYangAkanDiisi = index; this.modalPencarianOpen = true; this.$nextTick(() => { document.getElementById('modal-search-input').focus(); document.getElementById('modal-search-input').select(); }); } } catch (error) { console.error(error); } },
                async searchProdukInModal() { if (this.searchQueryModal.length < 2) return; try { const response = await fetch(`{{ route('kasir.transaksi.cariProduk') }}?search=${this.searchQueryModal}`); const data = await response.json(); this.searchResultsModal = data; this.activeModalIndex = 0; } catch (error) { console.error(error); } },
                isiBaris(index, semuaOpsi, produkTerpilih) { const item = this.items[index]; item.id_produk_final = produkTerpilih.id_produk; item.kode_item = produkTerpilih.id_produk; item.nama_barang = produkTerpilih.nama_produk; item.opsi_satuan = semuaOpsi.filter(p => p.id_produk === produkTerpilih.id_produk).map(p => ({ nama: p.nama_satuan, harga: p.harga_jual, stok: p.stok_real })); item.satuan = produkTerpilih.nama_satuan; item.harga = produkTerpilih.harga_jual; item.error = ''; this.ensureReorderAfterFill(item); this.hitungSubtotal(index); this.$nextTick(() => { document.getElementById('qty_' + index)?.focus(); }); if (index === this.items.length - 1) { this.tambahBarisBaru(); } },
                pilihProdukDariModal(produk) { const index = this.barisYangAkanDiisi; this.isiBaris(index, this.searchResultsModal, produk); this.modalPencarianOpen = false; this.searchResultsModal = []; this.barisYangAkanDiisi = null; },
                navigasiModal(arah) { if (arah === 'bawah') { if (this.activeModalIndex < this.searchResultsModal.length - 1) this.activeModalIndex++; this.scrollToModalItem(); } else if (arah === 'atas') { if (this.activeModalIndex > 0) this.activeModalIndex--; this.scrollToModalItem(); } },
                scrollToModalItem() { const el = document.getElementById('modal-row-' + this.activeModalIndex); el?.scrollIntoView({ block: 'nearest' }); },
                pilihProdukViaEnter() { if (this.searchResultsModal.length > 0) { this.pilihProdukDariModal(this.searchResultsModal[this.activeModalIndex]); } },
                formatRupiah(angka) { return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 }).format(angka || 0); }
            }
        }
    </script>
    @endpush
</x-app-layout>