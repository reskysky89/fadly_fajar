<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Data Kasir: ') }} {{ $kasir->nama }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form action="{{ route('admin.kasir.update', $kasir->id_user) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT') {{-- Wajib untuk update --}}

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            {{-- KOLOM KIRI: DATA PRIBADI --}}
                            <div class="space-y-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b pb-2">Data Pribadi</h3>
                                
                                {{-- Nama Lengkap --}}
                                <div>
                                    <x-input-label for="nama" :value="__('Nama Lengkap')" />
                                    <x-text-input id="nama" class="block mt-1 w-full" type="text" name="nama" :value="old('nama', $kasir->nama)" required autofocus />
                                    <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                                </div>

                                {{-- Kontak / No HP --}}
                                <div>
                                    <x-input-label for="kontak" :value="__('No. Telepon / WA')" />
                                    <x-text-input id="kontak" class="block mt-1 w-full" type="text" name="kontak" :value="old('kontak', $kasir->kontak)" />
                                    <x-input-error :messages="$errors->get('kontak')" class="mt-2" />
                                </div>

                                {{-- Foto Profil --}}
                                <div>
                                    <x-input-label for="foto_profil" :value="__('Ganti Foto Profil (Opsional)')" />
                                    <input id="foto_profil" type="file" name="foto_profil" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 mt-1">
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-300" id="file_input_help">Biarkan kosong jika tidak ingin mengganti foto.</p>
                                    <x-input-error :messages="$errors->get('foto_profil')" class="mt-2" />
                                    
                                    {{-- Preview Foto Lama --}}
                                    @if($kasir->foto_profil)
                                        <div class="mt-2">
                                            <p class="text-sm text-gray-500 mb-1">Foto Saat Ini:</p>
                                            <img src="{{ asset('storage/' . $kasir->foto_profil) }}" alt="Foto Profil" class="h-20 w-20 object-cover rounded-md border">
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- KOLOM KANAN: DATA AKUN (LOGIN) --}}
                            <div class="space-y-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b pb-2">Informasi Akun (Login)</h3>

                                {{-- Username --}}
                                <div>
                                    <x-input-label for="username" :value="__('Username')" />
                                    <x-text-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username', $kasir->username)" required />
                                    <x-input-error :messages="$errors->get('username')" class="mt-2" />
                                </div>

                                {{-- Email --}}
                                <div>
                                    <x-input-label for="email" :value="__('Email Address')" />
                                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $kasir->email)" required />
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>

                                <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-md border border-gray-200 dark:border-gray-600">
                                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Ubah Password (Opsional)</h4>
                                    <p class="text-xs text-gray-500 mb-4">Isi hanya jika ingin mengganti password kasir ini.</p>

                                    {{-- Password --}}
                                    <div class="mb-4">
                                        <x-input-label for="password" :value="__('Password Baru')" />
                                        <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" autocomplete="new-password" />
                                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                    </div>

                                    {{-- Konfirmasi Password --}}
                                    <div>
                                        <x-input-label for="password_confirmation" :value="__('Konfirmasi Password Baru')" />
                                        <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" />
                                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- TOMBOL AKSI --}}
                        <div class="flex items-center justify-end mt-8 border-t pt-4 dark:border-gray-700">
                            <a href="{{ route('admin.kasir.index') }}" class="text-gray-600 dark:text-gray-400 hover:underline mr-4">
                                Batal
                            </a>
                            <x-primary-button class="ml-4">
                                {{ __('Simpan Perubahan') }}
                            </x-primary-button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>