<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Toko Fadly Fajar') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900" x-data="{ mobileMenuOpen: false }">

    {{-- NAVBAR STICKY --}}
    <nav class="bg-white border-b border-gray-200 fixed w-full z-50 top-0 start-0 shadow-sm">
        <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
            
            {{-- LOGO --}}
            <a href="{{ url('/') }}" class="flex items-center space-x-3 rtl:space-x-reverse">
                <div class="bg-blue-600 text-white p-2 rounded-lg font-bold text-xl">FF</div>
                <span class="self-center text-2xl font-bold whitespace-nowrap text-blue-700">Fadly Fajar</span>
            </a>

            {{-- TOMBOL MOBILE (HAMBURGER) --}}
            <div class="flex md:order-2 items-center space-x-3">
                
                @auth
                    {{-- IKON KERANJANG (DENGAN LIVE UPDATE) --}}
                    {{-- x-data inisialisasi jumlah awal dari PHP --}}
                    <a href="{{ route('keranjang.index') }}" 
                       class="relative p-2 text-gray-600 hover:text-blue-600 transition mr-2"
                       x-data="{ count: {{ \App\Models\Keranjang::where('id_user', Auth::id())->count() }} }"
                       @cart-updated.window="count = $event.detail.count"> {{-- Dengarkan event global --}}
                        
                        {{-- Ikon --}}
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        
                        {{-- Badge Angka (Muncul jika count > 0) --}}
                        <span x-show="count > 0" 
                              x-text="count" 
                              class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/4 -translate-y-1/4 bg-red-600 rounded-full animate-bounce">
                        </span>
                    </a>
                    {{-- IKON LONCENG NOTIFIKASI (BARU) --}}
                    <div class="relative mr-4" x-data="{ openNotif: false }">
                        <button @click="openNotif = !openNotif" class="relative p-2 text-gray-600 hover:text-blue-600 transition">
                            {{-- Ikon Lonceng --}}
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            
                            {{-- Badge Merah (Jumlah Belum Dibaca) --}}
                            @php $unreadCount = Auth::user()->unreadNotifications->count(); @endphp
                            @if($unreadCount > 0)
                                <span class="absolute top-1 right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-500 rounded-full">
                                    {{ $unreadCount }}
                                </span>
                            @endif
                        </button>

                        {{-- Dropdown Isi Notifikasi --}}
                        <div x-show="openNotif" @click.away="openNotif = false" x-transition 
                             class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl py-2 z-50 border border-gray-100 overflow-hidden">
                            
                            <div class="px-4 py-2 border-b border-gray-100 font-bold text-gray-700">Notifikasi</div>

                            <div class="max-h-64 overflow-y-auto">
                                @forelse(Auth::user()->notifications as $notif)
                                    <a href="{{ route('notifikasi.baca', $notif->id) }}" class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-50 transition {{ $notif->read_at ? 'opacity-60' : 'bg-blue-50' }}">
                                        <p class="text-sm font-semibold text-gray-800">{{ $notif->data['pesan'] }}</p>
                                        <p class="text-xs text-gray-500 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                                    </a>
                                @empty
                                    <div class="px-4 py-4 text-center text-sm text-gray-500">Belum ada notifikasi baru.</div>
                                @endforelse
                            </div>
                            
                            @if(Auth::user()->notifications->count() > 0)
                                <a href="{{ route('notifikasi.bacaSemua') }}" class="block text-center py-2 text-xs font-bold text-blue-600 hover:bg-gray-50">
                                    Tandai Semua Dibaca
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- JIKA SUDAH LOGIN --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-2 text-sm font-medium text-gray-700 hover:text-blue-600 focus:outline-none">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold uppercase border border-blue-200">
                                {{ substr(Auth::user()->nama, 0, 1) }}
                            </div>
                            <span class="hidden md:block">{{ Auth::user()->nama }}</span>
                            <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        {{-- Dropdown Menu --}}
                        <div x-show="open" @click.away="open = false" x-transition 
                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50 border border-gray-100">
                            
                            @if(Auth::user()->role_user == 'admin')
                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Dashboard Admin</a>
                            @endif

                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Profil Saya
                            </a>
                            <a href="{{ route('pelanggan.riwayat') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Riwayat Pesanan</a>
                            
                            <div class="border-t border-gray-100 my-1"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-bold">
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>

                @else
                    {{-- JIKA BELUM LOGIN (TAMU) --}}
                    <a href="{{ route('login') }}" class="text-gray-800 hover:text-blue-600 font-semibold text-sm px-4 py-2 transition">Masuk</a>
                    <a href="{{ route('register') }}" class="text-white bg-blue-600 hover:bg-blue-700 font-bold rounded-full text-sm px-5 py-2.5 transition shadow-md transform hover:scale-105">Daftar</a>
                @endauth

                {{-- Tombol Hamburger (Mobile) --}}
                <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 ml-2">
                    <span class="sr-only">Open main menu</span>
                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h15M1 7h15M1 13h15"/></svg>
                </button>
            </div>

            {{-- MENU TENGAH (SEARCH BAR) --}}
            <div class="items-center justify-between hidden w-full md:flex md:w-auto md:order-1" id="navbar-sticky">
                <form action="{{ url('/') }}" method="GET" class="relative mt-3 md:mt-0 md:block w-full md:w-96">
                    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" class="block w-full p-2 ps-10 text-sm text-gray-900 border border-gray-300 rounded-full bg-gray-50 focus:ring-blue-500 focus:border-blue-500" placeholder="Cari beras, minyak, gula...">
                </form>
            </div>
        </div>

        {{-- MOBILE MENU DROPDOWN --}}
        <div x-show="mobileMenuOpen" class="md:hidden bg-white border-t p-4" x-transition>
            <form action="{{ url('/') }}" method="GET" class="relative mb-4">
                <input type="text" name="search" value="{{ request('search') }}" class="block w-full p-2 text-sm border border-gray-300 rounded-lg bg-gray-50" placeholder="Cari produk...">
            </form>
            <ul class="flex flex-col font-medium space-y-2">
                <li><a href="{{ url('/') }}" class="block py-2 px-3 text-white bg-blue-700 rounded" aria-current="page">Beranda</a></li>
                <li><a href="#" class="block py-2 px-3 text-gray-900 rounded hover:bg-gray-100">Kategori</a></li>
                <li><a href="#" class="block py-2 px-3 text-gray-900 rounded hover:bg-gray-100">Cara Belanja</a></li>
            </ul>
        </div>
    </nav>

    {{-- KONTEN UTAMA --}}
    <main class="pt-20 pb-10 min-h-screen">
        {{ $slot }}
    </main>

    {{-- FOOTER --}}
    <footer class="bg-white rounded-lg shadow m-4 dark:bg-gray-800">
        <div class="w-full mx-auto max-w-screen-xl p-4 md:flex md:items-center md:justify-between">
          <span class="text-sm text-gray-500 sm:text-center dark:text-gray-400">© {{ date('Y') }} <a href="/" class="hover:underline">Toko Fadly Fajar™</a>. All Rights Reserved.
        </span>
        <ul class="flex flex-wrap items-center mt-3 text-sm font-medium text-gray-500 dark:text-gray-400 sm:mt-0">
            <li><a href="#" class="hover:underline me-4 md:me-6">Tentang Kami</a></li>
            <li><a href="#" class="hover:underline">Kontak</a></li>
        </ul>
        </div>
    </footer>

</body>
</html> 