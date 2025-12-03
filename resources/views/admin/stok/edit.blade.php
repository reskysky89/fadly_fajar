<x-app-layout>
    {{-- CSS: Hilangkan Spinner Input Number --}}
    <style>
        input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        input[type=number] { -moz-appearance: textfield; }
    </style>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Transaksi Stok Masuk') }}
        </h2>
    </x-slot>

    {{-- LOGIKA PHP: SIAPKAN DATA --}}
    @php
        $initialData = [];
        foreach ($batch->details as $index => $detail) {
            $satuan_options = [];
            
            // 1. Satuan Dasar
            $satuan_options[] = [
                'nama' => $detail->produk->satuanDasar->nama_satuan ?? 'PCS',
                'harga' => $detail->produk->harga_pokok_dasar,
            ];
            
            // 2. Satuan Konversi
            foreach ($detail->produk->produkKonversis as $konv) {
                $satuan_options[] = [
                    'nama' => $konv->satuan->nama_satuan,
                    'harga' => $konv->harga_pokok_konversi,
                ];
            }

            // 3. Pastikan Satuan Lama Ada (Match by Name)
            $satuanTersimpan = trim($detail->satuan); 
            $isFound = false;
            foreach ($satuan_options as $opt) {
                if (strtoupper($opt['nama']) == strtoupper($satuanTersimpan)) {
                    $isFound = true; 
                    $satuanTersimpan = $opt['nama']; // Pakai casing dari opsi
                    break;
                }
            }
            
            if (!$isFound) {
                $satuan_options[] = [
                    'nama' => $satuanTersimpan,
                    'harga' => $detail->harga_beli_satuan
                ];
            }

            $initialData[] = [
                'id_temp' => rand(1000,9999), // ID Unik untuk key Alpine
                'id_produk_final' => $detail->id_produk,
                'kode_item' => $detail->id_produk, // Digunakan untuk input scan
                'nama_produk' => $detail->produk->nama_produk,
                'satuan_options' => $satuan_options,
                
                'satuan' => $satuanTersimpan, // Value untuk Dropdown
                
                'jumlah' => $detail->jumlah,
                'harga' => $detail->harga_beli_satuan,
                'subtotal' => $detail->jumlah * $detail->harga_beli_satuan,
                'error' => ''
            ];
        }
    @endphp

    <script>
        window.stokEditData = {!! json_encode($initialData) !!};
    </script>

    <div class="h-[calc(100vh-65px)] flex flex-col bg-gray-100 dark:bg-gray-900" 
         x-data="stokMasukEditForm()"
         @keydown.window="handleGlobalKey($event)">

        {{-- BAGIAN 1: HEADER INFO --}}
        <div class="bg-white dark:bg-gray-800 shadow p-4 flex-shrink-0 z-20 border-b border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-9 grid grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 uppercase font-bold">ID Transaksi</label>
                        <input type="text" value="STOK-{{ str_pad($batch->id_batch_stok, 4, '0', STR_PAD_LEFT) }}" disabled 
                               class="w-full font-mono font-bold bg-gray-100 border-gray-300 rounded text-gray-500 text-sm cursor-not-allowed">
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
                        <input type="text" x-model="keterangan" class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                {{-- Kanan: Total Besar --}}
                <div class="col-span-3 bg-gray-900 text-green-400 font-mono font-bold flex flex-col justify-center items-end px-4 rounded shadow-inner border-2 border-gray-700">
                    <span class="text-xs text-gray-400 uppercase tracking-widest">Total Faktur</span>
                    <span class="text-3xl tracking-tight" x-text="formatRupiah(totalFaktur)">0</span>
                </div>
            </div>
        </div>

        {{-- BAGIAN 2: TABEL EDIT (Scrollable) --}}
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

                                    {{-- KODE ITEM --}}
                                    <td class="px-4 py-2">
                                        <input type="text" x-model="baris.kode_item" :id="'kode_' + index"
                                               @focus="activeRow = index"
                                               @keydown.delete="hapusBaris(index)"
                                               @keydown.enter.prevent="scanProduk(index)"
                                               @keydown.tab="scanProduk(index)"
                                               @keydown.arrow-down.prevent="fokusBawah(index, 'kode')"
                                               @keydown.arrow-up.prevent="fokusAtas(index, 'kode')"
                                               @keydown.arrow-right.prevent="fokusKanan(index, 'qty')"
                                               class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 font-mono text-sm font-bold text-blue-700 uppercase h-9" 
                                               placeholder="Scan...">
                                        <p x-show="baris.error" x-text="baris.error" class="text-xs text-red-500 mt-1"></p>
                                    </td>

                                    <td class="px-4 py-2">
                                        <input type="text" x-model="baris.nama_produk" readonly 
                                               class="w-full bg-transparent border-transparent text-gray-600 text-sm cursor-not-allowed focus:ring-0 h-9">
                                    </td>

                                    {{-- QTY --}}
                                    <td class="px-4 py-2">
                                        <input type="text" inputmode="numeric" x-model="baris.jumlah" :id="'qty_' + index"
                                               @focus="activeRow = index; $el.select()" @click="$el.select()" @input="sanitizeQty(index, $el)" 
                                               @keydown.delete="hapusBaris(index)" @keydown.enter.prevent="fokusBawah(index, 'kode')"
                                               @keydown.arrow-down.prevent="fokusBawah(index, 'jumlah')" @keydown.arrow-up.prevent="fokusAtas(index, 'jumlah')"
                                               @keydown.arrow-left.prevent="fokusKiri(index, 'kode')" @keydown.arrow-right.prevent="fokusKanan(index, 'satuan')"
                                               class="w-full text-center border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 font-bold h-9"
                                               :disabled="!baris.id_produk_final">
                                    </td>

                                    {{-- SATUAN --}}
                                    <td class="px-4 py-2">
                                        <select x-model="baris.satuan" :id="'satuan_' + index"
                                                @focus="activeRow = index" @change="updateHarga(index)"
                                                @keydown.delete.prevent="hapusBaris(index)"
                                                @keydown.enter.prevent="fokusBawah(index, 'kode')"
                                                @keydown.arrow-down.prevent="fokusBawah(index, 'satuan')"
                                                @keydown.arrow-up.prevent="fokusAtas(index, 'satuan')"
                                                @keydown.arrow-left.prevent="fokusKiri(index, 'jumlah')"
                                                @keydown.arrow-right.prevent="fokusKanan(index, 'harga')"
                                                class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 h-9 py-1"
                                                :disabled="!baris.id_produk_final">
                                            <template x-for="opsi in baris.satuan_options" :key="opsi.nama">
                                                <option :value="opsi.nama" x-text="opsi.nama"></option>
                                            </template>
                                        </select>
                                    </td>

                                    {{-- HARGA BELI --}}
                                    <td class="px-4 py-2">
                                        <input type="text" inputmode="numeric" x-model="baris.harga" :id="'harga_' + index"
                                               @focus="activeRow = index; $el.select()" @click="$el.select()" @input="sanitizeHarga(index, $el)"
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
                                        <button type="button" :id="'hapus_' + index" @click="hapusBaris(index)" 
                                                @keydown.arrow-left.prevent="fokusKiri(index, 'harga')"
                                                @keydown.arrow-right.prevent="fokusBawah(index, 'kode')"
                                                x-show="barisTabel.length > 1" class="text-gray-400 hover:text-red-600 transition-colors focus:outline-none focus:text-red-600 focus:ring-2 focus:ring-red-500 rounded" tabindex="0">
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

        {{-- FOOTER (Tombol Simpan) --}}
        <div class="bg-gray-200 dark:bg-gray-800 p-3 border-t border-gray-300 dark:border-gray-700 flex-shrink-0 z-20">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    Shortcut: <span class="font-bold">[F2]</span> Baris Baru, <b>[END]</b> Simpan, <b>[DEL]</b> Hapus
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.stok.index') }}" class="px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white font-bold rounded shadow flex items-center gap-2">BATAL [ESC]</a>
                    <button @click="simpanPerubahan()" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xl rounded shadow-lg flex items-center gap-2">
                        SIMPAN PERUBAHAN [END]
                    </button>
                </div>
            </div>
        </div>

        {{-- MODAL PENCARIAN --}}
        <div x-show="modalPencarianOpen" class="fixed inset-0 bg-gray-600 bg-opacity-75 z-50 flex items-center justify-center" x-transition x-cloak>
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
                                    <td class="px-4 py-3 text-sm font-mono text-gray-600" x-text="produk.id_produk"></td><td class="px-4 py-3 text-sm font-bold text-gray-800 dark:text-gray-200" x-text="produk.nama_produk"></td><td class="px-4 py-3 text-center"><span class="bg-gray-200 px-2 py-1 rounded text-xs font-bold" x-text="produk.nama_satuan"></span></td><td class="px-4 py-3 text-center text-sm font-bold" :class="parseFloat(produk.stok_real.replace(',', '.')) > 0 ? 'text-green-600' : 'text-red-600'" x-text="produk.stok_real"></td><td class="px-4 py-3 text-right font-mono text-blue-600 font-bold" x-text="formatRupiah(produk.harga_pokok)"></td>
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
        function stokMasukEditForm() {
            return {
                // Data Awal
                barisTabel: window.stokEditData || [], 
                id_supplier: "{{ $batch->id_supplier }}",
                tanggal_masuk: "{{ $batch->tanggal_masuk }}",
                keterangan: "{{ $batch->keterangan ?? '' }}",
                
                // UI State
                activeRow: 0, modalPencarianOpen: false, searchResultsModal: [], searchTermModal: '', activeModalIndex: 0, barisYangAkanDiisi: null, searchQueryModal: '', isProcessing: false,

                init() {
                    if(this.barisTabel.length === 0) this.tambahBarisBaru();
                    // Reorder agar satuan yang tersimpan muncul pertama
                    this.barisTabel.forEach(item => this.ensureReorderAfterFill(item));
                },

                reorderSatuanOptions(item) {
                    if (!item || !Array.isArray(item.satuan_options)) return;
                    const selectedName = item.satuan;
                    if (!selectedName) return;
                    const idx = item.satuan_options.findIndex(o => o.nama === selectedName);
                    if (idx > 0) {
                        const [sel] = item.satuan_options.splice(idx, 1);
                        item.satuan_options.unshift(sel);
                    }
                },
                ensureReorderAfterFill(item) { if (item.satuan) this.reorderSatuanOptions(item); },

                handleGlobalKey(e) {
                    if (this.modalPencarianOpen) {
                        if (e.key === 'ArrowDown') { e.preventDefault(); this.navigasiModal('bawah'); }
                        else if (e.key === 'ArrowUp') { e.preventDefault(); this.navigasiModal('atas'); }
                        else if (e.key === 'Enter') { e.preventDefault(); this.pilihProdukViaEnter(); }
                        else if (e.key === 'Escape') { this.modalPencarianOpen = false; }
                        return;
                    }
                    if (e.key === 'End') { e.preventDefault(); this.simpanPerubahan(); }
                    if (e.key === 'F2') { e.preventDefault(); this.tambahBarisBaru(); }
                    if (e.key === 'Escape') { window.location.href = "{{ route('admin.stok.index') }}"; }
                },

                sanitizeQty(index, el) { let val = el.value.replace(/[^0-9]/g, ''); if (val === '') val = ''; this.barisTabel[index].jumlah = val; this.hitungSubtotal(index); },
                sanitizeHarga(index, el) { let val = el.value.replace(/[^0-9]/g, ''); this.barisTabel[index].harga = val; this.hitungSubtotal(index); },
                
                hitungSubtotal(index) { const baris = this.barisTabel[index]; let jumlah = parseInt(baris.jumlah) || 0; let harga = parseInt(baris.harga) || 0; baris.subtotal = jumlah * harga; },
                get totalFaktur() { return this.barisTabel.reduce((total, baris) => total + baris.subtotal, 0); },

                updateHarga(index) {
                    const baris = this.barisTabel[index];
                    const opsi = baris.satuan_options.find(o => o.nama == baris.satuan);
                    if (opsi) {
                        baris.harga = opsi.harga;
                        this.hitungSubtotal(index);
                    }
                },

                async simpanPerubahan() {
                    if (this.isProcessing) return;
                    this.isProcessing = true;

                    const items = this.barisTabel.filter(b => b.id_produk_final).map(item => ({
                        id_produk: item.id_produk_final,
                        jumlah: item.jumlah,
                        nama_satuan: item.satuan,
                        harga_beli_satuan: item.harga
                    }));

                    if (items.length === 0) { alert('Belum ada barang!'); this.isProcessing = false; return; }

                    const payload = {
                        id_supplier: this.id_supplier,
                        tanggal_masuk: this.tanggal_masuk,
                        keterangan: this.keterangan,
                        detail: items
                    };

                    try {
                        const response = await fetch("{{ route('admin.stok.update', $batch->id_batch_stok) }}", {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                            body: JSON.stringify(payload)
                        });
                        
                        if (response.ok || response.redirected) {
                            alert('Stok berhasil diupdate!');
                            window.location.href = "{{ route('admin.stok.index') }}";
                        } else {
                            const result = await response.json();
                            alert('Gagal: ' + result.message);
                            this.isProcessing = false;
                        }
                    } catch (error) { console.error(error); alert('Terjadi kesalahan.'); this.isProcessing = false; }
                },

                // ... (Fungsi navigasi & scan standar - sama seperti sebelumnya) ...
                tambahBarisBaru() { this.barisTabel.push({ id_temp: Date.now() + Math.random(), kode_item: '', id_produk_final: '', nama_produk: '---', satuan_options: [], satuan: '', jumlah: 1, harga: 0, subtotal: 0, error: '' }); this.activeRow = this.barisTabel.length - 1; this.$nextTick(() => { document.getElementById('kode_' + this.activeRow)?.focus(); }); },
                hapusBaris(index) { this.barisTabel.splice(index, 1); if(this.barisTabel.length === 0) { this.tambahBarisBaru(); } else { this.activeRow = Math.min(index, this.barisTabel.length - 1); this.$nextTick(() => document.getElementById('kode_' + this.activeRow)?.focus()); } },
                async scanProduk(index) { if (this.modalPencarianOpen) return; const baris = this.barisTabel[index]; const kode = baris.kode_item; if (kode.length < 2) return; if (baris.id_produk_final) return; try { const response = await fetch(`{{ route('admin.stok.cariProduk') }}?search=${kode}`); const data = await response.json(); if (data.length === 0) { baris.error = 'Produk tidak ditemukan!'; baris.nama_produk = '---'; } else if (data.length === 1) { this.isiBaris(index, data, data[0]); } else { this.searchResultsModal = data; this.activeModalIndex = 0; this.searchQueryModal = kode; this.barisYangAkanDiisi = index; this.modalPencarianOpen = true; this.$nextTick(() => { document.getElementById('modal-search-input').focus(); document.getElementById('modal-search-input').select(); }); } } catch (error) { console.error(error); } },
                async searchProdukInModal() { if (this.searchQueryModal.length < 2) return; try { const response = await fetch(`{{ route('admin.stok.cariProduk') }}?search=${this.searchQueryModal}`); const data = await response.json(); this.searchResultsModal = data; this.activeModalIndex = 0; } catch (error) { console.error(error); } },
                isiBaris(index, semuaOpsi, produkTerpilih) { const baris = this.barisTabel[index]; baris.id_produk_final = produkTerpilih.id_produk; baris.kode_item = produkTerpilih.id_produk; baris.nama_produk = produkTerpilih.nama_produk; baris.satuan_options = semuaOpsi.filter(p => p.id_produk === produkTerpilih.id_produk).map(p => ({ nama: p.nama_satuan, harga: p.harga_pokok })); baris.satuan = produkTerpilih.nama_satuan; baris.harga = produkTerpilih.harga_pokok; baris.error = ''; this.ensureReorderAfterFill(baris); this.hitungSubtotal(index); this.$nextTick(() => { document.getElementById('qty_' + index)?.focus(); }); if (index === this.barisTabel.length - 1) { this.tambahBarisBaru(); } },
                pilihProdukDariModal(produk) { if (this.barisYangAkanDiisi !== null) { this.isiBaris(this.barisYangAkanDiisi, this.searchResultsModal, produk); this.modalPencarianOpen = false; this.searchResultsModal = []; this.barisYangAkanDiisi = null; } },
                fokusBawah(index, colName) { if (this.modalPencarianOpen) return; const nextIndex = index + 1; if (nextIndex < this.barisTabel.length) { this.activeRow = nextIndex; document.getElementById(colName + '_' + nextIndex)?.focus(); } else { this.tambahBarisBaru(); } },
                fokusAtas(index, colName) { if (this.modalPencarianOpen) return; if (index > 0) { this.activeRow = index - 1; document.getElementById(colName + '_' + (index - 1))?.focus(); } },
                fokusKanan(index, nextCol) { if (this.modalPencarianOpen) return; document.getElementById(nextCol + '_' + index)?.focus(); },
                fokusKiri(index, prevCol) { if (this.modalPencarianOpen) return; document.getElementById(prevCol + '_' + index)?.focus(); },
                navigasiModal(arah) { if (arah === 'bawah') { if (this.activeModalIndex < this.searchResultsModal.length - 1) this.activeModalIndex++; this.scrollToModalItem(); } else if (arah === 'atas') { if (this.activeModalIndex > 0) this.activeModalIndex--; this.scrollToModalItem(); } },
                scrollToModalItem() { const el = document.getElementById('modal-row-' + this.activeModalIndex); el?.scrollIntoView({ block: 'nearest' }); },
                pilihProdukViaEnter() { if (this.searchResultsModal.length > 0) { this.pilihProdukDariModal(this.searchResultsModal[this.activeModalIndex]); } },
                formatRupiah(angka) { return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 }).format(angka || 0); }
            }
        }
    </script>
    @endpush
</x-app-layout>