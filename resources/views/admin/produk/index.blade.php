{{-- resources/views/admin/produk/index.blade.php --}}
<x-app-layout>
    
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manajemen Produk') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Tampilkan pesan sukses jika ada --}}
                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-md">
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- Bagian Atas: Pencarian dan Tombol Tambah --}}
                    <div class="flex justify-between items-center mb-6">
                        
                        {{-- Search Bar --}}
                        <div>
                            <form action="{{ route('admin.produk.index') }}" method="GET" class="flex items-center space-x-2">
                                <x-text-input id="search" class="block w-full" type="text" name="search" placeholder="Cari Kode atau Nama Produk..." :value="request('search')" />
                                <x-primary-button>Cari</x-primary-button>
                            </form>
                        </div>

                        <div class="flex space-x-2">
                            {{-- Tombol "Manajemen Satuan" --}}
                            <a href="{{ route('admin.satuan.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 active:bg-gray-700">
                                Manajemen Satuan
                            </a>
                            {{-- Tombol Tambah Produk Baru --}}
                            <a href="{{ route('admin.produk.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 ...">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" ...><path ...></path></svg>
                                Tambah Produk Baru
                            </a>
                        </div>
                    </div>

                    {{-- =============================================== --}}
                    {{-- TABEL DAFTAR PRODUK (VERSI BARU DENGAN LOOP BERSARANG) --}}
                    {{-- =============================================== --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left">Kode Item</th>
                                    <th class="px-6 py-3 text-left">Nama Barang</th>
                                    <th class="px-6 py-3 text-left">Kategori</th>
                                    <th class="px-6 py-3 text-left">Satuan</th> {{-- Kolom Stok diubah jadi Satuan --}}
                                    <th class="px-6 py-3 text-left">Stok</th> {{-- Kolom Stok baru (hanya angka) --}}
                                    <th class="px-6 py-3 text-left">Harga Beli</th>
                                    <th class="px-6 py-3 text-left">Harga Jual</th>
                                    <th class="px-6 py-3 text-left">Status</th>
                                    <th class="px-6 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                
                                @forelse ($produks as $produk)
                                    @php
                                        // Kalkulasi Stok (sudah ada di Controller)
                                        $stok_dasar = $produk->stok_saat_ini ?? 0;
                                    @endphp

                                    {{-- BARIS 1: UNTUK SATUAN DASAR (MISAL: PCS) --}}
                                    <tr class="bg-white dark:bg-gray-800">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ $produk->id_produk }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ $produk->nama_produk }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $produk->kategori->nama_kategori ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold">{{ $produk->satuanDasar->nama_satuan ?? 'PCS' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold {{ $stok_dasar <= 0 ? 'text-red-500' : '' }}">{{ $stok_dasar }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">Rp {{ number_format($produk->harga_pokok_dasar, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">Rp {{ number_format($produk->harga_jual_dasar, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{-- PERBAIKAN TOMBOL STATUS: Ganti <form> jadi <a> --}}
                                            <a href="{{ route('admin.produk.toggleStatus', $produk->id_produk) }}" 
                                               title="Klik untuk mengubah status">
                                                @if($produk->status_produk == 'aktif')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Nonaktif</span>
                                                @endif
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                            <a href="{{ route('admin.produk.edit', $produk->id_produk) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            {{-- Form Hapus (Ini aman karena hanya 1 form per baris utama) --}}
                                            <form action="{{ route('admin.produk.destroy', $produk->id_produk) }}" method="POST" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 ml-4" onclick="return confirm('Hapus produk {{ $produk->nama_produk }}? Ini akan menghapus semua konversi dan stoknya.')">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>

                                    {{-- BARIS 2...N: UNTUK SATUAN KONVERSI (MISAL: DUS, BAL) --}}
                                    @foreach ($produk->produkKonversis as $konv)
                                        @php
                                            // Kalkulasi stok konversi (e.g., 250 / 40 = 6.25)
                                            $stok_konversi = ($konv->nilai_konversi > 0) ? ($stok_dasar / $konv->nilai_konversi) : 0;
                                        @endphp
                                        <tr class="bg-gray-50 dark:bg-gray-700">
                                            <td class="px-6 py-4"></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                - {{ $produk->nama_produk }}
                                            </td>
                                            <td class="px-6 py-4"></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold">{{ $konv->satuan->nama_satuan ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold">{{ number_format($stok_konversi, 2, ',', '.') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">Rp {{ number_format($konv->harga_pokok_konversi, 0, ',', '.') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">Rp {{ number_format($konv->harga_jual_konversi, 0, ',', '.') }}</td>
                                            <td class="px-6 py-4"></td>
                                            <td class="px-6 py-4"></td>
                                        </tr>
                                    @endforeach

                                @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                            Belum ada data produk. Silakan <a href="{{ route('admin.produk.create') }}" class="text-blue-500 hover:underline">Tambah Produk Baru</a>.
                                        </td>
                                    </tr>
                                @endforelse

                            </tbody>
                        </table>
                    </div>

                    {{-- Link Paging --}}
                    <div class="mt-4">
                        {{ $produks->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>