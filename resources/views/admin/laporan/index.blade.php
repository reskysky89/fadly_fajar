<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Laporan Penjualan') }}
        </h2>
    </x-slot>

    {{-- LOGIKA NAVIGASI KEYBOARD (Alpine.js) --}}
    <div class="py-12"
         x-data="{ 
             activeRow: null, 
             totalRows: {{ $laporan->count() }},
             
             moveSelection(direction) {
                 if (this.totalRows === 0) return;

                 if (this.activeRow === null) {
                     this.activeRow = 0; // Mulai dari baris pertama
                 } else {
                     if (direction === 'down' && this.activeRow < this.totalRows - 1) {
                         this.activeRow++;
                     } else if (direction === 'up' && this.activeRow > 0) {
                         this.activeRow--;
                     }
                 }
                 
                 // Auto Scroll agar baris yang dipilih selalu terlihat
                 const el = document.getElementById('laporan-row-' + this.activeRow);
                 el?.scrollIntoView({ block: 'nearest' });
             },

             // Fungsi untuk tombol Edit (bisa dihubungkan ke route edit nanti)
             editTerpilih() {
                 if (this.activeRow !== null) {
                     // Simulasi klik tombol edit di baris yang aktif
                     const btn = document.querySelector(`#laporan-row-${this.activeRow} .btn-edit`);
                     if(btn) btn.click();
                 }
             }
         }"
         @keydown.window.arrow-down.prevent="moveSelection('down')"
         @keydown.window.arrow-up.prevent="moveSelection('up')"
         @keydown.window.enter.prevent="editTerpilih()" {{-- Tekan Enter untuk Edit --}}>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- 1. CARD RINGKASAN (Tetap) --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-green-600 rounded-lg shadow p-6 text-white">
                    <div class="text-sm uppercase font-bold opacity-75">Total Pendapatan (Omzet)</div>
                    <div class="text-3xl font-extrabold mt-2">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</div>
                    <div class="text-xs mt-1 opacity-75">Sesuai filter yang dipilih</div>
                </div>
                <div class="bg-blue-600 rounded-lg shadow p-6 text-white">
                    <div class="text-sm uppercase font-bold opacity-75">Total Transaksi</div>
                    <div class="text-3xl font-extrabold mt-2">{{ number_format($totalTransaksi) }}</div>
                    <div class="text-xs mt-1 opacity-75">Kali penjualan berhasil</div>
                </div>
                <div class="bg-purple-600 rounded-lg shadow p-6 text-white">
                    <div class="text-sm uppercase font-bold opacity-75">Rata-rata per Struk</div>
                    <div class="text-3xl font-extrabold mt-2">Rp {{ $totalTransaksi > 0 ? number_format($totalOmzet / $totalTransaksi, 0, ',', '.') : 0 }}</div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- 2. FORM FILTER (Tetap) --}}
                    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                        <form action="{{ route('admin.laporan.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div>
                                <x-input-label for="tanggal_mulai" :value="__('Dari Tanggal')" />
                                <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai', date('Y-m-01')) }}" class="block mt-1 w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm">
                            </div>
                            <div>
                                <x-input-label for="tanggal_akhir" :value="__('Sampai Tanggal')" />
                                <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir', date('Y-m-d')) }}" class="block mt-1 w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm">
                            </div>
                            <div>
                                <x-input-label for="id_kasir" :value="__('Pilih Kasir')" />
                                <select name="id_kasir" class="block mt-1 w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm">
                                    <option value="semua">-- Semua Kasir --</option>
                                    @foreach($listKasir as $k)
                                        <option value="{{ $k->id_user }}" {{ request('id_kasir') == $k->id_user ? 'selected' : '' }}>{{ $k->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex space-x-2">
                                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md font-bold shadow">Tampilkan</button>
                                <a href="{{ route('admin.laporan.index') }}" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-md shadow">Reset</a>
                            </div>
                        </form>
                    </div>
                    
                    {{-- Instruksi Kecil --}}
                    <div class="text-right text-xs text-gray-400 mb-2">
                        Gunakan <span class="font-bold">↑ ↓</span> untuk memilih, <span class="font-bold">Enter</span> untuk detail.
                    </div>

                    {{-- 3. TABEL RINCIAN (INTERAKTIF) --}}
                    <div class="overflow-x-auto border rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">No. Transaksi</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Waktu</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Kasir</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Pelanggan</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Total Belanja</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Aksi</th> {{-- Kolom Aksi Baru --}}
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($laporan as $index => $transaksi)
                                    {{-- Baris Tabel dengan ID dan Class Aktif --}}
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
                                        
                                        {{-- Tombol Aksi (Edit / Detail) --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                            {{-- 
                                                PERBAIKAN: Gunakan Array ['id' => ...] 
                                                Agar hasil URL-nya menjadi: ?id=0001/KSR/1125
                                            --}}
                                            <a href="{{ route('admin.laporan.edit', ['id' => $transaksi->id_transaksi]) }}" 
                                               class="btn-edit text-indigo-600 hover:text-indigo-900 font-bold">
                                                Edit
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-10 text-center text-gray-500 italic">
                                            Tidak ada data penjualan pada periode ini.
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