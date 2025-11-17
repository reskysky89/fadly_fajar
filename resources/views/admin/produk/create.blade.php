<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tambah Produk Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    {{-- Tampilkan error jika DB Transaction gagal --}}
                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-md">
                            {{ session('error') }}
                        </div>
                    @endif

                    {{-- FORM UTAMA --}}
                    <form action="{{ route('admin.produk.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- =============================================== --}}
                        {{-- 1. DATA UMUM --}}
                        {{-- =============================================== --}}
                        <h3 class="text-lg font-semibold mb-4 border-b pb-2">Data Umum</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div>
                                <x-input-label for="id_produk" :value="__('Kode Item / Barcode')" />
                                <x-text-input id="id_produk" class="block mt-1 w-full" type="text" name="id_produk" :value="old('id_produk')" required autofocus />
                                <x-input-error :messages="$errors->get('id_produk')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="nama_produk" :value="__('Nama Produk')" />
                                <x-text-input id="nama_produk" class="block mt-1 w-full" type="text" name="nama_produk" :value="old('nama_produk')" required />
                                <x-input-error :messages="$errors->get('nama_produk')" class="mt-2" />
                            </div>
                            
                            {{-- BLOK KATEGORI PRODUK (HTML Modal ada di sini) --}}
                            <div x-data="{ modalOpen: false }"> 
                                <x-input-label for="id_kategori_dropdown" :value="__('Kategori Produk')" />
                                <div class="flex items-center mt-1">
                                    <select name="id_kategori" id="id_kategori_dropdown" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                                        <option value="">Pilih Kategori</option>
                                        @foreach ($kategoris as $kategori)
                                            <option value="{{ $kategori->id_kategori }}" {{ old('id_kategori') == $kategori->id_kategori ? 'selected' : '' }}>{{ $kategori->nama_kategori }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" @click="modalOpen = true" class="ml-2 flex-shrink-0 px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-500 focus:outline-none" title="Tambah Kategori Baru">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                    </button>
                                </div>
                                <x-input-error :messages="$errors->get('id_kategori')" class="mt-2" />
                                
                                {{-- MODAL KATEGORI --}}
                                <div x-show="modalOpen" x-transition class="fixed inset-0 bg-gray-600 bg-opacity-75 z-50 flex items-center justify-center" @click.away="modalOpen = false" x-cloak>
                                    <div @click.stop class="relative mx-auto p-6 border w-full max-w-md shadow-lg rounded-md bg-white dark:bg-gray-800">
                                        <h3 class="text-xl font-medium mb-4">Tambah Kategori Cepat</h3>
                                        <div id="quick-add-kategori-form">
                                            <div class="mt-2">
                                                <x-input-label for="modal_nama_kategori" :value="__('Nama Kategori Baru')" />
                                                {{-- Hapus 'required' dari input modal --}}
                                                <x-text-input id="modal_nama_kategori" class="block mt-1 w-full" type="text" name="nama_kategori" /> 
                                                <p id="modal-error-kategori" class="text-sm text-red-600 mt-1"></p>
                                            </div>
                                            <div class="mt-4 flex justify-end space-x-2">
                                                <button type="button" id="batal-kategori-cepat" @click="modalOpen = false" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-200 dark:bg-gray-600 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500">Batal</button>
                                                <button type="button" id="simpan-kategori-cepat" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">Simpan</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- BLOK SUPPLIER (HTML Modal ada di sini) --}}
                            <div x-data="{ modalOpen: false }"> 
                                <x-input-label for="id_supplier_dropdown" :value="__('Supplier')" />
                                <div class="flex items-center mt-1">
                                    <select name="id_supplier" id="id_supplier_dropdown" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                                        <option value="">Pilih Supplier</option>
                                        @foreach ($suppliers as $supplier)
                                            <option value="{{ $supplier->id_supplier }}" {{ old('id_supplier') == $supplier->id_supplier ? 'selected' : '' }}>{{ $supplier->nama_supplier }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" @click="modalOpen = true" class="ml-2 flex-shrink-0 px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-500 focus:outline-none" title="Tambah Supplier Baru">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                    </button>
                                </div>
                                <x-input-error :messages="$errors->get('id_supplier')" class="mt-2" />
                                
                                {{-- MODAL SUPPLIER --}}
                                <div x-show="modalOpen" x-transition class="fixed inset-0 bg-gray-600 bg-opacity-75 z-50 flex items-center justify-center" @click.away="modalOpen = false" x-cloak>
                                    <div @click.stop class="relative mx-auto p-6 border w-full max-w-md shadow-lg rounded-md bg-white dark:bg-gray-800">
                                        <h3 class="text-xl font-medium mb-4">Tambah Supplier Cepat</h3>
                                        <div id="quick-add-supplier-form">
                                            <div class="mt-2">
                                                <x-input-label for="modal_nama_supplier" :value="__('Nama Supplier Baru')" />
                                                {{-- Hapus 'required' dari input modal --}}
                                                <x-text-input id="modal_nama_supplier" class="block mt-1 w-full" type="text" name="nama_supplier" />
                                                <p id="modal-error-supplier" class="text-sm text-red-600 mt-1"></p>
                                            </div>
                                            <div class="mt-4 flex justify-end space-x-2">
                                                <button type="button" id="batal-supplier-cepat" @click="modalOpen = false" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-200 dark:bg-gray-600 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500">Batal</button>
                                                <button type="button" id="simpan-supplier-cepat" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">Simpan</button>
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
                                <input id="gambar" class="block mt-1 w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" type="file" name="gambar">
                                <x-input-error :messages="$errors->get('gambar')" class="mt-2" />
                            </div>
                        </div>

                        {{-- =============================================== --}}
                        {{-- 2. SATUAN & HARGA POKOK --}}
                        {{-- =============================================== --}}
                        <div x-data="{ konversis: [] }">
                            <h3 class="text-lg font-semibold mb-4 border-b pb-2">Satuan & Harga Pokok (Modal)</h3>
                            
                            {{-- Bagian Satuan Dasar --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4 p-4 border rounded-md bg-gray-50 dark:bg-gray-700">
                                <div>
                                    <x-input-label for="id_satuan_dasar" :value="__('Satuan Dasar (Wajib)')" />
                                    <select name="id_satuan_dasar" id="id_satuan_dasar" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                                        <option value="">Pilih Satuan Terkecil (misal: PCS)</option>
                                        @foreach ($satuans as $satuan)
                                            <option value="{{ $satuan->id_satuan }}">{{ $satuan->nama_satuan }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('id_satuan_dasar')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="harga_pokok_dasar" :value="__('Harga Pokok Dasar (Modal)')" />
                                    <x-text-input id="harga_pokok_dasar" class="block mt-1 w-full" type="number" step="0.01" name="harga_pokok_dasar" :value="old('harga_pokok_dasar')" required />
                                    <x-input-error :messages="$errors->get('harga_pokok_dasar')" class="mt-2" />
                                </div>
                            </div>
    
                            {{-- Bagian Daftar Konversi (Dinamis dengan Alpine.js) --}}
                            <h4 class="text-md font-semibold mb-2">Daftar Konversi (Opsional)</h4>
                            <template x-for="(konversi, index) in konversis" :key="index">
                                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-2 p-4 border rounded-md relative">
                                    <div>
                                        <x-input-label :value="__('Satuan Konversi')" />
                                        {{-- PERBAIKAN: Menggunakan x-bind:name untuk Alpine --}}
                                        <select x-bind:name="'konversi[' + index + '][id_satuan_konversi]'" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                                            <option value="">Pilih Satuan</option>
                                            @foreach ($satuans as $satuan)
                                                <option value="{{ $satuan->id_satuan }}">{{ $satuan->nama_satuan }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <x-input-label :value="__('Nilai Konversi (x Satuan Dasar)')" />
                                        {{-- PERBAIKAN: Menggunakan x-bind:name untuk Alpine --}}
                                        <x-text-input type="number" x-bind:name="'konversi[' + index + '][nilai_konversi]'" class="block mt-1 w-full" required />
                                    </div>
                                    <div>
                                        <x-input-label :value="__('Harga Pokok Konversi (Modal)')" />
                                        {{-- PERBAIKAN: Menggunakan x-bind:name untuk Alpine --}}
                                        <x-text-input type="number" step="0.01" x-bind:name="'konversi[' + index + '][harga_pokok_konversi]'" class="block mt-1 w-full" required />
                                    </div>
                                    <div>
                                        <x-input-label :value="__('Harga Jual Konversi')" />
                                        {{-- PERBAIKAN: Menggunakan x-bind:name untuk Alpine --}}
                                        <x-text-input type="number" step="0.01" x-bind:name="'konversi[' + index + '][harga_jual_konversi]'" class="block mt-1 w-full" required />
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
                                <x-text-input id="harga_jual_dasar" class="block mt-1 w-full" type="number" step="0.01" name="harga_jual_dasar" :value="old('harga_jual_dasar')" required />
                                <x-input-error :messages="$errors->get('harga_jual_dasar')" class="mt-2" />
                            </div>
                        </div>
                        {{-- Harga Jual untuk Konversi dimasukkan di tabel 'Daftar Konversi' di atas --}}
    
                        {{-- Tombol Simpan & Batal FORM UTAMA --}}
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

    {{-- =================================================== --}}
    {{-- SCRIPT AJAX DITEMPATKAN DI SINI --}}
    {{-- =================================================== --}}
    @push('scripts')
    {{-- Script untuk Kategori Quick Add --}}
    <script>
        // Pastikan script ini hanya berjalan jika tombolnya ada
        if (document.getElementById('simpan-kategori-cepat')) {
            document.getElementById('simpan-kategori-cepat').addEventListener('click', async function(e) {
                e.preventDefault(); 
                const namaKategoriInput = document.getElementById('modal_nama_kategori');
                const errorMessage = document.getElementById('modal-error-kategori');
                const dropdown = document.getElementById('id_kategori_dropdown');
                const batalButton = document.getElementById('batal-kategori-cepat');
                
                errorMessage.textContent = ''; 
                errorMessage.classList.remove('text-green-600', 'text-red-600');

                try {
                    const response = await fetch("{{ route('admin.kategori.store') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json' 
                        },
                        body: JSON.stringify({ nama_kategori: namaKategoriInput.value })
                    });
                    const data = await response.json();
                    if (!response.ok) {
                        errorMessage.classList.add('text-red-600');
                        if (data.errors && data.errors.nama_kategori) {
                            errorMessage.textContent = data.errors.nama_kategori[0];
                        } else { errorMessage.textContent = data.message || 'Terjadi kesalahan.'; }
                    } else if (data.success) {
                        const newOption = new Option(data.kategori.nama_kategori, data.kategori.id_kategori, true, true);
                        dropdown.add(newOption);
                        namaKategoriInput.value = ''; 
                        errorMessage.textContent = 'Berhasil disimpan!';
                        errorMessage.classList.add('text-green-600');
                        setTimeout(() => {
                            batalButton.click();
                            errorMessage.textContent = ''; 
                            errorMessage.classList.remove('text-green-600');
                        }, 1000); 
                    }
                } catch (error) {
                    console.error('Error Kategori:', error);
                    errorMessage.textContent = 'Gagal terhubung ke server (Script Error).';
                }
            });
        }
    </script>
    
    {{-- Script untuk Supplier Quick Add --}}
    <script>
        // Pastikan script ini hanya berjalan jika tombolnya ada
        if (document.getElementById('simpan-supplier-cepat')) {
            document.getElementById('simpan-supplier-cepat').addEventListener('click', async function(e) {
                e.preventDefault(); 
                const namaSupplierInput = document.getElementById('modal_nama_supplier');
                const errorMessage = document.getElementById('modal-error-supplier');
                const dropdown = document.getElementById('id_supplier_dropdown');
                const batalButton = document.getElementById('batal-supplier-cepat');
                
                errorMessage.textContent = ''; 
                errorMessage.classList.remove('text-green-600', 'text-red-600');

                try {
                    const response = await fetch("{{ route('admin.supplier.store') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ nama_supplier: namaSupplierInput.value })
                    });
                    const data = await response.json();
                    if (!response.ok) {
                        errorMessage.classList.add('text-red-600');
                        if (data.errors && data.errors.nama_supplier) {
                            errorMessage.textContent = data.errors.nama_supplier[0];
                        } else { errorMessage.textContent = data.message || 'Terjadi kesalahan.'; }
                    } else if (data.success) {
                        const newOption = new Option(data.supplier.nama_supplier, data.supplier.id_supplier, true, true);
                        dropdown.add(newOption);
                        namaSupplierInput.value = ''; 
                        errorMessage.textContent = 'Berhasil disimpan!';
                        errorMessage.classList.add('text-green-600');
                        setTimeout(() => {
                            batalButton.click();
                            errorMessage.textContent = ''; 
                            errorMessage.classList.remove('text-green-600');
                        }, 1000); 
                    }
                } catch (error) {
                    console.error('Error Supplier:', error);
                    errorMessage.textContent = 'Gagal terhubung ke server (Script Error).';
                }
            });
        }
    </script>
    @endpush

</x-app-layout>