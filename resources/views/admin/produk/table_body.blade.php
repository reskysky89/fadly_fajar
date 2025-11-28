{{-- Inisialisasi Counter Baris Global --}}
@php $rowIndex = 0; @endphp

@forelse ($produks as $produk)
    @php
        // Hitung Stok Dasar
        $stok_dasar = $produk->stok_saat_ini ?? 0;
    @endphp

    {{-- ==================================================== --}}
    {{-- BARIS 1: UTAMA (SATUAN DASAR) --}}
    {{-- ==================================================== --}}
    <tr id="product-row-{{ $rowIndex }}" 
        class="transition-colors cursor-pointer border-b border-gray-100 dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-gray-700"
        :class="activeRow === {{ $rowIndex }} ? 'bg-blue-100 dark:bg-blue-900' : ''"
        @click="activeRow = {{ $rowIndex }}">
        
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ $produk->id_produk }}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">{{ $produk->nama_produk }}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $produk->kategori->nama_kategori ?? 'N/A' }}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
            {{ $produk->supplier->nama_supplier ?? '-' }}
        </td>
        
        {{-- Satuan --}}
        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-blue-600">
            {{ $produk->satuanDasar->nama_satuan ?? 'PCS' }}
        </td>

        {{-- Stok --}}
        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold {{ $stok_dasar <= 0 ? 'text-red-500' : 'text-green-600' }}">
            {{ number_format($stok_dasar, 0, ',', '.') }}
        </td>
        
        <td class="px-6 py-4 whitespace-nowrap text-sm">Rp {{ number_format($produk->harga_pokok_dasar, 0, ',', '.') }}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm">Rp {{ number_format($produk->harga_jual_dasar, 0, ',', '.') }}</td>
        
        {{-- Status --}}
        <td class="px-6 py-4 whitespace-nowrap text-sm">
            <a href="{{ route('admin.produk.toggleStatus', $produk->id_produk) }}">
                @if($produk->status_produk == 'aktif')
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                @else
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Nonaktif</span>
                @endif
            </a>
        </td>

        {{-- Aksi --}}
        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
            <a href="{{ route('admin.produk.edit', $produk->id_produk) }}" class="text-indigo-600 hover:text-indigo-900 font-bold mr-2">Edit</a>
        </td>
    </tr>
    
    {{-- Increment Counter --}}
    @php $rowIndex++; @endphp


    {{-- ==================================================== --}}
    {{-- BARIS 2..N: KONVERSI (DUS, BAL, DLL) --}}
    {{-- ==================================================== --}}
    @foreach ($produk->produkKonversis as $konv)
        @php
            $stok_konversi = ($konv->nilai_konversi > 0) ? ($stok_dasar / $konv->nilai_konversi) : 0;
        @endphp
        
        <tr id="product-row-{{ $rowIndex }}" 
            class="transition-colors cursor-pointer border-b border-gray-100 dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-gray-700"
            :class="activeRow === {{ $rowIndex }} ? 'bg-blue-100 dark:bg-blue-900' : ''"
            @click="activeRow = {{ $rowIndex }}">
            
            <td class="px-6 py-4"></td> {{-- Kode Item Kosong --}}
            
            {{-- 
                PERBAIKAN DI SINI:
                - Menghapus class 'pl-10' (padding left)
                - Menghapus ikon panah '↳'
                - Sekarang teksnya rata kiri sejajar dengan induknya.
            --}}
            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
                 {{ $produk->nama_produk }}
            </td>
            
            <td class="px-6 py-4"></td> {{-- Kategori Kosong --}}
            <td class="px-6 py-4"></td> {{-- Supplier Kosong (Tambahan Baru) --}}
            
            {{-- Satuan Konversi --}}
            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-purple-600">
                {{ $konv->satuan->nama_satuan ?? 'N/A' }}
            </td>

            {{-- Stok Konversi --}}
            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-700 dark:text-gray-300">
                {{ number_format($stok_konversi, 2, ',', '.') }}
            </td>

            <td class="px-6 py-4 whitespace-nowrap text-sm">Rp {{ number_format($konv->harga_pokok_konversi, 0, ',', '.') }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">Rp {{ number_format($konv->harga_jual_konversi, 0, ',', '.') }}</td>
            
            <td class="px-6 py-4"></td> {{-- Status Kosong --}}
            
            
        </tr>
        
        {{-- Increment Counter --}}
        @php $rowIndex++; @endphp
    @endforeach

@empty
    <tr>
        <td colspan="9" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
            Belum ada data produk.
        </td>
    </tr>
@endforelse