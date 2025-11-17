<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Stok Masuk') }}
        </h2>
    </x-slot>

    {{-- LOGIKA PHP: MENYIAPKAN DATA --}}
    @php
        $initialData = [];
        foreach ($batch->details as $index => $detail) {
            $satuan_options = [];
            
            // 1. Masukkan Opsi Satuan Dasar
            $satuan_options[] = [
                'id_satuan' => $detail->produk->id_satuan_dasar,
                'nama_satuan' => $detail->produk->satuanDasar->nama_satuan,
                'harga_pokok' => $detail->produk->harga_pokok_dasar,
                'id_produk' => $detail->id_produk,
                'nama_produk' => $detail->produk->nama_produk
            ];
            
            // 2. Masukkan Opsi Satuan Konversi
            foreach ($detail->produk->produkKonversis as $konv) {
                $satuan_options[] = [
                    'id_satuan' => $konv->id_satuan_konversi,
                    'nama_satuan' => $konv->satuan->nama_satuan,
                    'harga_pokok' => $konv->harga_pokok_konversi,
                    'id_produk' => $detail->id_produk,
                    'nama_produk' => $detail->produk->nama_produk
                ];
            }
            
            // --- PERBAIKAN LOGIKA PENCOCOKAN SATUAN ---
            $currentSatuanId = null;
            $satuanDb = strtoupper(trim($detail->satuan)); // Ambil dari DB, bersihkan

            // Cek satu per satu opsi
            if (!empty($satuan_options)) {
                // Default ke yang pertama (jika tidak ketemu)
                $currentSatuanId = $satuan_options[0]['id_satuan']; 

                foreach ($satuan_options as $opt) {
                    $satuanOpt = strtoupper(trim($opt['nama_satuan']));
                    // Jika nama di DB sama dengan nama di Opsi
                    if ($satuanDb === $satuanOpt) {
                        $currentSatuanId = $opt['id_satuan'];
                        break; // Ketemu! Stop looping.
                    }
                }
            }
            // ------------------------------------------

            $initialData[] = [
                'id' => $index,
                'id_produk_input' => $detail->id_produk,
                'id_produk_final' => $detail->id_produk,
                'nama_produk' => $detail->produk->nama_produk,
                'satuan_options' => $satuan_options,
                'id_satuan' => $currentSatuanId, // ID yang sudah dicocokkan
                'nama_satuan_final' => $detail->satuan,
                'jumlah' => $detail->jumlah,
                'harga_beli_satuan' => $detail->harga_beli_satuan,
                'subtotal' => $detail->jumlah * $detail->harga_beli_satuan,
                'error' => ''
            ];
        }
    @endphp

    <script>
        window.stokEditData = {!! json_encode($initialData) !!};
    </script>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                
                <div class="p-6 text-gray-900 dark:text-gray-100" x-data="stokMasukEditForm()">
                    
                    {{-- Tab Navigasi --}}
                    <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
                        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                            <li class="me-2">
                                <a href="{{ route('admin.stok.index') }}" class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300">
                                    Riwayat Stok Masuk
                                </a>
                            </li>
                            <li class="me-2">
                                <a href="{{ route('admin.stok.create') }}" class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300">
                                    Input Stok Masuk
                                </a>
                            </li>
                            <li class="me-2">
                                <a href="#" class="inline-block p-4 text-blue-600 border-b-2 border-blue-600 rounded-t-lg active dark:text-blue-500 dark:border-blue-500">
                                    Edit Transaksi (STOK-{{ str_pad($batch->id_batch_stok, 4, '0', STR_PAD_LEFT) }})
                                </a>
                            </li>
                        </ul>
                    </div>

                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-md">{{ session('error') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
                            <ul class="mt-2 list-disc list-inside">
                                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.stok.update', $batch->id_batch_stok) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- INFO FAKTUR --}}
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                            <div>
                                <x-input-label for="id_transaksi" :value="__('ID Transaksi')" />
                                <x-text-input id="id_transaksi" class="block mt-1 w-full bg-gray-100" type="text" value="STOK-{{ str_pad($batch->id_batch_stok, 4, '0', STR_PAD_LEFT) }}" disabled />
                            </div>
                            <div>
                                <x-input-label for="id_supplier" :value="__('Supplier (Opsional)')" />
                                <select name="id_supplier" id="id_supplier_dropdown" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm">
                                    <option value="">Pilih Supplier</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id_supplier }}" {{ old('id_supplier', $batch->id_supplier) == $supplier->id_supplier ? 'selected' : '' }}>
                                            {{ $supplier->nama_supplier }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="tanggal_masuk" :value="__('Tanggal Masuk')" />
                                <x-text-input id="tanggal_masuk" class="block mt-1 w-full" type="date" name="tanggal_masuk" :value="old('tanggal_masuk', $batch->tanggal_masuk)" required />
                            </div>
                            <div>
                                <x-input-label for="keterangan" :value="__('Keterangan (Opsional)')" />
                                <x-text-input id="keterangan" class="block mt-1 w-full" type="text" name="keterangan" :value="old('keterangan', $batch->keterangan)" />
                            </div>
                        </div>

                        {{-- TABEL TRANSAKSI --}}
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left w-16">No.</th>
                                        <th class="px-6 py-3 text-left w-48">Kode Item (Scan/Ketik)</th>
                                        <th class="px-6 py-3 text-left">Nama Barang</th>
                                        <th class="px-6 py-3 text-left w-40">Satuan</th>
                                        <th class="px-6 py-3 text-left w-24">Jumlah</th>
                                        <th class="px-6 py-3 text-left w-40">Harga Beli Satuan</th>
                                        <th class="px-6 py-3 text-left w-40">Subtotal</th>
                                        <th class="px-6 py-3 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <template x-for="(baris, index) in barisTabel" :key="index">
                                        <tr>
                                            <td class="px-6 py-4" x-text="index + 1"></td>
                                            
                                            <td class="px-6 py-4">
                                                <input type="text" x-model="baris.id_produk_input" 
                                                       @input="baris.id_produk_final = ''"
                                                       @keydown.enter.prevent="cariProdukDanIsi(index)" 
                                                       @blur="cariProdukDanIsi(index)"
                                                       class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" 
                                                       placeholder="Scan...">
                                                <input type="hidden" x-bind:name="'detail[' + index + '][id_produk]'" x-model="baris.id_produk_final">
                                                <p x-show="baris.error" x-text="baris.error" class="text-sm text-red-500 mt-1"></p>
                                            </td>

                                            <td class="px-6 py-4"><span x-text="baris.nama_produk"></span></td>
                                            
                                            <td class="px-6 py-4">
                                                <select x-model="baris.id_satuan" @change="updateHargaDanNamaSatuan(index)"
                                                        class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm">
                                                    <template x-for="satuan in baris.satuan_options" :key="satuan.id_satuan">
                                                        <option :value="satuan.id_satuan" x-text="satuan.nama_satuan"></option>
                                                    </template>
                                                </select>
                                                <input type="hidden" x-bind:name="'detail[' + index + '][nama_satuan]'" :value="baris.nama_satuan_final">
                                            </td>

                                            <td class="px-6 py-4">
                                                <input type="number" x-bind:name="'detail[' + index + '][jumlah]'" x-model.number="baris.jumlah" @input="hitungSubtotal(index)" 
                                                       class="w-24 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm">
                                            </td>
                                            
                                            <td class="px-6 py-4">
                                                <input type="number" step="0.01" x-bind:name="'detail[' + index + '][harga_beli_satuan]'" x-model.number="baris.harga_beli_satuan" @input="hitungSubtotal(index)" 
                                                       class="w-40 border-gray-100 bg-gray-100 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm" readonly>
                                            </td>
                                            
                                            <td class="px-6 py-4"><span x-text="formatRupiah(baris.subtotal)"></span></td>
                                            
                                            <td class="px-6 py-4 text-right">
                                                <button type="button" @click="hapusBaris(index)" class="text-red-500 hover:text-red-700">Hapus</button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                                <tfoot class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <td colspan="6" class="px-6 py-3 text-right font-bold">Total Faktur:</td>
                                        <td colspan="2" class="px-6 py-3 font-bold"><span x-text="formatRupiah(totalFaktur)"></span></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        {{-- HAPUS Tombol "+ Tambah Baris" agar konsisten dengan auto-add --}}

                        <div class="flex items-center justify-end mt-8 space-x-3">
                            <a href="{{ route('admin.stok.index') }}" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-md font-semibold text-xs uppercase tracking-widest transition">Batal</a>
                            <button type="submit" name="action" value="cetak" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-md font-semibold text-xs uppercase tracking-widest transition">Simpan & Cetak Struk</button>
                            <button type="submit" name="action" value="simpan" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-md font-semibold text-xs uppercase tracking-widest transition">Simpan Perubahan</button>
                        </div>
                    </form>

                    {{-- MODAL PENCARIAN --}}
                    <div x-show="modalPencarianOpen" x-transition class="fixed inset-0 bg-gray-600 bg-opacity-75 z-50 flex items-center justify-center" @click.away="modalPencarianOpen = false" x-cloak>
                        <div @click.stop class="relative mx-auto p-6 border w-full max-w-2xl shadow-lg rounded-md bg-white dark:bg-gray-800">
                            <h3 class="text-xl font-medium mb-4">Daftar Item Ditemukan</h3>
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700"><tr><th>Kode</th><th>Nama</th><th>Satuan</th><th>Harga</th><th>Aksi</th></tr></thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <template x-for="produk in searchResultsModal" :key="produk.unique_id">
                                        <tr class="hover:bg-gray-100 dark:hover:bg-gray-600">
                                            <td class="px-6 py-4" x-text="produk.id_produk"></td>
                                            <td class="px-6 py-4" x-text="produk.nama_produk"></td>
                                            <td class="px-6 py-4 font-bold" x-text="produk.nama_satuan"></td>
                                            <td class="px-6 py-4" x-text="formatRupiah(produk.harga_pokok)"></td>
                                            <td class="px-6 py-4"><button type="button" @click="pilihProdukDariModal(produk)" class="px-3 py-1 bg-blue-600 text-white rounded-md text-sm">Pilih</button></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                            <div class="mt-4 flex justify-end"><button type="button" @click="modalPencarianOpen = false" class="px-4 py-2 text-sm bg-gray-200 rounded-md">Tutup</button></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function stokMasukEditForm() {
            return {
                barisTabel: window.stokEditData || [], 
                modalPencarianOpen: false, searchResultsModal: [], searchTermModal: '', barisYangAkanDiisi: null, 
                
                init() {
                    // Selalu tambahkan 1 baris kosong di akhir untuk scan baru
                    this.tambahBarisBaru(); 
                },
                
                formatRupiah(angka) { if (isNaN(angka)) { angka = 0; } return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka); },
                tambahBarisBaru() { this.barisTabel.push({ id: Date.now() + Math.random(), id_produk_input: '', id_produk_final: '', nama_produk: '---', satuan_options: [], id_satuan: '', nama_satuan_final: '', jumlah: 1, harga_beli_satuan: 0, subtotal: 0, error: '' }); },
                hapusBaris(index) { this.barisTabel.splice(index, 1); if(this.barisTabel.length === 0) { this.tambahBarisBaru(); } },
                async cariProdukDanIsi(index) {
                    if (this.modalPencarianOpen) return;
                    const baris = this.barisTabel[index];
                    if (baris.id_produk_final && baris.id_produk_final !== '') return;

                    const searchTerm = baris.id_produk_input;
                    if (searchTerm.length < 2) return; 
                    baris.error = ''; baris.nama_produk = 'Mencari...';
                    try {
                        const response = await fetch(`{{ route('admin.stok.cariProduk') }}?search=${searchTerm}`);
                        const data = await response.json();
                        if (data.length === 0) { baris.error = "Produk tidak ditemukan"; baris.nama_produk = '---'; baris.id_produk_final = ''; } 
                        else if (data.length === 1) { this.isiBarisTabel(index, data, data[0]); } 
                        else { this.searchResultsModal = data; this.searchTermModal = searchTerm; this.barisYangAkanDiisi = index; this.modalPencarianOpen = true; }
                    } catch (error) { console.error('Gagal mencari produk:', error); baris.nama_produk = "Error koneksi..."; }
                },
                isiBarisTabel(index, semuaOpsiSatuan, satuanTerpilih) {
                    let baris = this.barisTabel[index];
                    baris.id_produk_input = satuanTerpilih.id_produk; baris.id_produk_final = satuanTerpilih.id_produk; baris.nama_produk = satuanTerpilih.nama_produk;
                    baris.satuan_options = semuaOpsiSatuan.filter(p => p.id_produk === satuanTerpilih.id_produk);
                    baris.id_satuan = satuanTerpilih.id_satuan; baris.nama_satuan_final = satuanTerpilih.nama_satuan; baris.harga_beli_satuan = satuanTerpilih.harga_pokok;
                    
                    // Jumlah otomatis 1
                    baris.jumlah = 1;

                    this.hitungSubtotal(index);
                    // Auto-add baris baru
                    if (index === this.barisTabel.length - 1) { this.tambahBarisBaru(); }
                },
                pilihProdukDariModal(produk) { const index = this.barisYangAkanDiisi; this.isiBarisTabel(index, this.searchResultsModal, produk); this.modalPencarianOpen = false; this.searchResultsModal = []; this.barisYangAkanDiisi = null; },
                updateHargaDanNamaSatuan(index) {
                     const baris = this.barisTabel[index];
                    const selectedSatuan = baris.satuan_options.find(s => s.id_satuan == baris.id_satuan);
                    if (selectedSatuan) { 
                        baris.harga_beli_satuan = selectedSatuan.harga_pokok; 
                        baris.nama_satuan_final = selectedSatuan.nama_satuan; 
                        this.hitungSubtotal(index); 
                    }
                },
                hitungSubtotal(index) { const baris = this.barisTabel[index]; baris.subtotal = baris.jumlah * baris.harga_beli_satuan; },
                get totalFaktur() { return this.barisTabel.reduce((total, baris) => total + baris.subtotal, 0); }
            }
        }
    </script>
    @endpush
</x-app-layout>