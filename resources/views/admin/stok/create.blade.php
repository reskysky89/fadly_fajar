{{-- File: resources/views/admin/stok/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manajemen Stok') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                {{-- 
                  Kita bungkus semua dengan Alpine.js
                  x-data="stokMasukForm()" memanggil 'otak' JavaScript di bawah
                --}}
                <div class="p-6 text-gray-900 dark:text-gray-100" x-data="stokMasukForm()">
                    
                    {{-- Tab Navigasi --}}
                    <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
                        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                            <li class="me-2">
                                <a href="{{ route('admin.stok.index') }}" class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300">
                                    Riwayat Stok Masuk
                                </a>
                            </li>
                            <li class="me-2">
                                <a href="{{ route('admin.stok.create') }}" class="inline-block p-4 text-blue-600 border-b-2 border-blue-600 rounded-t-lg active dark:text-blue-500 dark:border-blue-500">
                                    Input Stok Masuk
                                </a>
                            </li>
                        </ul>
                    </div>

                    {{-- Tampilkan pesan sukses/error --}}
                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-md">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-md">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('admin.stok.store') }}" method="POST">
                        @csrf
                        @if ($errors->any())
                            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
                                <strong class="font-bold">Oops! Ada yang salah:</strong>
                                <ul class="mt-2 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        {{-- BAGIAN ATAS: INFO FAKTUR (Sesuai Desain) --}}
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                            <div>
                                <x-input-label for="id_transaksi" :value="__('ID Transaksi')" />
                                <x-text-input id="id_transaksi" class="block mt-1 w-full bg-gray-100" type="text" value="[OTOMATIS]" disabled />
                            </div>
                            <div>
                                <x-input-label for="id_supplier" :value="__('Supplier (Opsional)')" />
                                <select name="id_supplier" id="id_supplier_dropdown" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm">
                                    <option value="">Pilih Supplier</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id_supplier }}">{{ $supplier->nama_supplier }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="tanggal_masuk" :value="__('Tanggal Masuk')" />
                                <x-text-input id="tanggal_masuk" class="block mt-1 w-full" type="date" name="tanggal_masuk" :value="date('Y-m-d')" required />
                            </div>
                            <div>
                                <x-input-label for="keterangan" :value="__('Keterangan (Opsional)')" />
                                <x-text-input id="keterangan" class="block mt-1 w-full" type="text" name="keterangan" :value="old('keterangan')" />
                            </div>
                        </div>

                        {{-- =============================================== --}}
                        {{-- BAGIAN TABEL TRANSAKSI (Form Transaksional) --}}
                        {{-- =============================================== --}}
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
                                    {{-- Baris tabel dinamis dari Alpine.js --}}
                                    <template x-for="(baris, index) in barisTabel" :key="index">
                                        <tr>
                                            <td class="px-6 py-4" x-text="index + 1"></td>
                                            
                                            {{-- Kolom Kode Item (Bisa Scan/Ketik) --}}
                                            <td class="px-6 py-4">
                                                <x-text-input type="text" x-model="baris.id_produk_input" 
                                                       {{-- Saat menekan Enter, panggil 'cariProdukDanIsi' --}}
                                                       @keydown.enter.prevent="cariProdukDanIsi(index)" 
                                                       class="w-full" 
                                                       placeholder="Scan atau Ketik...">
                                                </x-text-input>
                                                {{-- Ini input tersembunyi untuk menyimpan ID Produk final --}}
                                                <input type="hidden" x-bind:name="'detail[' + index + '][id_produk]'" x-model="baris.id_produk_final">
                                                <p x-show="baris.error" x-text="baris.error" class="text-sm text-red-500 mt-1"></p>
                                            </td>

                                            {{-- Kolom Nama Barang (Otomatis) --}}
                                            <td class="px-6 py-4">
                                                <span x-text="baris.nama_produk"></span>
                                            </td>
                                            
                                            {{-- Kolom Satuan (Dropdown) --}}
                                            <td class="px-6 py-4">
                                                <select x-model="baris.id_satuan" @change="updateHargaDanNamaSatuan(index)"
                                                        x-bind:name="'detail[' + index + '][id_satuan_pilihan_id]'" {{-- Nama sementara --}}
                                                        class="w-full border-gray-300 ... rounded-md shadow-sm">
                                                    <option value="">Pilih...</option>
                                                    <template x-for="satuan in baris.satuan_options" :key="satuan.id_satuan">
                                                        <option :value="satuan.id_satuan" x-text="satuan.nama_satuan"></option>
                                                    </template>
                                                </select>
                                                {{-- Ini input tersembunyi untuk menyimpan NAMA satuan --}}
                                                <input type="hidden" x-bind:name="'detail[' + index + '][nama_satuan]'" x-model="baris.nama_satuan_final">
                                            </td>

                                            {{-- Kolom Jumlah --}}
                                            <td class="px-6 py-4">
                                                <input type="number" x-bind:name="'detail[' + index + '][jumlah]'" x-model.number="baris.jumlah" @input="hitungSubtotal(index)" 
                                                       class="w-24 border-gray-300 ... rounded-md shadow-sm">
                                            </td>
                                            
                                            {{-- Kolom Harga Beli Satuan --}}
                                            <td class="px-6 py-4">
                                                <input type="number" step="0.01" x-bind:name="'detail[' + index + '][harga_beli_satuan]'" x-model.number="baris.harga_beli_satuan" @input="hitungSubtotal(index)" 
                                                       class="w-40 border-gray-300 ... rounded-md shadow-sm">
                                            </td>
                                            
                                            {{-- Kolom Subtotal (Otomatis) --}}
                                            <td class="px-6 py-4"><span x-text="formatRupiah(baris.subtotal)"></span></td>
                                            
                                            {{-- Kolom Aksi --}}
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

                        {{-- Tombol Simpan Transaksi --}}
                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button>
                                {{ __('Simpan Transaksi Stok Masuk') }}
                            </x-primary-button>
                        </div>
                    </form>

                    {{-- =============================================== --}}
                    {{-- MODAL PENCARIAN PRODUK (Sesuai Desain) --}}
                    {{-- =============================================== --}}
                    <div x-show="modalPencarianOpen" x-transition class="fixed inset-0 bg-gray-600 bg-opacity-75 z-50 flex items-center justify-center" @click.away="modalPencarianOpen = false" x-cloak>
                        <div @click.stop class="relative mx-auto p-6 border w-full max-w-2xl shadow-lg rounded-md bg-white dark:bg-gray-800">
                            <h3 class="text-xl font-medium mb-4">Daftar Item Ditemukan</h3>
                            <p class="mb-2">Menampilkan hasil untuk "<span x-text="searchTermModal"></span>":</p>
                            
                            {{-- Tabel Hasil di Modal --}}
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th>Kode Item</th>
                                        <th>Nama Barang</th>
                                        <th>Satuan</th>
                                        <th>Harga Pokok</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <template x-for="produk in searchResultsModal" :key="produk.unique_id">
                                        <tr class="hover:bg-gray-100 dark:hover:bg-gray-600">
                                            <td class="px-6 py-4" x-text="produk.id_produk"></td>
                                            <td class="px-6 py-4" x-text="produk.nama_produk"></td>
                                            <td class="px-6 py-4 font-bold" x-text="produk.nama_satuan"></td>
                                            <td class="px-6 py-4" x-text="formatRupiah(produk.harga_pokok)"></td>
                                            <td class="px-6 py-4">
                                                <button type="button" @click="pilihProdukDariModal(produk)" class="px-3 py-1 bg-blue-600 text-white rounded-md text-sm">Pilih</button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                            
                            <div class="mt-4 flex justify-end">
                                <button type="button" @click="modalPencarianOpen = false" class="px-4 py-2 text-sm ... bg-gray-200 rounded-md">Tutup</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPT ALPINE.JS UNTUK MENGATUR SEMUA LOGIKA --}}
    @push('scripts')
    <script>
        function stokMasukForm() {
            return {
                // --- DATA ---
                barisTabel: [], // Ini adalah daftar baris di tabel utama
                modalPencarianOpen: false, // Status modal pencarian
                searchResultsModal: [], // Data untuk ditampilkan di modal
                searchTermModal: '', // Teks pencarian yang menghasilkan modal
                barisYangAkanDiisi: null, // Index baris tabel yang akan diisi (misal: 0, 1, 2)
                
                // --- INISIALISASI ---
                init() {
                    this.tambahBarisBaru(); // Mulai dengan satu baris kosong
                },
                
                // --- FUNGSI FORMAT ---
                formatRupiah(angka) {
                    if (isNaN(angka)) { angka = 0; }
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
                },
                
                // --- FUNGSI TABEL UTAMA ---
                tambahBarisBaru() {
                    this.barisTabel.push({
                        id: this.barisTabel.length,
                        id_produk_input: '', // Teks yang diketik/scan user
                        id_produk_final: '', // ID Produk yang valid (untuk dikirim ke form)
                        nama_produk: '---',
                        satuan_options: [], // Opsi dropdown satuan
                        id_satuan: '', // Satuan yang dipilih
                        nama_satuan_final: '', // Nama satuan yang dipilih (untuk dikirim ke form)
                        jumlah: 1,
                        harga_beli_satuan: 0,
                        subtotal: 0,
                        error: '' // Pesan error jika produk tidak ditemukan
                    });
                },
                hapusBaris(index) {
                    this.barisTabel.splice(index, 1);
                    if(this.barisTabel.length === 0) {
                        this.tambahBarisBaru();
                    }
                },
                hitungSubtotal(index) {
                    const baris = this.barisTabel[index];
                    baris.subtotal = baris.jumlah * baris.harga_beli_satuan;
                },
                
                // --- FUNGSI PENCARIAN (AJAX) ---
                async cariProdukDanIsi(index) {
                    const baris = this.barisTabel[index];
                    const searchTerm = baris.id_produk_input;
                    
                    if (searchTerm.length < 2) return;
                     

                    baris.error = ''; // Hapus error lama
                    baris.nama_produk = 'Mencari...';

                    try {
                        const response = await fetch(`{{ route('admin.stok.cariProduk') }}?search=${searchTerm}`);
                        const data = await response.json();
                        
                        if (data.length === 0) {
                            // SKENARIO GAGAL: Tidak ada hasil
                            baris.error = "Produk tidak ditemukan";
                            baris.nama_produk = '---';
                            baris.id_produk_final = '';
                        } else if (data.length === 1) {
                            // SKENARIO A: Hanya 1 hasil (Scan Barcode Berhasil)
                            // Langsung isi baris, tidak perlu modal
                            this.isiBarisTabel(index, data[0]);
                            // Jika ini baris terakhir, tambahkan baris baru
                            if (index === this.barisTabel.length - 1) {
                                this.tambahBarisBaru();
                            }
                        } else {
                            // SKENARIO B: Lebih dari 1 hasil (Ketik "indomie")
                            // Buka Modal Pencarian
                            this.searchResultsModal = data;
                            this.searchTermModal = searchTerm;
                            this.barisYangAkanDiisi = index; // Simpan index baris yang akan kita isi
                            this.modalPencarianOpen = true;
                        }

                    } catch (error) {
                        console.error('Gagal mencari produk:', error);
                        baris.nama_produk = "Error koneksi...";
                        baris.error = "Gagal terhubung ke server.";
                    }
                },

                // --- FUNGSI UNTUK MENGISI DATA BARIS ---
                isiBarisTabel(index, produk) {
                    let baris = this.barisTabel[index];
                    
                    baris.id_produk_input = produk.id_produk; // Isi ulang input scan
                    baris.id_produk_final = produk.id_produk;
                    baris.nama_produk = produk.nama_produk;
                    
                    // Kita perlu mengambil SEMUA opsi satuan untuk produk ini
                    // Mari kita asumsikan 'cariProdukDanIsi' sudah mengembalikan semua opsi
                    // ATAU kita panggil AJAX lagi (tapi controller kita sudah canggih)
                    
                    // Kita harus memfilter hasil pencarian untuk produk ini saja
                    let semuaOpsiSatuan = this.searchResultsModal.length > 0 ? this.searchResultsModal : [produk];
                    
                    // Jika kita auto-fill (hanya 1 hasil), kita perlu ambil semua konversinya
                    // Ini bagian yang rumit, kita sederhanakan:
                    // Asumsikan 'cariProduk' di controller sudah mengembalikan SEMUA satuan
                    
                    // --- Logika Baru: Ambil semua data dari controller ---
                    // Controller kita (cariProduk) sudah mengembalikan SEMUA satuan
                    // 'data' di 'cariProdukDanIsi' sudah berisi SEMUA satuan
                    
                    // Ini akan diisi oleh 'pilihProdukDariModal' atau 'cariProdukDanIsi (skenario A)'
                    baris.satuan_options = this.searchResultsModal.length > 0 ? this.searchResultsModal : [produk];
                    baris.id_satuan = produk.id_satuan;
                    baris.nama_satuan_final = produk.nama_satuan;
                    baris.harga_beli_satuan = produk.harga_pokok;
                    this.hitungSubtotal(index);
                },

                // Fungsi dipanggil saat memilih dari MODAL
                pilihProdukDariModal(produk) {
                    const index = this.barisYangAkanDiisi; // Ambil index baris yang kita simpan
                    this.isiBarisTabel(index, produk); // Isi baris tersebut
                    
                    // Tutup modal
                    this.modalPencarianOpen = false;
                    this.searchResultsModal = [];
                    this.barisYangAkanDiisi = null;

                    // Jika ini baris terakhir, tambahkan baris baru
                    if (index === this.barisTabel.length - 1) {
                        this.tambahBarisBaru();
                    }
                },
                
                // Fungsi dipanggil saat dropdown satuan diubah
                updateHargaDanNamaSatuan(index) {
                    const baris = this.barisTabel[index];
                    const selectedSatuan = baris.satuan_options.find(s => s.id_satuan == baris.id_satuan);
                    if (selectedSatuan) {
                        baris.harga_beli_satuan = selectedSatuan.harga_pokok;
                        baris.nama_satuan_final = selectedSatuan.nama_satuan; // Simpan nama satuan
                        this.hitungSubtotal(index);
                    }
                },

                // Komputasi total faktur
                get totalFaktur() {
                    return this.barisTabel.reduce((total, baris) => total + baris.subtotal, 0);
                }
            }
        }
    </script>
    @endpush
</x-app-layout>