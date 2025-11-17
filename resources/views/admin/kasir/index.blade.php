<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manajemen Kasir') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    {{-- Pesan Sukses --}}
                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-md">
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- Header: Pencarian & Tombol Tambah --}}
                    <div class="flex justify-between items-center mb-6">
                        {{-- Search Bar --}}
                        <div class="w-1/3">
                            <form action="{{ route('admin.kasir.index') }}" method="GET" class="relative">
                                <x-text-input type="text" name="search" placeholder="Cari kasir..." class="w-full pl-10" :value="request('search')" />
                                <span class="absolute left-3 top-2.5 text-gray-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </span>
                            </form>
                        </div>

                        {{-- Tombol Tambah --}}
                        <a href="{{ route('admin.kasir.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 focus:bg-blue-600 active:bg-blue-800 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Tambah Akun Kasir
                        </a>
                    </div>

                    {{-- Tabel Daftar Kasir --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avatar</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Tlpn</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Bergabung</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($kasirs as $kasir)
                                    <tr>
                                        {{-- Avatar (Inisial atau Foto) --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($kasir->foto_profil)
                                                <img src="{{ asset('storage/' . $kasir->foto_profil) }}" alt="" class="h-10 w-10 rounded-full object-cover">
                                            @else
                                                <img src="https://ui-avatars.com/api/?name={{ urlencode($kasir->nama) }}&background=random&color=fff" alt="" class="h-10 w-10 rounded-full">
                                            @endif
                                        </td>
                                        
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $kasir->nama }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $kasir->email }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $kasir->kontak ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $kasir->created_at->format('d M Y') }}</td>
                                        
                                        {{-- Toggle Status (Hijau/Abu) --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <form action="{{ route('admin.kasir.toggleStatus', $kasir->id_user) }}" method="POST" class="inline-block">
                                                @csrf
                                                @method('PATCH')
                                                
                                                {{-- 
                                                    PERUBAHAN UKURAN:
                                                    1. h-8 w-16 (Memperbesar wadah)
                                                    2. h-6 w-6 (Memperbesar bulatan putih)
                                                    3. translate-x-9 (Menggeser bulatan lebih jauh ke kanan saat aktif)
                                                --}}
                                                <button type="submit" 
                                                    class="relative inline-flex h-4 w-16 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ $kasir->status_akun === 'aktif' ? 'bg-green-500' : 'bg-gray-600' }}" 
                                                    title="Klik untuk mengubah status">
                                                    
                                                    <span class="sr-only">Ubah Status</span>
                                                    
                                                    {{-- Knob Putih --}}
                                                    <span class="inline-block h-6 w-6 transform rounded-full bg-white transition-transform {{ $kasir->status_akun === 'aktif' ? 'translate-x-9' : 'translate-x-1' }}"></span>
                                                </button>
                                            </form>
                                        </td>

                                        {{-- Tombol Aksi --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('admin.kasir.edit', $kasir->id_user) }}" class="text-blue-600 hover:text-blue-900 bg-blue-100 hover:bg-blue-200 px-3 py-1 rounded-md transition mr-2">Edit</a>
                                            
                                            {{-- Tombol Hapus (Opsional, jika ingin permanen) --}}
                                            {{-- <form action="{{ route('admin.kasir.destroy', $kasir->id_user) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus kasir ini permanen?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 bg-red-100 hover:bg-red-200 px-3 py-1 rounded-md transition">Hapus</button>
                                            </form> --}}
                                            
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                            Belum ada data kasir.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $kasirs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>