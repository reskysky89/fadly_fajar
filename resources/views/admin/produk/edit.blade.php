<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Produk') }} : {{ $produk->nama_produk }}
        </h2>
    </x-slot>

    {{-- LOGIKA PHP: SIAPKAN DATA KONVERSI --}}
    @php
        // 1. Cek apakah ada data inputan baru (karena error validasi)
        $dataKonversi = old('konversi');

        // 2. Jika tidak ada data baru (baru buka halaman edit), ambil dari DATABASE
        if (!$dataKonversi) {
            // Kita mapping agar formatnya sesuai dengan nama field di form
            $dataKonversi = $produk->produkKonversis->map(function($item) {
                return [
                    'id_satuan_konversi' => $item->id_satuan_konversi,
                    'nilai_konversi'     => $item->nilai_konversi,
                    'harga_pokok_konversi' => $item->harga_pokok_konversi,
                    'harga_jual_konversi'  => $item->harga_jual_konversi,
                ];
            });
        }
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
                            <strong class="font-bold">Gagal Mengupdate!</strong>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.produk.update', $produk->id_produk) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        {{-- 1. DATA UMUM --}}
                        <h3 class="text-lg font-semibold mb-4 border-b pb-2">Data Umum</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            {{-- Kode Item (Readonly) --}}
                            <div>
                                <x-input-label for="id_produk" :value="__('Kode Item (Tidak bisa diubah)')" />
                                <x-text-input id="id_produk" class="block mt-1 w-full bg-gray-100" type="text" :value="$produk->id_produk" readonly />
                            </div>
                            
                            {{-- Nama Produk --}}
                            <div>
                                <x-input-label for="nama_produk" :value="__('Nama Produk')" />
                                <x-text-input id="nama_produk" class="block mt-1 w-full" type="text" name="nama_produk" :value="old('nama_produk', $produk->nama_produk)" required />
                            </div>
                            
                            {{-- Kategori --}}
                            <div>
                                <x-input-label for="id_kategori" :value="__('Kategori Produk')" />
                                <select name="id_kategori" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="">Pilih Kategori</option>
                                    @foreach ($kategoris as $kategori)
                                        <option value="{{ $kategori->id_kategori }}" {{ old('id_kategori', $produk->id_kategori) == $kategori->id_kategori ? 'selected' : '' }}>
                                            {{ $kategori->nama_kategori }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Supplier --}}
                            <div>
                                <x-input-label for="id_supplier" :value="__('Supplier')" />
                                <select name="id_supplier" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="">Pilih Supplier</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id_supplier }}" {{ old('id_supplier', $produk->id_supplier) == $supplier->id_supplier ? 'selected' : '' }}>
                                            {{ $supplier->nama_supplier }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="md:col-span-2">
                                <x-input-label for="deskripsi" :value="__('Keterangan (Opsional)')" />
                                <textarea id="deskripsi" name="deskripsi" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('deskripsi', $produk->deskripsi) }}</textarea>
                            </div>
                            
                            <div>
                                <x-input-label for="gambar" :value="__('Ganti Gambar (Opsional)')" />
                                <input id="gambar" class="block mt-1 w-full text-sm border border-gray-300 rounded-lg" type="file" name="gambar">
                                @if($produk->gambar)
                                    <p class="text-xs text-gray-500 mt-1">Gambar saat ini:</p>
                                    <img src="{{ asset('storage/' . $produk->gambar) }}" alt="Gambar Produk" class="h-16 mt-1 rounded border">
                                @endif
                            </div>
                        </div>

                        {{-- 2. SATUAN & HARGA --}}
                        <h3 class="text-lg font-semibold mb-4 border-b pb-2">Satuan & Harga</h3>
                        
                        {{-- INI BAGIAN PENTINGNYA: Masukkan $dataKonversi ke Alpine --}}
                        <div x-data="{ konversis: {{ json_encode($dataKonversi) }} }">
                            
                            {{-- Satuan Dasar --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4 p-4 border rounded-md bg-gray-50 dark:bg-gray-700">
                                <div>
                                    <x-input-label for="id_satuan_dasar" :value="__('Satuan Dasar (Wajib)')" />
                                    <select name="id_satuan_dasar" id="id_satuan_dasar" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                        @foreach ($satuans as $satuan)
                                            <option value="{{ $satuan->id_satuan }}" {{ old('id_satuan_dasar', $produk->id_satuan_dasar) == $satuan->id_satuan ? 'selected' : '' }}>
                                                {{ $satuan->nama_satuan }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="harga_pokok_dasar" :value="__('Harga Pokok Dasar')" />
                                    <x-text-input id="harga_pokok_dasar" class="block mt-1 w-full" type="number" step="0.01" name="harga_pokok_dasar" :value="old('harga_pokok_dasar', $produk->harga_pokok_dasar)" required />
                                </div>
                                <div>
                                    <x-input-label for="harga_jual_dasar" :value="__('Harga Jual Dasar')" />
                                    <x-text-input id="harga_jual_dasar" class="block mt-1 w-full" type="number" step="0.01" name="harga_jual_dasar" :value="old('harga_jual_dasar', $produk->harga_jual_dasar)" required />
                                </div>
                            </div>
    
                            <h4 class="text-md font-semibold mb-2 mt-6">Daftar Konversi (Opsional)</h4>

                            {{-- Pesan Error Konversi --}}
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

                            {{-- Loop Konversi --}}
                            <template x-for="(konversi, index) in konversis" :key="index">
                                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-2 p-4 border rounded-md relative bg-gray-50 dark:bg-gray-800">
                                    
                                    <div>
                                        <x-input-label :value="__('Satuan Konversi')" />
                                        <select x-bind:name="'konversi[' + index + '][id_satuan_konversi]'" 
                                                x-model="konversi.id_satuan_konversi"
                                                class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                            <option value="">Pilih...</option>
                                            @foreach ($satuans as $satuan)
                                                <option value="{{ $satuan->id_satuan }}">{{ $satuan->nama_satuan }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <x-input-label :value="__('Nilai (x Dasar)')" />
                                        <x-text-input type="number" x-bind:name="'konversi[' + index + '][nilai_konversi]'" x-model="konversi.nilai_konversi" class="block mt-1 w-full" required />
                                    </div>

                                    <div>
                                        <x-input-label :value="__('Modal Konversi')" />
                                        <x-text-input type="number" step="0.01" x-bind:name="'konversi[' + index + '][harga_pokok_konversi]'" x-model="konversi.harga_pokok_konversi" class="block mt-1 w-full" required />
                                    </div>

                                    <div>
                                        <x-input-label :value="__('Jual Konversi')" />
                                        <x-text-input type="number" step="0.01" x-bind:name="'konversi[' + index + '][harga_jual_konversi]'" x-model="konversi.harga_jual_konversi" class="block mt-1 w-full" required />
                                    </div>

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
                            <a href="{{ route('admin.produk.index') }}" class="text-gray-600 hover:underline mr-4">Batal</a>
                            <x-primary-button>
                                {{ __('Simpan Perubahan') }}
                            </x-primary-button>
                        </div>
                    </form>
    
                </div>
            </div>
        </div>
    </div>

    {{-- Script Modal (Sama seperti Create) --}}
    @push('scripts')
    <script>
        // Script modal kategori/supplier (jika diperlukan)
    </script>
    @endpush

</x-app-layout>