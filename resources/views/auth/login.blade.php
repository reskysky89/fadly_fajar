<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Toko Fadly Fajar</title>
    
    {{-- Load Tailwind & Alpine --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    {{-- Font Google (Inter) --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="bg-gray-50">

    <div class="min-h-screen flex flex-col md:flex-row">

        {{-- BAGIAN KIRI: IMAGE & BRANDING --}}
        <div class="hidden md:flex md:w-1/2 bg-blue-900 relative items-center justify-center overflow-hidden">
            {{-- Background Image (Ganti URL ini dengan foto toko asli nanti jika mau) --}}
            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1578916171728-46686eac8d58?q=80&w=1974&auto=format&fit=crop'); opacity: 0.4;"></div>
            
            {{-- Overlay Content --}}
            <div class="relative z-10 text-white p-12 text-center">
                <div class="mb-6 flex justify-center">
                    {{-- Ikon Gudang / Toko Besar --}}
                    <div class="bg-white/20 p-4 rounded-full backdrop-blur-sm border border-white/30">
                        <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                </div>
                <h1 class="text-4xl font-bold mb-4 tracking-tight">Toko Fadly Fajar</h1>
                <p class="text-lg text-blue-100 font-light max-w-md mx-auto leading-relaxed">
                    Sistem Informasi Manajemen Toko Grosir Terintegrasi. Kelola stok, penjualan, dan pelanggan dalam satu pintu.
                </p>
            </div>
            
            {{-- Dekorasi Lingkaran --}}
            <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-blue-500 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-purple-500 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
        </div>

        {{-- BAGIAN KANAN: FORM LOGIN --}}
        <div class="flex-1 flex items-center justify-center p-6 md:p-12 bg-white">
            <div class="w-full max-w-md">
                
                {{-- Header Form --}}
                <div class="mb-10 text-center md:text-left">
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">Selamat Datang! 👋</h2>
                    <p class="text-gray-500">Silakan login terlebih dahulu yahhh...</p>
                </div>

                {{-- Session Status --}}
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    {{-- Input: Email / Username --}}
                    <div>
                        <label for="login" class="block text-sm font-semibold text-gray-700 mb-2">Email atau Username</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                            <input id="login" type="text" name="login" :value="old('login')" required autofocus
                                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none"
                                   placeholder="Masukkan email/username">
                        </div>
                        <x-input-error :messages="$errors->get('login')" class="mt-2" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        <x-input-error :messages="$errors->get('username')" class="mt-2" />
                    </div>

                    {{-- Input: Password --}}
                    <div x-data="{ show: false }">
                        <div class="flex justify-between items-center mb-2">
                            <label for="password" class="block text-sm font-semibold text-gray-700">Password</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800">Lupa Password?</a>
                            @endif
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                            <input :type="show ? 'text' : 'password'" id="password" name="password" required autocomplete="current-password"
                                   class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none"
                                   placeholder="••••••••">
                            {{-- Eye Icon --}}
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                <svg x-show="!show" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                                <svg x-show="show" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    {{-- Remember Me --}}
                    <div class="flex items-center">
                        <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="remember_me" class="ml-2 block text-sm text-gray-700">Ingat Saya</label>
                    </div>

                    {{-- Tombol Login --}}
                    <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-blue-800 hover:from-blue-700 hover:to-blue-900 text-white font-bold py-3 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        MASUK SEKARANG
                    </button>

                    {{-- Footer Link --}}
                    <div class="text-center mt-6">
                        <p class="text-sm text-gray-600">Belum punya akun? <a href="{{ route('register') }}" class="font-bold text-blue-600 hover:text-blue-800 hover:underline">Daftar disini</a></p>
                    </div>
                </form>
                
                {{-- Footer Copyright --}}
                <div class="mt-10 text-center text-xs text-gray-400">
                    &copy; {{ date('Y') }} Toko Grosir Fadly Fajar. <br>System by IT Support.
                </div>

            </div>
        </div>
    </div>

</body>
</html>