<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tambah Kasir Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form action="{{ route('admin.kasir.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            {{-- KOLOM KIRI: DATA PRIBADI --}}
                            <div class="space-y-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b pb-2">Data Pribadi</h3>
                                
                                {{-- Nama Lengkap --}}
                                <div>
                                    <x-input-label for="nama" :value="__('Nama Lengkap')" />
                                    <x-text-input id="nama" class="block mt-1 w-full" type="text" name="nama" :value="old('nama')" required autofocus />
                                    <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                                </div>

                                {{-- Kontak / No HP --}}
                                <div>
                                    <x-input-label for="kontak" :value="__('No. Telepon / WA')" />
                                    <x-text-input id="kontak" class="block mt-1 w-full" type="text" name="kontak" :value="old('kontak')" />
                                    <x-input-error :messages="$errors->get('kontak')" class="mt-2" />
                                </div>

                                {{-- Foto Profil --}}
                                <div>
                                    <x-input-label for="foto_profil" :value="__('Foto Profil (Opsional)')" />
                                    <input id="foto_profil" type="file" name="foto_profil" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 mt-1">
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-300" id="file_input_help">SVG, PNG, JPG or GIF (MAX. 2MB).</p>
                                    <x-input-error :messages="$errors->get('foto_profil')" class="mt-2" />
                                </div>
                            </div>

                            {{-- KOLOM KANAN: DATA AKUN (LOGIN) --}}
                            <div class="space-y-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b pb-2">Informasi Akun (Login)</h3>

                                {{-- Username --}}
                                <div>
                                    <x-input-label for="username" :value="__('Username')" />
                                    <x-text-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username')" required />
                                    <x-input-error :messages="$errors->get('username')" class="mt-2" />
                                </div>

                                {{-- Email --}}
                                <div>
                                    <x-input-label for="email" :value="__('Email Address')" />
                                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>

                                {{-- Password --}}
                                <div>
                                    <x-input-label for="password" :value="__('Password')" />
                                    <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                </div>

                                {{-- Konfirmasi Password --}}
                                <div>
                                    <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
                                    <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required />
                                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                                </div>
                            </div>

                        </div>

                        {{-- TOMBOL AKSI --}}
                        <div class="flex items-center justify-end mt-8 border-t pt-4 dark:border-gray-700">
                            <a href="{{ route('admin.kasir.index') }}" class="text-gray-600 dark:text-gray-400 hover:underline mr-4">
                                Batal
                            </a>
                            <x-primary-button class="ml-4">
                                {{ __('Simpan Kasir') }}
                            </x-primary-button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>