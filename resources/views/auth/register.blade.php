<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Akun - Toko Fadly Fajar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 antialiased flex items-center justify-center min-h-screen py-10 px-4">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden">
        
        {{-- Header Biru --}}
        <div class="bg-blue-700 p-8 text-center">
            <h2 class="text-3xl font-bold text-white">Buat Akun Baru</h2>
            <p class="text-blue-100 mt-2 text-sm">Bergabunglah untuk belanja grosir lebih mudah</p>
        </div>

        <div class="p-8">
            <form method="POST" action="{{ route('register') }}">
                @csrf

                {{-- Nama --}}
                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="nama" value="{{ old('nama') }}" required autofocus
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                           placeholder="Contoh: Budi Santoso">
                    @error('nama') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                {{-- Username (Auto Generate atau Input) - Kita pakai Input saja biar bebas --}}
                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Username</label>
                    <input type="text" name="username" value="{{ old('username') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                           placeholder="Username unik (tanpa spasi)">
                    @error('username') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                {{-- Email --}}
                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Alamat Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                           placeholder="email@contoh.com">
                    @error('email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                {{-- No HP (Opsional/Wajib) --}}
                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-1">No. WhatsApp / HP</label>
                    <input type="text" name="kontak" value="{{ old('kontak') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                           placeholder="08123xxxx">
                    @error('kontak') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                {{-- Password --}}
                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                           placeholder="Minimal 8 karakter">
                    @error('password') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                {{-- Konfirmasi Password --}}
                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Ulangi Password</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                           placeholder="Ketik ulang password">
                </div>

                {{-- Tombol Submit --}}
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg shadow-lg hover:shadow-xl transition duration-300 transform hover:-translate-y-1">
                    Daftar Sekarang
                </button>

                {{-- Link Login --}}
                <div class="mt-6 text-center text-sm text-gray-600">
                    Sudah punya akun? 
                    <a href="{{ route('login') }}" class="text-blue-600 font-bold hover:underline">Masuk disini</a>
                </div>

                <div class="mt-4 text-center text-xs text-gray-400">
                    <a href="/" class="hover:text-gray-600">← Kembali ke Beranda</a>
                </div>

            </form>
        </div>
    </div>

</body>
</html>