<x-app-layout>
    {{-- PERUBAHAN 1: Judul Halaman --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Produk: ') }} {{ $produk->nama_produk }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-md">
                            {{ session('error') }}
                        </div>
                    @endif

                    {{-- PERUBAHAN 2: Ganti Action Route dan Tambah Method PUT --}}
                    <form action="{{ route('admin.produk.update', $produk->id_produk) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT') {{-- <-- WAJIB UNTUK EDIT/UPDATE --}}

                        {{-- =============================================== --}}
                        {{-- 1. DATA UMUM --}}
                        {{-- =============================================== --}}
                        <h3 class="text-lg font-semibold mb-4 border-b pb-2">Data Umum</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            
                            {{-- PERUBAHAN 3: Isi Value & Buat Readonly --}}
                            <div>
                                <x-input-label for="id_produk" :value="__('Kode Item / Barcode (Tidak Bisa Diubah)')" />
                                <x-text-input id="id_produk" class="block mt-1 w-full bg-gray-100 dark:bg-gray-700" type="text" name="id_produk" :value="$produk->id_produk" readonly disabled />
                            </div>
                            
                            {{-- PERUBAHAN 3: Isi Value --}}
                            <div>
                                <x-input-label for="nama_produk" :value="__('Nama Produk')" />
                                <x-text-input id="nama_produk" class="block mt-1 w-full" type="text" name="nama_produk" :value="old('nama_produk', $produk->nama_produk)" required />
                                <x-input-error :messages="$errors->get('nama_produk')" class="mt-2" />
                            </div>
                            
                            {{-- BLOK KATEGORI (Kode modal tetap sama) --}}
                            <div x-data="{ modalOpen: false }"> 
                                <x-input-label for="id_kategori_dropdown" :value="__('Kategori Produk')" />
                                <div class="flex items-center mt-1">
                                    {{-- PERUBAHAN 3: Isi Value (Selected) --}}
                                    <select name="id_kategori" id="id_kategori_dropdown" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                                        <option value="">Pilih Kategori</option>
                                        @foreach ($kategoris as $kategori)
                                            <option value="{{ $kategori->id_kategori }}" {{ old('id_kategori', $produk->id_kategori) == $kategori->id_kategori ? 'selected' : '' }}>
                                                {{ $kategori->nama_kategori }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="button" @click="modalOpen = true" class="ml-2 flex-shrink-0 ...">+</button>
                                </div>
                                <x-input-error :messages="$errors->get('id_kategori')" class="mt-2" />
                                
                                {{-- Modal Kategori (Biarkan kodenya sama persis) --}}
                                <div x-show="modalOpen" x-transition class="fixed inset-0 ... z-50" @click.away="modalOpen = false" x-cloak>
                                    <div @click.stop class="relative mx-auto p-6 ... bg-white dark:bg-gray-800">
                                        <h3 class="text-xl ... mb-4">Tambah Kategori Cepat</h3>
                                        <div id="quick-add-kategori-form">
                                            {{-- ... (Isi modal Kategori, tidak perlu diubah) ... --}}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- BLOK SUPPLIER (Kode modal tetap sama) --}}
                            <div x-data="{ modalOpen: false }"> 
                                <x-input-label for="id_supplier_dropdown" :value="__('Supplier')" />
                                <div class="flex items-center mt-1">
                                    {{-- PERUBAHAN 3: Isi Value (Selected) --}}
                                    <select name="id_supplier" id="id_supplier_dropdown" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                                        <option value="">Pilih Supplier</option>
                                        @foreach ($suppliers as $supplier)
                                            <option value="{{ $supplier->id_supplier }}" {{ old('id_supplier', $produk->id_supplier) == $supplier->id_supplier ? 'selected' : '' }}>
                                                {{ $supplier->nama_supplier }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="button" @click="modalOpen = true" class="ml-2 flex-shrink-0 ...">+</button>
                                </div>
                                <x-input-error :messages="$errors->get('id_supplier')" class="mt-2" />
                                
                                {{-- Modal Supplier (Biarkan kodenya sama persis) --}}
                                <div x-show="modalOpen" x-transition class="fixed inset-0 ... z-50" @click.away="modalOpen = false" x-cloak>
                                    <div @click.stop class="relative mx-auto p-6 ... bg-white dark:bg-gray-800">
                                        <h3 class="text-xl ... mb-4">Tambah Supplier Cepat</h3>
                                        <div id="quick-add-supplier-form">
                                            {{-- ... (Isi modal Supplier, tidak perlu diubah) ... --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="md:col-span-2">
                                <x-input-label for="deskripsi" :value="__('Keterangan (Opsional)')" />
                                {{-- PERUBAHAN 3: Isi Value --}}
                                <textarea id="deskripsi" name="deskripsi" rows="3" class="block mt-1 w-full ... rounded-md shadow-sm">{{ old('deskripsi', $produk->deskripsi) }}</textarea>
                            </div>
                            <div>
                                <x-input-label for="gambar" :value="__('Ganti Gambar Produk (Opsional)')" />
                                <input id="gambar" class="block mt-1 w-full ... cursor-pointer" type="file" name="gambar">
                                {{-- Tampilkan gambar lama jika ada --}}
                                @if($produk->gambar)
                                    <img src="{{ asset('storage/'. $produk->gambar) }}" alt="{{ $produk->nama_produk }}" class="mt-2 h-20 w-20 object-cover rounded-md">
                                @endif
                            </div>
                        </div>

                        {{-- =============================================== --}}
                        {{-- 2. SATUAN & HARGA POKOK --}}
                        {{-- =============================================== --}}
                        {{-- PERUBAHAN 4: Isi x-data dengan konversi yang ada --}}
                        <div x-data="{ konversis: {{ $produk->produkKonversis->toJson() }} }">
                            <h3 class="text-lg font-semibold mb-4 border-b pb-2">Satuan & Harga Pokok (Modal)</h3>
                            
                            {{-- Bagian Satuan Dasar --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4 p-4 border rounded-md bg-gray-50 dark:bg-gray-700">
                                <div>
                                    <x-input-label for="id_satuan_dasar" :value="__('Satuan Dasar (Wajib)')" />
                                    <select name="id_satuan_dasar" id="id_satuan_dasar" class="block mt-1 w-full ... rounded-md shadow-sm" required>
                                        <option value="">Pilih Satuan Terkecil (misal: PCS)</option>
                                        @foreach ($satuans as $satuan)
                                            {{-- PERUBAHAN 3: Isi Value (Selected) --}}
                                            <option value="{{ $satuan->id_satuan }}" {{ old('id_satuan_dasar', $produk->id_satuan_dasar) == $satuan->id_satuan ? 'selected' : '' }}>
                                                {{ $satuan->nama_satuan }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('id_satuan_dasar')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="harga_pokok_dasar" :value="__('Harga Pokok Dasar (Modal)')" />
                                    {{-- PERUBAHAN 3: Isi Value --}}
                                    <x-text-input id="harga_pokok_dasar" class="block mt-1 w-full" type="number" step="0.01" name="harga_pokok_dasar" :value="old('harga_pokok_dasar', $produk->harga_pokok_dasar)" required />
                                    <x-input-error :messages="$errors->get('harga_pokok_dasar')" class="mt-2" />
                                </div>
                            </div>
    
                            {{-- Bagian Daftar Konversi (Dinamis dengan Alpine.js) --}}
                            <h4 class="text-md font-semibold mb-2">Daftar Konversi (Opsional)</h4>
                            <template x-for="(konversi, index) in konversis" :key="index">
                                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-2 p-4 border rounded-md relative">
                                    <div>
                                        <x-input-label :value="__('Satuan Konversi')" />
                                        {{-- PERUBAHAN 5: Tambahkan x-model untuk mengisi data Alpine --}}
                                        <select x-bind:name="'konversi[' + index + '][id_satuan_konversi]'" class="block mt-1 w-full ... rounded-md shadow-sm" required x-model="konversi.id_satuan_konversi">
                                            <option value="">Pilih Satuan</option>
                                            @foreach ($satuans as $satuan)
                                                <option value="{{ $satuan->id_satuan }}">{{ $satuan->nama_satuan }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <x-input-label :value="__('Nilai Konversi (x Satuan Dasar)')" />
                                        <x-text-input type="number" x-bind:name="'konversi[' + index + '][nilai_konversi]'" class="block mt-1 w-full" required x-model="konversi.nilai_konversi" />
                                    </div>
                                    <div>
                                        <x-input-label :value="__('Harga Pokok Konversi (Modal)')" />
                                        <x-text-input type="number" step="0.01" x-bind:name="'konversi[' + index + '][harga_pokok_konversi]'" class="block mt-1 w-full" required x-model="konversi.harga_pokok_konversi" />
                                    </div>
                                    <div>
                                        <x-input-label :value="__('Harga Jual Konversi')" />
                                        <x-text-input type="number" step="0.01" x-bind:name="'konversi[' + index + '][harga_jual_konversi]'" class="block mt-1 w-full" required x-model="konversi.harga_jual_konversi" />
                                    </div>
                                    <div class="flex items-end">
                                        <button type="button" @click="konversis.splice(index, 1)" class="px-3 py-2 bg-red-600 text-white rounded-md">Hapus</button>
                                    </div>
                                </div>
                            </template>
                            <button type="button" @click="konversis.push({})" class="mt-2 px-4 py-2 bg-green-500 text-white rounded-md">
                                + Tambah Konversi (misal: DUS)
                            </button>
                        </div>
                        
                        {{-- =============================================== --}}
                        {{-- 3. HARGA JUAL --}}
                        {{-- =============================================== --}}
                        <h3 class="text-lg font-semibold mb-4 mt-6 border-b pb-2">Harga Jual</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6 p-4 border rounded-md bg-gray-50 dark:bg-gray-700">
                            <div>
                                <x-input-label for="harga_jual_dasar" :value="__('Harga Jual Satuan Dasar (misal: PCS)')" />
                                {{-- PERUBAHAN 3: Isi Value --}}
                                <x-text-input id="harga_jual_dasar" class="block mt-1 w-full" type="number" step="0.01" name="harga_jual_dasar" :value="old('harga_jual_dasar', $produk->harga_jual_dasar)" required />
                                <x-input-error :messages="$errors->get('harga_jual_dasar')" class="mt-2" />
                            </div>
                        </div>
    
                        {{-- Tombol Simpan & Batal FORM UTAMA --}}
                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.produk.index') }}" class="text-gray-600 dark:text-gray-400 hover:underline mr-4">Batal</a>
                            {{-- PERUBAHAN 6: Ganti Teks Tombol --}}
                            <x-primary-button>
                                {{ __('Update Produk') }}
                            </x-primary-button>
                        </div>
                    </form>
    
                </div>
            </div>
        </div>
    </div>

    {{-- Script AJAX untuk Quick Add (TETAP SAMA, TIDAK DIUBAH) --}}
    @push('scripts')
        {{-- Script untuk Kategori Quick Add --}}
        <script>
            // ... (Kode script Kategori Anda yang sudah benar) ...
        </script>
        
        {{-- Script untuk Supplier Quick Add --}}
        <script>
            // ... (Kode script Supplier Anda yang sudah benar) ...
        </script>
    @endpush
</x-app-layout>