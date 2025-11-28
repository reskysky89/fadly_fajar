<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tambah Produk Baru') }}
        </h2>
    </x-slot>

    {{-- LOGIKA PHP: Siapkan data awal (agar tidak hilang saat error validasi) --}}
    @php
        // Cek apakah ada data lama (dari error validasi)
        $initialKonversi = old('konversi');
        
        // Jika tidak ada data lama, set array kosong
        if (!$initialKonversi) {
            $initialKonversi = [];
        }
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    {{-- Tampilkan Error Umum --}}
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
                            <strong class="font-bold">Gagal Menyimpan!</strong>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.produk.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        {{-- =============================================== --}}
                        {{-- 1. DATA UMUM --}}
                        {{-- =============================================== --}}
                        <h3 class="text-lg font-semibold mb-4 border-b pb-2">Data Umum</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            {{-- Kode Item --}}
                            <div>
                                <x-input-label for="id_produk" :value="__('Kode Item / Barcode (ID Produk)')" />
                                <x-text-input id="id_produk" class="block mt-1 w-full" type="text" name="id_produk" :value="old('id_produk')" required autofocus />
                                <x-input-error :messages="$errors->get('id_produk')" class="mt-2" />
                            </div>
                            
                            {{-- Nama Produk --}}
                            <div>
                                <x-input-label for="nama_produk" :value="__('Nama Produk')" />
                                <x-text-input id="nama_produk" class="block mt-1 w-full" type="text" name="nama_produk" :value="old('nama_produk')" required />
                                <x-input-error :messages="$errors->get('nama_produk')" class="mt-2" />
                            </div>
                            
                            {{-- Kategori --}}
                            <div x-data="{ modalOpen: false }"> 
                                <x-input-label for="id_kategori_dropdown" :value="__('Kategori Produk')" />
                                <div class="flex items-center mt-1">
                                    <select name="id_kategori" id="id_kategori_dropdown" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                                        <option value="">Pilih Kategori</option>
                                        @foreach ($kategoris as $kategori)
                                            <option value="{{ $kategori->id_kategori }}" {{ old('id_kategori') == $kategori->id_kategori ? 'selected' : '' }}>{{ $kategori->nama_kategori }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" @click="modalOpen = true" class="ml-2 flex-shrink-0 px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-500 focus:outline-none">+</button>
                                </div>
                                <x-input-error :messages="$errors->get('id_kategori')" class="mt-2" />
                                {{-- Modal Kategori --}}
                                <div x-show="modalOpen" class="fixed inset-0 bg-gray-600 bg-opacity-75 z-50 flex items-center justify-center" x-cloak>
                                    <div class="relative mx-auto p-6 border w-full max-w-md shadow-lg rounded-md bg-white dark:bg-gray-800">
                                        <h3 class="text-xl font-medium mb-4">Tambah Kategori Cepat</h3>
                                        <div id="quick-add-kategori-form">
                                            <div class="mt-2">
                                                <x-input-label for="modal_nama_kategori" :value="__('Nama Kategori Baru')" />
                                                <x-text-input id="modal_nama_kategori" class="block mt-1 w-full" type="text" name="nama_kategori" />
                                                <p id="modal-error-kategori" class="text-sm text-red-600 mt-1"></p>
                                            </div>
                                            <div class="mt-4 flex justify-end space-x-2">
                                                <button type="button" id="batal-kategori-cepat" @click="modalOpen = false" class="px-4 py-2 text-sm bg-gray-200 rounded-md">Batal</button>
                                                <button type="button" id="simpan-kategori-cepat" class="px-4 py-2 text-sm text-white bg-indigo-600 rounded-md">Simpan</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Supplier --}}
                            <div x-data="{ modalOpen: false }"> 
                                <x-input-label for="id_supplier_dropdown" :value="__('Supplier')" />
                                <div class="flex items-center mt-1">
                                    <select name="id_supplier" id="id_supplier_dropdown" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                                        <option value="">Pilih Supplier</option>
                                        @foreach ($suppliers as $supplier)
                                            <option value="{{ $supplier->id_supplier }}" {{ old('id_supplier') == $supplier->id_supplier ? 'selected' : '' }}>{{ $supplier->nama_supplier }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" @click="modalOpen = true" class="ml-2 flex-shrink-0 px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-500 focus:outline-none">+</button>
                                </div>
                                <x-input-error :messages="$errors->get('id_supplier')" class="mt-2" />
                                {{-- Modal Supplier --}}
                                <div x-show="modalOpen" class="fixed inset-0 bg-gray-600 bg-opacity-75 z-50 flex items-center justify-center" x-cloak>
                                    <div class="relative mx-auto p-6 border w-full max-w-md shadow-lg rounded-md bg-white dark:bg-gray-800">
                                        <h3 class="text-xl font-medium mb-4">Tambah Supplier Cepat</h3>
                                        <div id="quick-add-supplier-form">
                                            <div class="mt-2">
                                                <x-input-label for="modal_nama_supplier" :value="__('Nama Supplier Baru')" />
                                                <x-text-input id="modal_nama_supplier" class="block mt-1 w-full" type="text" name="nama_supplier" />
                                                <p id="modal-error-supplier" class="text-sm text-red-600 mt-1"></p>
                                            </div>
                                            <div class="mt-4 flex justify-end space-x-2">
                                                <button type="button" id="batal-supplier-cepat" @click="modalOpen = false" class="px-4 py-2 text-sm bg-gray-200 rounded-md">Batal</button>
                                                <button type="button" id="simpan-supplier-cepat" class="px-4 py-2 text-sm text-white bg-indigo-600 rounded-md">Simpan</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="md:col-span-2">
                                <x-input-label for="deskripsi" :value="__('Keterangan (Opsional)')" />
                                <textarea id="deskripsi" name="deskripsi" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm">{{ old('deskripsi') }}</textarea>
                            </div>
                            <div>
                                <x-input-label for="gambar" :value="__('Gambar Produk (Opsional)')" />
                                <input id="gambar" class="block mt-1 w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none" type="file" name="gambar">
                            </div>
                        </div>

                        {{-- =============================================== --}}
                        {{-- 2. SATUAN & HARGA --}}
                        {{-- =============================================== --}}
                        <h3 class="text-lg font-semibold mb-4 border-b pb-2">Satuan & Harga</h3>
                        
                        {{-- Masukkan Data PHP ke Alpine di sini --}}
                        <div x-data="{ konversis: {{ json_encode($initialKonversi) }} }">
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4 p-4 border rounded-md bg-gray-50 dark:bg-gray-700">
                                <div>
                                    <x-input-label for="id_satuan_dasar" :value="__('Satuan Dasar (Wajib)')" />
                                    <select name="id_satuan_dasar" id="id_satuan_dasar" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                                        <option value="">Pilih Satuan (misal: PCS)</option>
                                        @foreach ($satuans as $satuan)
                                            <option value="{{ $satuan->id_satuan }}" {{ old('id_satuan_dasar') == $satuan->id_satuan ? 'selected' : '' }}>{{ $satuan->nama_satuan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="harga_pokok_dasar" :value="__('Harga Pokok Dasar (Modal)')" />
                                    <x-text-input id="harga_pokok_dasar" class="block mt-1 w-full" type="number" step="0.01" name="harga_pokok_dasar" :value="old('harga_pokok_dasar')" required />
                                </div>
                                <div>
                                    <x-input-label for="harga_jual_dasar" :value="__('Harga Jual Dasar')" />
                                    <x-text-input id="harga_jual_dasar" class="block mt-1 w-full" type="number" step="0.01" name="harga_jual_dasar" :value="old('harga_jual_dasar')" required />
                                </div>
                            </div>
    
                            <h4 class="text-md font-semibold mb-2 mt-6">Daftar Konversi (Opsional)</h4>

                            {{-- Tampilkan Pesan Error Khusus Konversi --}}
                            @if ($errors->has('konversi.*'))
                                <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded text-sm">
                                    <strong>Perbaiki Data Konversi:</strong>
                                    <ul class="list-disc list-inside mt-1">
                                        @foreach ($errors->get('konversi.*') as $fieldErrors)
                                            @foreach ($fieldErrors as $message)
                                                <li>{{ $message }}</li>
                                            @endforeach
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- LOOP KONVERSI --}}
                            <template x-for="(konversi, index) in konversis" :key="index">
                                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-2 p-4 border rounded-md relative bg-gray-50 dark:bg-gray-800">
                                    
                                    {{-- Satuan --}}
                                    <div>
                                        <x-input-label :value="__('Satuan Konversi')" />
                                        <select x-bind:name="'konversi[' + index + '][id_satuan_konversi]'" 
                                                x-model="konversi.id_satuan_konversi"
                                                class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                                            <option value="">Pilih Satuan</option>
                                            @foreach ($satuans as $satuan)
                                                <option value="{{ $satuan->id_satuan }}">{{ $satuan->nama_satuan }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Nilai --}}
                                    <div>
                                        <x-input-label :value="__('Nilai (x Dasar)')" />
                                        <x-text-input type="number" 
                                                      x-bind:name="'konversi[' + index + '][nilai_konversi]'" 
                                                      x-model="konversi.nilai_konversi" 
                                                      class="block mt-1 w-full" required placeholder="Contoh: 10" />
                                    </div>

                                    {{-- Harga Modal --}}
                                    <div>
                                        <x-input-label :value="__('Modal Konversi')" />
                                        <x-text-input type="number" step="0.01" 
                                                      x-bind:name="'konversi[' + index + '][harga_pokok_konversi]'" 
                                                      x-model="konversi.harga_pokok_konversi" 
                                                      class="block mt-1 w-full" required />
                                    </div>

                                    {{-- Harga Jual --}}
                                    <div>
                                        <x-input-label :value="__('Jual Konversi')" />
                                        <x-text-input type="number" step="0.01" 
                                                      x-bind:name="'konversi[' + index + '][harga_jual_konversi]'" 
                                                      x-model="konversi.harga_jual_konversi" 
                                                      class="block mt-1 w-full" required />
                                    </div>

                                    {{-- Hapus --}}
                                    <div class="flex items-end">
                                        <button type="button" @click="konversis.splice(index, 1)" class="px-3 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Hapus</button>
                                    </div>
                                </div>
                            </template>

                            <button type="button" @click="konversis.push({ id_satuan_konversi: '', nilai_konversi: '', harga_pokok_konversi: '', harga_jual_konversi: '' })" class="mt-2 px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                                + Tambah Konversi
                            </button>
                        </div>
    
                        {{-- Tombol Simpan --}}
                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.produk.index') }}" class="text-gray-600 dark:text-gray-400 hover:underline mr-4">Batal</a>
                            <x-primary-button>
                                {{ __('Simpan Produk') }}
                            </x-primary-button>
                        </div>
                    </form>
    
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPT TAMBAHAN UNTUK QUICK ADD KATEGORI/SUPPLIER (JIKA DIPERLUKAN) --}}
    @push('scripts')
    <script>
        // Script modal kategori/supplier bisa ditaruh sini jika belum ada di layout utama
        // (Seperti kode sebelumnya)
    </script>
    @endpush

</x-app-layout>