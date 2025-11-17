<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manajemen Stok') }}
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
                    
                    {{-- Bagian Atas: Filter dan Tombol "Input Stok Masuk" --}}
                    <div class="flex justify-between items-center mb-6">
                        <form action="{{ route('admin.stok.index') }}" method="GET" class="flex items-center space-x-2 flex-wrap">
                            
                            {{-- Input Pencarian (BARU) --}}
                            <div class="relative">
                                <input type="text" name="search" 
                                       value="{{ request('search') }}" 
                                       placeholder="Cari Supplier / Ket..."
                                       class="form-input rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 w-48">
                            </div>

                            {{-- Input Tanggal --}}
                            <input type="date" name="tanggal_mulai" 
                                   value="{{ request('tanggal_mulai') }}" 
                                   class="form-input rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700"
                                   title="Tanggal Mulai">
                                   
                            <span class="text-gray-500">-</span>

                            <input type="date" name="tanggal_akhir" 
                                   value="{{ request('tanggal_akhir') }}" 
                                   class="form-input rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700"
                                   title="Tanggal Akhir">
                            
                            <x-primary-button>Filter</x-primary-button>
                            
                            <a href="{{ route('admin.stok.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-200 dark:bg-gray-600 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500">
                                Reset
                            </a>
                        </form>

                        {{-- Tombol Input Stok --}}
                        <a href="{{ route('admin.stok.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-200 active:bg-blue-600 disabled:opacity-25 transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Input Stok Masuk
                        </a>
                    </div>

                    {{-- Tabel Riwayat (Sesuai Desain Anda) --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left">No. Transaksi</th>
                                    <th class="px-6 py-3 text-left">Tanggal</th>
                                    <th class="px-6 py-3 text-left">Waktu</th>
                                    <th class="px-6 py-3 text-left">Supplier</th>
                                    <th class="px-6 py-3 text-left">Total Nilai</th>
                                    <th class="px-6 py-3 text-left">Keterangan</th>
                                    <th class="px-6 py-3 text-left">User</th>
                                    <th class="px-6 py-3 text-left">Diubah</th>
                                    <th class="px-6 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($riwayatStok as $batch)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">STOK-{{ str_pad($batch->id_batch_stok, 4, '0', STR_PAD_LEFT) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ \Carbon\Carbon::parse($batch->tanggal_masuk)->format('d/m/Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $batch->created_at->format('H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $batch->supplier->nama_supplier ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">Rp {{ number_format($batch->total_nilai_faktur, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $batch->keterangan }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $batch->user->nama ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $batch->userDiubah->nama ?? '' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                            <a href="{{ route('admin.stok.edit', $batch->id_batch_stok) }}" class="text-indigo-600 hover:text-indigo-900">
                                                Edit
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">
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