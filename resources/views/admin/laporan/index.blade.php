<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Laporan Penjualan') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ 
             activeRow: null, 
             totalRows: {{ $laporan->count() }},
             moveSelection(direction) {
                 if (this.totalRows === 0) return;
                 if (this.activeRow === null) { this.activeRow = 0; } 
                 else {
                     if (direction === 'down' && this.activeRow < this.totalRows - 1) { this.activeRow++; } 
                     else if (direction === 'up' && this.activeRow > 0) { this.activeRow--; }
                 }
                 document.getElementById('laporan-row-' + this.activeRow)?.scrollIntoView({ block: 'nearest' });
             }
         }"
         @keydown.window.arrow-down.prevent="moveSelection('down')"
         @keydown.window.arrow-up.prevent="moveSelection('up')">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- 1. CARD RINGKASAN --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-green-600 rounded-lg shadow p-6 text-white">
                    <div class="text-sm uppercase font-bold opacity-75">Total Omzet</div>
                    <div class="text-3xl font-extrabold mt-2">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</div>
                </div>
                <div class="bg-blue-600 rounded-lg shadow p-6 text-white">
                    <div class="text-sm uppercase font-bold opacity-75">Total Transaksi</div>
                    <div class="text-3xl font-extrabold mt-2">{{ number_format($totalTransaksi) }}</div>
                </div>
                <div class="bg-purple-600 rounded-lg shadow p-6 text-white">
                    <div class="text-sm uppercase font-bold opacity-75">Rata-rata</div>
                    <div class="text-3xl font-extrabold mt-2">Rp {{ $totalTransaksi > 0 ? number_format($totalOmzet / $totalTransaksi, 0, ',', '.') : 0 }}</div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- 2. FORM FILTER & PENCARIAN (GABUNGAN) --}}
                    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                        <form action="{{ route('admin.laporan.index') }}" method="GET">
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                                
                                {{-- Input Pencarian (4 Kolom) --}}
                                <div class="md:col-span-4">
                                    <x-input-label for="search" :value="__('Pencarian (ID / Pelanggan / Barang)')" />
                                    <div class="relative mt-1">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                        </div>
                                        <input type="text" name="search" value="{{ request('search') }}" 
                                               placeholder="Cari: 'Indomie', 'Budi', atau 'TRX-001'..." 
                                               class="block w-full pl-10 border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>

                                {{-- Filter Tanggal (4 Kolom) --}}
                                <div class="md:col-span-2">
                                    <x-input-label for="tanggal_mulai" :value="__('Dari')" />
                                    <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai', date('Y-m-01')) }}" class="block mt-1 w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm">
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="tanggal_akhir" :value="__('Sampai')" />
                                    <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir', date('Y-m-d')) }}" class="block mt-1 w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm">
                                </div>

                                {{-- Filter Kasir (2 Kolom) --}}
                                <div class="md:col-span-2">
                                    <x-input-label for="id_kasir" :value="__('Kasir')" />
                                    <select name="id_kasir" class="block mt-1 w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm">
                                        <option value="semua">Semua</option>
                                        @foreach($listKasir as $k)
                                            <option value="{{ $k->id_user }}" {{ request('id_kasir') == $k->id_user ? 'selected' : '' }}>{{ $k->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Tombol (2 Kolom) --}}
                                <div class="md:col-span-2 flex space-x-2">
                                    <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md font-bold shadow h-[38px] mt-6">Cari</button>
                                    @if(request()->has('search') || request()->has('tanggal_mulai'))
                                        <a href="{{ route('admin.laporan.index') }}" class="px-3 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-md shadow h-[38px] mt-6 flex items-center justify-center" title="Reset Filter">
                                            X
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- 3. TABEL --}}
                    <div class="overflow-x-auto border rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">No. Transaksi</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Waktu</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Kasir</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Pelanggan</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Total</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($laporan as $index => $transaksi)
                                    <tr id="laporan-row-{{ $index }}" 
                                        class="transition-colors cursor-pointer hover:bg-blue-50 dark:hover:bg-gray-700"
                                        :class="activeRow === {{ $index }} ? 'bg-blue-100 dark:bg-blue-900' : ''"
                                        @click="activeRow = {{ $index }}">
                                        
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-blue-600 font-mono">
                                            {{ $transaksi->id_transaksi }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ \Carbon\Carbon::parse($transaksi->waktu_transaksi)->setTimezone('Asia/Makassar')->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                            {{ $transaksi->nama_kasir }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $transaksi->nama_pelanggan ?? 'UMUM' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-right text-gray-900 dark:text-white">
                                            Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                            <a href="{{ route('admin.laporan.edit', ['id' => $transaksi->id_transaksi]) }}" class="text-indigo-600 hover:text-indigo-900 font-bold">Edit</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-10 text-center text-gray-500 italic">
                                            Tidak ada data penjualan yang cocok.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $laporan->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>