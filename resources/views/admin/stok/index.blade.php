<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manajemen Stok') }}
        </h2>
    </x-slot>

    {{-- 
        LOGIKA NAVIGASI KEYBOARD (Sama seperti Manajemen Produk)
    --}}
    <div class="py-12"
         x-data="{ 
             activeRow: null, 
             totalRows: {{ $riwayatStok->count() }},
             
             moveSelection(direction) {
                 if (this.totalRows === 0) return;

                 if (this.activeRow === null) {
                     this.activeRow = 0;
                 } else {
                     if (direction === 'down' && this.activeRow < this.totalRows - 1) {
                         this.activeRow++;
                     } else if (direction === 'up' && this.activeRow > 0) {
                         this.activeRow--;
                     }
                 }
                 
                 // Auto Scroll ke baris yang dipilih
                 const el = document.getElementById('stok-row-' + this.activeRow);
                 el?.scrollIntoView({ block: 'nearest' });
             }
         }"
         @keydown.window.arrow-down.prevent="moveSelection('down')"
         @keydown.window.arrow-up.prevent="moveSelection('up')">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    {{-- Pesan Sukses --}}
                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-md">{{ session('success') }}</div>
                    @endif

                    {{-- Tab Navigasi --}}
                    <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
                        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                            <li class="me-2">
                                <a href="{{ route('admin.stok.index') }}" class="inline-block p-4 text-blue-600 border-b-2 border-blue-600 rounded-t-lg active dark:text-blue-500 dark:border-blue-500">
                                    Riwayat Stok Masuk
                                </a>
                            </li>
                            <li class="me-2">
                                <a href="{{ route('admin.stok.create') }}" class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300">
                                    Input Stok Masuk
                                </a>
                            </li>
                        </ul>
                    </div>
                    
                    {{-- Filter & Pencarian --}}
                    <div class="flex justify-between items-center mb-6">
                        <form action="{{ route('admin.stok.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                            
                            {{-- 1. Input Pencarian (BARU) --}}
                            <div class="relative">
                                <input type="text" name="search" 
                                       value="{{ request('search') }}" 
                                       placeholder="Cari Supplier / Ket..." 
                                       class="w-64 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                            </div>

                            {{-- 2. Filter Tanggal (SAMA SEPERTI SEBELUMNYA) --}}
                            <div class="flex items-center space-x-2 bg-gray-100 dark:bg-gray-700 p-1 rounded-md">
                                <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}" 
                                       class="border-none bg-transparent text-sm focus:ring-0 p-1 dark:text-gray-300" title="Dari Tanggal">
                                <span class="text-gray-500">-</span>
                                <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}" 
                                       class="border-none bg-transparent text-sm focus:ring-0 p-1 dark:text-gray-300" title="Sampai Tanggal">
                            </div>

                            {{-- Tombol Filter --}}
                            <x-primary-button>Cari</x-primary-button>
                            
                            {{-- Tombol Reset --}}
                            @if(request('search') || request('tanggal_mulai'))
                                <a href="{{ route('admin.stok.index') }}" class="px-3 py-2 text-sm font-medium text-gray-600 bg-gray-200 rounded-md hover:bg-gray-300 transition">
                                    Reset
                                </a>
                            @endif
                        </form>

                        {{-- Tombol Input Stok (Tetap di Kanan) --}}
                        <a href="{{ route('admin.stok.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 transition shadow-lg">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Input Stok Masuk
                        </a>
                    </div>

                    {{-- Instruksi Kecil --}}
                    <div class="text-right text-xs text-gray-400 mb-2">
                        Gunakan <span class="font-bold">↑ ↓</span> untuk memilih transaksi.
                    </div>

                    {{-- Tabel Riwayat (Dipercantik) --}}
                    <div class="overflow-x-auto border rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">No. Transaksi</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Waktu</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Supplier</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Total Nilai</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Keterangan</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Diubah</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($riwayatStok as $index => $batch)
                                    <tr id="stok-row-{{ $index }}"
                                        class="transition-colors cursor-pointer hover:bg-blue-50 dark:hover:bg-gray-700"
                                        :class="activeRow === {{ $index }} ? 'bg-blue-100 dark:bg-blue-900' : ''"
                                        @click="activeRow = {{ $index }}">
                                        
                                        {{-- ID Transaksi (Mono & Biru) --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-blue-600 font-mono">
                                            STOK-{{ str_pad($batch->id_batch_stok, 4, '0', STR_PAD_LEFT) }}
                                        </td>
                                        
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 font-medium">
                                            {{ \Carbon\Carbon::parse($batch->tanggal_masuk)->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $batch->created_at->format('H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-gray-200">
                                            {{ $batch->supplier->nama_supplier ?? '-' }}
                                        </td>
                                        
                                        {{-- Total Nilai (Tebal) --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-gray-100">
                                            Rp {{ number_format($batch->total_nilai_faktur, 0, ',', '.') }}
                                        </td>
                                        
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 italic">
                                            {{ $batch->keterangan ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $batch->user->nama ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400 text-xs">
                                            {{ $batch->userDiubah->nama ?? '' }}
                                        </td>

                                        {{-- Tombol Aksi --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                            <a href="{{ route('admin.stok.edit', $batch->id_batch_stok) }}" class="text-indigo-600 hover:text-indigo-900 font-bold">
                                                Edit
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-10 text-center text-gray-500">
                                            Belum ada riwayat stok masuk.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Link Paging --}}
                    <div class="mt-4">
                        {{ $riwayatStok->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>