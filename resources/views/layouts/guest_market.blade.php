<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Toko Fadly Fajar') }}</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900" x-data="{ mobileMenuOpen: false, caraBelanjaOpen: false }">

    {{-- NAVBAR --}}
    <nav class="bg-white border-b border-gray-200 fixed w-full z-50 top-0 start-0 shadow-sm">
        <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
            
            {{-- KIRI --}}
            <div class="flex items-center gap-8">
                <a href="{{ url('/') }}" class="flex items-center space-x-2">
                    <div class="bg-blue-600 text-white p-2 rounded-lg font-bold text-xl">FF</div>
                    <span class="self-center text-2xl font-bold whitespace-nowrap text-blue-700 hidden sm:block">Fadly Fajar</span>
                </a>
                <div class="hidden md:flex space-x-6">
                    <a href="{{ url('/') }}" class="text-sm font-bold text-gray-900 hover:text-blue-600 transition">Beranda</a>
                    <a href="{{ url('/#katalog') }}" class="text-sm font-medium text-gray-500 hover:text-blue-600 transition">Kategori</a>
                    <button @click="caraBelanjaOpen = true" class="text-sm font-medium text-gray-500 hover:text-blue-600 transition focus:outline-none">Cara Belanja</button>
                </div>
            </div>

            {{-- KANAN --}}
            <div class="flex items-center space-x-3 md:order-2 ml-auto md:ml-0">
                @auth
                    <a href="{{ route('keranjang.index') }}" class="relative p-2 text-gray-600 hover:text-blue-600 transition mr-1 md:mr-2"
                       x-data="{ count: {{ \App\Models\Keranjang::where('id_user', Auth::id())->count() }} }"
                       @cart-updated.window="count = $event.detail.count">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <span x-show="count > 0" x-text="count" class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full animate-bounce"></span>
                    </a>

                    <div class="relative mr-1 md:mr-2" x-data="{ openNotif: false }">
                        <button @click="openNotif = !openNotif" class="relative p-2 text-gray-600 hover:text-blue-600 transition">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            @php $unreadCount = Auth::user()->unreadNotifications->count(); @endphp
                            @if($unreadCount > 0) <span class="absolute top-1 right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-500 rounded-full animate-pulse">{{ $unreadCount }}</span> @endif
                        </button>
                        <div x-show="openNotif" @click.away="openNotif = false" x-transition class="absolute right-[-50px] md:right-0 mt-2 w-72 md:w-80 max-w-[90vw] bg-white rounded-lg shadow-xl py-2 z-50 border border-gray-100 overflow-hidden origin-top-right" style="display: none;">
                            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50 flex justify-between items-center"><span class="font-bold text-gray-700 text-sm">Notifikasi</span> @if(Auth::user()->notifications->count() > 0) <a href="{{ route('notifikasi.bacaSemua') }}" class="text-xs text-blue-600 hover:underline">Baca Semua</a> @endif</div>
                            <div class="max-h-64 overflow-y-auto custom-scrollbar">
                                @forelse(Auth::user()->notifications as $notif)
                                    <a href="{{ route('notifikasi.baca', $notif->id) }}" class="block px-4 py-3 hover:bg-blue-50 border-b border-gray-50 transition group">
                                        <div class="flex items-start gap-3">
                                            @if(!$notif->read_at) <span class="mt-1.5 w-2 h-2 bg-blue-500 rounded-full flex-shrink-0"></span> @endif
                                            <div><p class="text-sm font-semibold text-gray-800 group-hover:text-blue-700 transition {{ $notif->read_at ? 'font-normal text-gray-600' : '' }}">{{ $notif->data['pesan'] }}</p><p class="text-xs text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p></div>
                                        </div>
                                    </a>
                                @empty <div class="px-4 py-8 text-center"><p class="text-sm text-gray-500">Belum ada notifikasi</p></div> @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- USER DROPDOWN (UPDATE FOTO PROFIL) --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-2 text-sm font-medium text-gray-700 hover:text-blue-600 focus:outline-none">
                            
                            {{-- FOTO PROFIL --}}
                            <div class="w-8 h-8 rounded-full overflow-hidden border border-blue-200 bg-gray-100 flex-shrink-0">
                                @if(Auth::user()->foto_profil)
                                    <img src="{{ asset(Auth::user()->foto_profil) }}" alt="Avatar" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold uppercase text-xs">
                                        {{ substr(Auth::user()->nama, 0, 1) }}
                                    </div>
                                @endif
                            </div>

                            <span class="hidden md:block truncate max-w-[100px]">{{ Auth::user()->nama }}</span>
                            <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50 border border-gray-100" style="display: none;">
                            @if(Auth::user()->role_user == 'admin') <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 font-bold text-blue-600">Dashboard Admin</a> @endif
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profil Saya</a>
                            <a href="{{ route('pelanggan.riwayat') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Riwayat Pesanan</a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-bold">Keluar</button></form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-gray-700 hover:text-blue-600 font-semibold text-sm px-3 py-2">Masuk</a>
                    <a href="{{ route('register') }}" class="text-white bg-blue-600 hover:bg-blue-700 font-bold rounded-full text-sm px-4 py-2 shadow-md">Daftar</a>
                @endauth
                <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="inline-flex items-center p-2 w-10 h-10 justify-center text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:ring-2 focus:ring-gray-200 ml-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
            </div>

            {{-- SEARCH TENGAH --}}
            <div class="items-center justify-between hidden w-full md:flex md:w-auto md:order-1 flex-grow md:mx-10">
                <form action="{{ url('/') }}" method="GET" class="relative w-full max-w-xl mx-auto">
                    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none"><svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg></div>
                    <input type="text" name="search" value="{{ request('search') }}" class="block w-full p-3 ps-10 text-sm text-gray-900 border border-gray-300 rounded-full bg-gray-50 focus:ring-blue-500 focus:border-blue-500 shadow-sm" placeholder="Cari produk...">
                </form>
            </div>
        </div>

        <div x-show="mobileMenuOpen" class="md:hidden bg-white border-t p-4 shadow-lg" x-transition x-cloak style="display: none;">
            <form action="{{ url('/') }}" method="GET" class="relative mb-4">
                <input type="text" name="search" class="block w-full p-2 text-sm border border-gray-300 rounded-lg" placeholder="Cari produk...">
            </form>
            <ul class="flex flex-col space-y-2">
                <li><a href="{{ url('/') }}" class="block py-2 px-3 text-blue-700 font-bold bg-blue-50 rounded">Beranda</a></li>
                <li><a href="{{ url('/#katalog') }}" class="block py-2 px-3 text-gray-700 hover:bg-gray-100 rounded">Kategori</a></li>
                <li><button @click="caraBelanjaOpen = true; mobileMenuOpen = false" class="block w-full text-left py-2 px-3 text-gray-700 hover:bg-gray-100 rounded">Cara Belanja</button></li>
            </ul>
        </div>
    </nav>

    {{-- MODAL CARA BELANJA --}}
    <div x-show="caraBelanjaOpen" class="fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm" x-transition x-cloak style="display: none;"> 
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl mx-4 overflow-hidden transform transition-all" @click.away="caraBelanjaOpen = false">
            <div class="bg-blue-600 p-6 text-white text-center relative">
                <h2 class="text-2xl font-extrabold">Panduan Cara Belanja</h2>
                <button @click="caraBelanjaOpen = false" class="absolute top-4 right-4 text-white hover:text-gray-200 focus:outline-none"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
            <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="flex gap-4"><div class="w-12 h-12 flex-shrink-0 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-xl">1</div><div><h3 class="font-bold text-gray-800 text-lg">Cari & Pilih</h3><p class="text-sm text-gray-500 mt-1">Gunakan pencarian atau kategori.</p></div></div>
                <div class="flex gap-4"><div class="w-12 h-12 flex-shrink-0 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-xl">2</div><div><h3 class="font-bold text-gray-800 text-lg">Masuk Keranjang</h3><p class="text-sm text-gray-500 mt-1">Klik tombol Beli.</p></div></div>
                <div class="flex gap-4"><div class="w-12 h-12 flex-shrink-0 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-xl">3</div><div><h3 class="font-bold text-gray-800 text-lg">Checkout</h3><p class="text-sm text-gray-500 mt-1">Isi alamat & pilih metode bayar.</p></div></div>
                <div class="flex gap-4"><div class="w-12 h-12 flex-shrink-0 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-xl">4</div><div><h3 class="font-bold text-gray-800 text-lg">Selesai</h3><p class="text-sm text-gray-500 mt-1">Tunggu konfirmasi Admin.</p></div></div>
            </div>
            <div class="p-4 bg-gray-50 border-t text-center"><button @click="caraBelanjaOpen = false" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow transition">Mengerti</button></div>
        </div>
    </div>

    <main class="pt-24 pb-10 min-h-screen px-4 md:px-8">
        {{ $slot }}
    </main>

    <footer class="bg-white border-t mt-10 py-8">
        <div class="max-w-screen-xl mx-auto px-4 text-center text-gray-500 text-sm">
            &copy; {{ date('Y') }} <strong>Toko Fadly Fajar</strong>. Melayani dengan Hati.
        </div>
    </footer>
</body>
</html>