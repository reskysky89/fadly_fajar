{{-- resources/views/layouts/sidebar.blade.php --}}

{{-- 
    Perubahan: 
    - Mengganti <x-nav-link> dengan tag <a> biasa.
    - Menambahkan 'block w-full' untuk memastikan setiap link 
      mengambil satu baris penuh (tampil ke bawah).
    - Menambahkan logika 'active' secara manual.
--}}
<div class="w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex-shrink-0 h-full flex flex-col">
    <div class="flex items-center justify-center h-16 border-b dark:border-gray-700 flex-shrink-0">
        <span class="text-xl font-semibold text-gray-800 dark:text-gray-200">Fadly Fajar</span>
    </div>

    <nav class="mt-4 flex-1 overflow-y-auto">

        {{-- Logika untuk menentukan kelas 'active' --}}
        @php
            $baseClass     = 'block w-full px-6 py-3'; // 'block' memaksa tampil ke bawah
            $activeClass   = 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100 border-l-4 border-indigo-500 font-semibold';
            $inactiveClass = 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700';
        @endphp

        {{-- ================================================= --}}
        {{-- MENU UNTUK ADMIN --}}
        {{-- ================================================= --}}
        @if (Auth::user()->role_user == 'admin')
            
            <a href="{{ route('admin.dashboard') }}" 
               class="{{ $baseClass }} {{ request()->routeIs('admin.dashboard') ? $activeClass : $inactiveClass }}">
                Dashboard
            </a>
            <a href="{{ route('admin.produk.index') }}" 
               class="{{ $baseClass }} {{ request()->routeIs('admin.produk.*') ? $activeClass : $inactiveClass }}">
                Manajemen Produk
            </a>
            <a href="{{ route('admin.stok.index') }}" 
                class="{{ $baseClass }} {{ request()->routeIs('admin.stok.*') ? $activeClass : $inactiveClass }}">
                Manajemen Stok
            </a>
            <a href="{{ route('admin.kasir.index') }}" 
               class="{{ $baseClass }} {{ request()->routeIs('admin.kasir.*') ? $activeClass : $inactiveClass }}">
                Manajemen Kasir
            </a>
            <a href="{{ route('admin.laporan.index') }}" class="{{ $baseClass }} {{ request()->routeIs('admin.laporan.*') ? $activeClass : $inactiveClass }}">
                Laporan Penjualan
            </a>
            <a href="#" class="{{ $baseClass }} {{ $inactiveClass }}">
                Pesanan Online
            </a>
            <a href="#" class="{{ $baseClass }} {{ $inactiveClass }}">
                Pengaturan
            </a>
        
        {{-- ================================================= --}}
        {{-- MENU UNTUK KASIR --}}
        {{-- ================================================= --}}
        @elseif (Auth::user()->role_user == 'kasir')
            
            {{-- 1. Penjualan Kasir (Ganti nama dari Dashboard) --}}
            {{-- Ini mengarah ke halaman POS (Transaksi) --}}
            <a href="{{ route('kasir.transaksi.index') }}" 
               class="{{ $baseClass }} {{ request()->routeIs('kasir.transaksi.*') || request()->routeIs('kasir.dashboard') ? $activeClass : $inactiveClass }}">
                <div class="flex items-center">
                    {{-- Ikon Kasir/Mesin (Opsional, agar cantik) --}}
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    Penjualan Kasir
                </div>
            </a>

            {{-- 2. Pesanan Online (Placeholder) --}}
            <a href="#" class="{{ $baseClass }} {{ $inactiveClass }}">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Pesanan Online
                </div>
            </a>

            {{-- 3. Riwayat Penjualan (Placeholder) --}}
            <a href="{{ route('kasir.riwayat.index') }}" 
               class="{{ $baseClass }} {{ request()->routeIs('kasir.riwayat.*') ? $activeClass : $inactiveClass }}">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3" ...>...</svg>
                    Riwayat Penjualan
                </div>
            </a>
        @endif
    </nav>

    {{-- ================================================= --}}
    {{-- MENU LOGOUT (Diletakkan di bagian bawah) --}}
    {{-- ================================================= --}}
    <div class="flex-shrink-0 border-t dark:border-gray-700">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <a href="{{ route('logout') }}"
                    onclick="event.preventDefault(); this.closest('form').submit();"
                    class="flex items-center w-full px-6 py-4 text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                Logout
            </a>
        </form>
    </div>
</div>