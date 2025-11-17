<div x-data="{ modalOpen: false }"> 
    <x-input-label for="id_kategori_dropdown" :value="__('Kategori Produk')" />
    <div class="flex items-center mt-1">
        <select name="id_kategori" id="id_kategori_dropdown" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
            <option value="">Pilih Kategori</option>
            @foreach ($kategoris as $kategori)
                <option value="{{ $kategori->id_kategori }}" {{ old('id_kategori') == $kategori->id_kategori ? 'selected' : '' }}>{{ $kategori->nama_kategori }}</option>
            @endforeach
        </select>
        <button type="button" @click="modalOpen = true" class="ml-2 flex-shrink-0 px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-500 focus:outline-none" title="Tambah Kategori Baru">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
        </button>
    </div>
    <x-input-error :messages="$errors->get('id_kategori')" class="mt-2" />
    
    {{-- MODAL KATEGORI --}}
    <div x-show="modalOpen" x-transition class="fixed inset-0 bg-gray-600 bg-opacity-75 z-50 flex items-center justify-center" @click.away="modalOpen = false" x-cloak>
        <div @click.stop class="relative mx-auto p-6 border w-full max-w-md shadow-lg rounded-md bg-white dark:bg-gray-800">
            <h3 class="text-xl font-medium mb-4">Tambah Kategori Cepat</h3>
            <div id="quick-add-kategori-form">
                <div class="mt-2">
                    <x-input-label for="modal_nama_kategori" :value="__('Nama Kategori Baru')" />
                    <x-text-input id="modal_nama_kategori" class="block mt-1 w-full" type="text" name="nama_kategori" />
                    <p id="modal-error-kategori" class="text-sm text-red-600 mt-1"></p>
                </div>
                <div class="mt-4 flex justify-end space-x-2">
                    <button type="button" id="batal-kategori-cepat" @click="modalOpen = false" class="px-4 py-2 text-sm ...">Batal</button>
                    <button type="button" id="simpan-kategori-cepat" class="px-4 py-2 text-sm ... bg-indigo-600">Simpan</button>
                </div>
            </div>
        </div>
    </div>
</div>