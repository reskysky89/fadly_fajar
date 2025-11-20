@forelse ($riwayat as $index => $transaksi)
    <tr id="row-{{ $index }}"
        class="transition-colors cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700"
        @click="activeRow = {{ $index }}"
        :class="activeRow === {{ $index }} ? 'bg-blue-100 dark:bg-blue-900' : ''">
        
        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-blue-600 font-mono">
            {{ $transaksi->id_transaksi }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            <div class="font-bold text-gray-700 dark:text-gray-300">{{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d/m/Y') }}</div>
            <div class="text-xs">{{ \Carbon\Carbon::parse($transaksi->waktu_transaksi)->format('H:i') }}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
            {{ $transaksi->nama_pelanggan ?? 'UMUM' }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-right font-mono text-gray-900 dark:text-gray-100">
            Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 pl-10">
            {{ $transaksi->kasir->nama ?? $transaksi->nama_kasir }}
        </td>
        
        {{-- INI TOMBOLNYA (KOLOM TERAKHIR) --}}
        <td class="px-6 py-4 whitespace-nowrap text-center">
            {{-- Tombol Lihat Detail (Memanggil fungsi bukaDetail di riwayat.blade.php) --}}
            <button @click.stop="bukaDetail('{{ $transaksi->id_transaksi }}')" 
                    class="px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition font-bold text-xs">
                Lihat / Cetak
            </button>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="px-6 py-20 text-center text-gray-500 italic">
            <div class="flex flex-col items-center justify-center">
                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                <p>Belum ada data penjualan.</p>
            </div>
        </td>
    </tr>
@endforelse