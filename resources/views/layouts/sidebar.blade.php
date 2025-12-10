<div class="w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex-shrink-0 h-full flex flex-col">
    
    {{-- HEADER SIDEBAR --}}
    <div class="flex items-center justify-center h-16 border-b dark:border-gray-700 flex-shrink-0">
        <div class="flex items-center gap-2">
            {{-- Logo Kecil --}}
            <div class="bg-blue-600 text-white p-1.5 rounded-lg font-bold text-lg">FF</div>
            <span class="text-xl font-bold text-gray-800 dark:text-gray-200">Fadly Fajar</span>
        </div>
    </div>

    <nav class="mt-4 flex-1 overflow-y-auto custom-scrollbar">

        {{-- DEFINISI STYLE BIAR RAPI --}}
        @php
            $baseClass     = 'group flex items-center w-full px-6 py-3 text-sm font-medium transition-all duration-200';
            // Active: Ada border kiri warna biru, background abu terang
            $activeClass   = 'bg-blue-50 dark:bg-gray-700 text-blue-700 dark:text-blue-400 border-r-4 border-blue-600';
            // Inactive: Teks abu, hover jadi gelap
            $inactiveClass = 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900';
            // Icon Class
            $iconClass     = 'w-5 h-5 mr-3 flex-shrink-0'; 
        @endphp

        {{-- ================================================= --}}
        {{-- MENU KHUSUS ADMIN --}}
        {{-- ================================================= --}}
        @if (Auth::user()->role_user == 'admin')
            
            {{-- 1. DASHBOARD --}}
            <a href="{{ route('admin.dashboard') }}" 
               class="{{ $baseClass }} {{ request()->routeIs('admin.dashboard') ? $activeClass : $inactiveClass }}">
                <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                Dashboard
            </a>

            <div class="px-6 py-2 mt-2 text-xs font-bold text-gray-400 uppercase tracking-wider">Master Data</div>

            {{-- 2. MANAJEMEN PRODUK --}}
            <a href="{{ route('admin.produk.index') }}" 
               class="{{ $baseClass }} {{ request()->routeIs('admin.produk.*') ? $activeClass : $inactiveClass }}">
                <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                Manajemen Produk
            </a>

            {{-- 3. MANAJEMEN STOK --}}
            <a href="{{ route('admin.stok.index') }}" 
               class="{{ $baseClass }} {{ request()->routeIs('admin.stok.*') ? $activeClass : $inactiveClass }}">
                <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                Manajemen Stok
            </a>

            {{-- 4. MANAJEMEN KASIR --}}
            <a href="{{ route('admin.kasir.index') }}" 
               class="{{ $baseClass }} {{ request()->routeIs('admin.kasir.*') ? $activeClass : $inactiveClass }}">
                <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Manajemen Kasir
            </a>

            <div class="px-6 py-2 mt-2 text-xs font-bold text-gray-400 uppercase tracking-wider">Transaksi</div>

            {{-- 5. PENJUALAN (POS) --}}
            <a href="{{ route('kasir.transaksi.index') }}" 
               class="{{ $baseClass }} {{ request()->routeIs('kasir.transaksi.*') ? $activeClass : $inactiveClass }}">
                <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 3m5.25-3l.75 3 1 3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                Penjualan (POS)
            </a>

            {{-- 6. LAPORAN PENJUALAN --}}
            <a href="{{ route('admin.laporan.index') }}" 
               class="{{ $baseClass }} {{ request()->routeIs('admin.laporan.*') ? $activeClass : $inactiveClass }}">
                <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Laporan Penjualan
            </a>

            {{-- 7. PESANAN ONLINE --}}
            <a href="{{ route('pesanan.index') }}" 
               class="{{ $baseClass }} {{ request()->routeIs('pesanan.*') ? $activeClass : $inactiveClass }}"
               x-data="{ count: {{ \App\Models\Transaksi::where('jenis_transaksi', 'online')->where('status_pesanan', 'diproses')->count() }} }"
               x-init="setInterval(async () => { 
                   try { 
                       const res = await fetch('{{ route('api.cekPesananBaru') }}'); 
                       const data = await res.json(); 
                       count = data.count; 
                   } catch(e) {} 
               }, 5000)"> 
                
                <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                <span class="flex-1">Pesanan Online</span>
                
                <span x-show="count > 0" 
                      x-text="count" 
                      class="ml-auto bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm animate-pulse">
                </span>
            </a>

            <div class="px-6 py-2 mt-2 text-xs font-bold text-gray-400 uppercase tracking-wider">Lainnya</div>

            {{-- 8. PENGATURAN --}}
            <a href="{{ route('profile.edit') }}" 
               class="{{ $baseClass }} {{ request()->routeIs('profile.edit') ? $activeClass : $inactiveClass }}">
                <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                Pengaturan
            </a>

        {{-- ================================================= --}}
        {{-- MENU UNTUK KASIR --}}
        {{-- ================================================= --}}
        @elseif (Auth::user()->role_user == 'kasir')
            
            <a href="{{ route('kasir.transaksi.index') }}" 
               class="{{ $baseClass }} {{ request()->routeIs('kasir.transaksi.*') ? $activeClass : $inactiveClass }}">
               <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 3m5.25-3l.75 3 1 3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
               Penjualan Kasir
            </a>

            <a href="{{ route('pesanan.index') }}" 
               class="{{ $baseClass }} {{ request()->routeIs('pesanan.*') ? $activeClass : $inactiveClass }}"
               x-data="{ count: {{ \App\Models\Transaksi::where('jenis_transaksi', 'online')->where('status_pesanan', 'diproses')->count() }} }"
               x-init="setInterval(async () => { 
                   try { 
                       const res = await fetch('{{ route('api.cekPesananBaru') }}'); 
                       const data = await res.json(); 
                       count = data.count; 
                   } catch(e) {} 
               }, 5000)">
               
               <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
               <span class="flex-1">Pesanan Online</span>
               
               <span x-show="count > 0" 
                     x-text="count" 
                     class="ml-auto bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm animate-pulse">
               </span>
            </a>

            <a href="{{ route('kasir.riwayat.index') }}" 
               class="{{ $baseClass }} {{ request()->routeIs('kasir.riwayat.*') ? $activeClass : $inactiveClass }}">
               <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
               Riwayat Penjualan
            </a>
        @endif

    </nav>

    {{-- LOGOUT --}}
    <div class="flex-shrink-0 border-t border-gray-200 dark:border-gray-700 p-4">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <a href="{{ route('logout') }}"
               onclick="event.preventDefault(); this.closest('form').submit();"
               class="flex items-center w-full px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg transition font-bold text-sm">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                Logout
            </a>
        </form>
    </div>

</div>