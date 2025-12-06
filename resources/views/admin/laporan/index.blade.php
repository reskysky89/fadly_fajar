<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-800 dark:text-white leading-tight">
            {{ __('Laporan Penjualan') }}
        </h2>
    </x-slot>

    {{-- WRAPPER UTAMA (FULL SCREEN LOGIC) --}}
    <div class="h-[calc(100vh-65px)] flex flex-col bg-gray-100 dark:bg-gray-900"
         x-data="{ 
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

        {{-- BAGIAN ATAS: RINGKASAN & FILTER (TIDAK IKUT SCROLL) --}}
        <div class="flex-shrink-0 p-4 md:p-6 pb-0">
            
            {{-- 1. CARD RINGKASAN --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm border border-green-200 dark:border-gray-700">
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wide">Total Omzet</div>
                    <div class="text-2xl font-extrabold text-green-600 mt-1">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm border border-blue-200 dark:border-gray-700">
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wide">Total Transaksi</div>
                    <div class="text-2xl font-extrabold text-blue-600 mt-1">{{ number_format($totalTransaksi) }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm border border-purple-200 dark:border-gray-700">
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wide">Rata-rata / Struk</div>
                    <div class="text-2xl font-extrabold text-purple-600 mt-1">
                        Rp {{ $totalTransaksi > 0 ? number_format($totalOmzet / $totalTransaksi, 0, ',', '.') : 0 }}
                    </div>
                </div>
            </div>

            {{-- 2. FILTER BAR --}}
            <div class="bg-white dark:bg-gray-800 p-4 rounded-t-xl border-b border-gray-200 shadow-sm">
                <form action="{{ route('admin.laporan.index') }}" method="GET">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                        {{-- Cari --}}
                        <div class="md:col-span-4">
                            <x-input-label for="search" :value="__('Cari Data')" />
                            <div class="relative mt-1">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg></div>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="No. TRX / Pelanggan / Produk..." class="block w-full pl-9 border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        {{-- Tgl Mulai --}}
                        <div class="md:col-span-2">
                            <x-input-label for="tanggal_mulai" :value="__('Dari')" />
                            <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai', date('Y-m-01')) }}" class="block mt-1 w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm focus:ring-blue-500">
                        </div>
                        {{-- Tgl Akhir --}}
                        <div class="md:col-span-2">
                            <x-input-label for="tanggal_akhir" :value="__('Sampai')" />
                            <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir', date('Y-m-d')) }}" class="block mt-1 w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm focus:ring-blue-500">
                        </div>
                        {{-- Kasir --}}
                        <div class="md:col-span-2">
                            <x-input-label for="id_kasir" :value="__('Kasir')" />
                            <select name="id_kasir" class="block mt-1 w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm focus:ring-blue-500">
                                <option value="semua">Semua</option>
                                @foreach($listKasir as $k)
                                    <option value="{{ $k->id_user }}" {{ request('id_kasir') == $k->id_user ? 'selected' : '' }}>{{ $k->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Tombol --}}
                        <div class="md:col-span-2 flex gap-1">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded-md text-sm h-[38px] mt-6">Filter</button>
                            @if(request()->has('search') || request()->has('tanggal_mulai'))
                                <a href="{{ route('admin.laporan.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-3 rounded-md text-sm h-[38px] mt-6 flex items-center justify-center">Reset</a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- BAGIAN 3: TABEL SCROLLABLE --}}
        <div class="flex-1 overflow-hidden px-4 md:px-6 pb-6">
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-b-xl h-full flex flex-col">
                
                <div class="overflow-y-auto flex-1 custom-scrollbar">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 relative">
                        {{-- HEADER LENGKET (STICKY) --}}
                        <thead class="bg-gray-100 dark:bg-gray-700 sticky top-0 z-10 shadow-sm">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">No. Transaksi</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Waktu</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Pelanggan</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Total Belanja</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider pl-10">Kasir</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        
                        {{-- BODY SCROLLABLE --}}
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($laporan as $index => $trx)
                                <tr id="laporan-row-{{ $index }}" 
                                    class="transition-colors cursor-pointer hover:bg-blue-50 dark:hover:bg-gray-700"
                                    :class="activeRow === {{ $index }} ? 'bg-blue-100 dark:bg-blue-900' : ''"
                                    @click="activeRow = {{ $index }}">
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono font-bold text-blue-600">
                                        {{ $trx->id_transaksi }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $trx->created_at->setTimezone('Asia/Makassar')->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800 dark:text-gray-200">
                                        {{ $trx->nama_pelanggan ?? 'UMUM' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-extrabold text-right text-gray-900 dark:text-white">
                                        Rp {{ number_format($trx->total_harga, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 pl-10">
                                        {{ $trx->nama_kasir }}
                                    </td>
                                    
                                    {{-- Aksi: Cuma Tombol Edit --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <a href="{{ route('admin.laporan.edit', ['id' => $trx->id_transaksi]) }}" 
                                           class="text-orange-500 hover:text-orange-700 font-bold text-sm flex items-center justify-center gap-1 transition bg-orange-50 hover:bg-orange-100 px-3 py-1 rounded-full">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-500 italic bg-gray-50">
                                        Tidak ada data penjualan pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- TOTAL DATA --}}
                <div class="p-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-right text-xs text-gray-500">
                    Menampilkan semua data ({{ $laporan->count() }} Transaksi)
                </div>

            </div>
        </div>
    </div>
    
</x-app-layout>