@component('layouts.guest_market')

    <div class="max-w-screen-xl mx-auto px-4 py-10">
        
        {{-- TOMBOL KEMBALI --}}
        <div class="mb-6">
            <a href="{{ route('home') }}" class="inline-flex items-center text-gray-500 hover:text-blue-600 font-bold transition duration-200 group text-sm">
                <div class="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center mr-2 shadow-sm group-hover:bg-blue-50 group-hover:border-blue-200 group-hover:shadow transition-all">
                    <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </div>
                Kembali ke Beranda
            </a>
        </div>

        <h1 class="text-3xl font-extrabold text-gray-900 mb-8 border-b border-gray-200 pb-4">
            Profil Saya
        </h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- KOLOM KIRI: KARTU MEMBER & STATISTIK --}}
            <div class="lg:col-span-1 space-y-6">
                
                {{-- 1. Kartu Identitas --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm text-center relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-r from-blue-600 to-purple-600"></div>
                    
                    {{-- FOTO PROFIL --}}
                    <div class="relative mx-auto w-28 h-28 bg-white rounded-full p-1 shadow-lg -mt-4 group">
                        <div class="w-full h-full rounded-full overflow-hidden bg-gray-100 flex items-center justify-center text-4xl font-bold text-blue-600 uppercase border-4 border-white relative">
                            @if($user->foto_profil)
                                <img src="{{ asset($user->foto_profil) }}" alt="Profil" class="w-full h-full object-cover">
                            @else
                                {{ substr($user->nama, 0, 1) }}
                            @endif
                            
                            {{-- Overlay Edit (Hanya visual, uploadnya di form kanan) --}}
                            <div class="absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center opacity-0 group-hover:opacity-100 transition duration-300">
                                <span class="text-white text-xs font-bold">Edit di Kanan</span>
                            </div>
                        </div>
                    </div>

                    <h2 class="mt-4 text-xl font-bold text-gray-900">{{ $user->nama }}</h2>
                    <p class="text-gray-500 text-sm">{{ $user->email }}</p>
                    <p class="text-blue-600 text-sm font-medium mt-1">{{ $user->kontak ?? 'No HP belum diisi' }}</p>
                    
                    <div class="mt-4">
                        <span class="bg-green-100 text-green-800 text-xs font-bold px-3 py-1 rounded-full">
                            Member Terverifikasi
                        </span>
                    </div>
                </div>

                {{-- 2. Statistik Belanja --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                    <h3 class="text-sm font-bold text-gray-400 uppercase mb-4">Aktivitas Belanja</h3>
                    
                    <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 018 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Total Transaksi</p>
                                <p class="font-bold text-gray-900 text-lg">{{ $totalTransaksi }}x Order</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-green-50 rounded-lg text-green-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Total Pengeluaran</p>
                            <p class="font-bold text-gray-900 text-lg">Rp {{ number_format($totalBelanja, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
                
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full bg-red-50 text-red-600 hover:bg-red-100 font-bold py-3 rounded-xl transition shadow-sm flex justify-center items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Keluar Akun
                    </button>
                </form>

            </div>

            {{-- KOLOM KANAN: FORM EDIT --}}
            <div class="lg:col-span-2 space-y-8">
                
                {{-- 1. Form Ubah Data Diri --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-8 shadow-sm">
                    <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Ubah Data Diri
                    </h3>

                    {{-- TAMBAHKAN ENCTYPE UNTUK UPLOAD --}}
                    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('patch')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            
                            {{-- Input Foto Profil --}}
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Foto Profil (Opsional)</label>
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full overflow-hidden flex-shrink-0">
                                         @if($user->foto_profil)
                                            <img src="{{ asset($user->foto_profil) }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-400 font-bold text-xl">{{ substr($user->nama, 0, 1) }}</div>
                                        @endif
                                    </div>
                                    <input type="file" name="foto_profil" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition">
                                </div>
                                @error('foto_profil') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Nama Lengkap</label>
                                <input type="text" name="nama" value="{{ old('nama', $user->nama) }}" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                @error('nama') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Username</label>
                                <input type="text" name="username" value="{{ old('username', $user->username) }}" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Email</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">No. WhatsApp / HP</label>
                                <input type="text" name="kontak" value="{{ old('kontak', $user->kontak) }}" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Alamat Lengkap (Untuk Pengiriman)</label>
                                <textarea name="alamat" rows="3" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Nama Jalan, Nomor Rumah, RT/RW, Kelurahan, Kecamatan...">{{ old('alamat', $user->alamat) }}</textarea>
                                @error('alamat') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg shadow transition transform hover:-translate-y-0.5">
                            Simpan Perubahan
                        </button>

                        @if (session('status') === 'profile-updated')
                            <p class="mt-2 text-sm text-green-600 font-bold" x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)">
                                Data berhasil diperbarui!
                            </p>
                        @endif
                    </form>
                </div>

                {{-- 2. Form Ganti Password --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-8 shadow-sm">
                    <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        Ganti Password
                    </h3>

                    <form method="post" action="{{ route('password.update') }}">
                        @csrf
                        @method('put')

                        <div class="space-y-4 max-w-md">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Password Saat Ini</label>
                                <input type="password" name="current_password" class="w-full border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                                @error('current_password') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Password Baru</label>
                                <input type="password" name="password" class="w-full border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Konfirmasi Password Baru</label>
                                <input type="password" name="password_confirmation" class="w-full border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>

                        <button type="submit" class="mt-6 bg-purple-600 hover:bg-purple-700 text-white font-bold py-2.5 px-6 rounded-lg shadow transition transform hover:-translate-y-0.5">
                            Update Password
                        </button>
                        
                        @if (session('status') === 'password-updated')
                            <p class="mt-2 text-sm text-green-600 font-bold" x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)">
                                Password berhasil diubah!
                            </p>
                        @endif
                    </form>
                </div>

            </div>
        </div>
    </div>

@endcomponent