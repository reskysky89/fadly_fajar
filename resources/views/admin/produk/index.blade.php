<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manajemen Produk') }}
        </h2>
    </x-slot>

    {{-- Alpine Data untuk Navigasi Keyboard --}}
    <div class="py-12" 
         x-data="{ 
             activeRow: 0, 
             totalRows: {{ $produks->reduce(function($carry, $produk) {
                 return $carry + 1 + $produk->produkKonversis->count();
             }, 0) }},
             moveSelection(direction) {
                 if (this.totalRows === 0) return;
                 if (direction === 'down' && this.activeRow < this.totalRows - 1) { this.activeRow++; } 
                 else if (direction === 'up' && this.activeRow > 0) { this.activeRow--; }
                 document.getElementById('product-row-' + this.activeRow)?.scrollIntoView({ block: 'nearest' });
             }
         }"
         @keydown.window.arrow-down.prevent="moveSelection('down')"
         @keydown.window.arrow-up.prevent="moveSelection('up')">
         
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-md">{{ session('success') }}</div>
                    @endif

                    {{-- Header: Pencarian & Tombol --}}
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <form action="{{ route('admin.produk.index') }}" method="GET" class="flex items-center space-x-2">
                                <x-text-input id="search" class="block w-full" type="text" name="search" placeholder="Cari Kode atau Nama..." :value="request('search')" />
                                <x-primary-button>Cari</x-primary-button>
                            </form>
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.satuan.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase hover:bg-gray-500">Manajemen Satuan</a>
                            <a href="{{ route('admin.produk.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase hover:bg-blue-500">
                                + Tambah Produk Baru
                            </a>
                        </div>
                    </div>

                    {{-- Tabel Produk --}}
                    <div class="overflow-x-auto border rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left">Kode Item</th>
                                    <th class="px-6 py-3 text-left">Nama Barang</th>
                                    <th class="px-6 py-3 text-left">Kategori</th>
                                    <th class="px-6 py-3 text-left">Supplier</th>
                                    <th class="px-6 py-3 text-left">Satuan</th>
                                    <th class="px-6 py-3 text-left">Stok</th>
                                    <th class="px-6 py-3 text-left">Harga Beli</th>
                                    <th class="px-6 py-3 text-left">Harga Jual</th>
                                    <th class="px-6 py-3 text-left">Status</th>
                                    <th class="px-6 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            
                            {{-- PENTING: ID ini digunakan oleh Script Auto Refresh --}}
                            <tbody id="live-data-produk" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                {{-- Kita panggil file terpisah yang baru Anda buat --}}
                                @include('admin.produk.table_body')
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
    
    {{-- SCRIPT AUTO REFRESH (3 Detik) --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            setInterval(function() {
                let url = window.location.href;
                fetch(url, { headers: { "X-Requested-With": "XMLHttpRequest" } })
                .then(response => response.text())
                .then(html => {
                    // Ganti isi tabel dengan data terbaru
                    document.getElementById('live-data-produk').innerHTML = html;
                })
                .catch(error => console.error('Gagal update stok otomatis:', error));
            }, 2000); 
        });
    </script>
</x-app-layout>