<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manajemen Satuan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                {{-- KIRI: FORM TAMBAH SATUAN --}}
                <div class="md:col-span-1">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-semibold mb-4">Tambah Satuan Baru</h3>
                            <form action="{{ route('admin.satuan.store') }}" method="POST">
                                @csrf
                                <div>
                                    <x-input-label for="nama_satuan" :value="__('Nama Satuan (Contoh: PCS, DUS)')" />
                                    <x-text-input id="nama_satuan" class="block mt-1 w-full" type="text" name="nama_satuan" :value="old('nama_satuan')" required autofocus />
                                    <x-input-error :messages="$errors->get('nama_satuan')" class="mt-2" />
                                </div>
                                <div class="mt-4">
                                    <x-input-label for="keterangan" :value="__('Keterangan (Opsional)')" />
                                    <textarea id="keterangan" name="keterangan" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm">{{ old('keterangan') }}</textarea>
                                    <x-input-error :messages="$errors->get('keterangan')" class="mt-2" />
                                </div>
                                <div class="flex items-center justify-end mt-4">
                                    <x-primary-button>
                                        {{ __('Simpan Satuan') }}
                                    </x-primary-button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- KANAN: TABEL DAFTAR SATUAN --}}
                <div class="md:col-span-2">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-semibold mb-4">Daftar Satuan yang Ada</h3>
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Satuan</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse ($satuans as $satuan)
                                        <tr>
                                            <td class="px-6 py-4">{{ $satuan->nama_satuan }}</td>
                                            <td class="px-6 py-4">{{ $satuan->keterangan }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="px-6 py-4 text-center">Belum ada data satuan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>