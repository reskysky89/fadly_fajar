@component('layouts.guest_market')

    <div class="max-w-screen-xl mx-auto px-4">
        
        <h1 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-4">Keranjang Belanja</h1>

        {{-- Alert Sukses --}}
        @if(session('success'))
            <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-100 border border-green-200 flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        @if($keranjang->count() > 0)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- DAFTAR BARANG --}}
                <div class="lg:col-span-2 space-y-4">
                    @foreach($keranjang as $item)
                        <div class="flex flex-col sm:flex-row items-center bg-white border border-gray-200 rounded-xl shadow-sm p-4 transition hover:shadow-md"
                             x-data="{ qty: {{ $item->jumlah }} }">
                            
                            {{-- Gambar --}}
                            <div class="w-24 h-24 flex-shrink-0 overflow-hidden rounded-lg bg-gray-100">
                                @if($item->produk->gambar)
                                    <img src="{{ asset('storage/' . $item->produk->gambar) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="flex items-center justify-center h-full text-gray-400">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                @endif
                            </div>

                            {{-- Info Produk --}}
                            <div class="flex-1 sm:ml-6 text-center sm:text-left w-full mt-4 sm:mt-0">
                                <h3 class="text-lg font-bold text-gray-900">{{ $item->produk->nama_produk }}</h3>
                                <p class="text-sm text-gray-500">Satuan: <span class="font-semibold text-blue-600">{{ $item->satuan }}</span></p>
                                <p class="text-lg font-bold text-blue-700 mt-1">Rp {{ number_format($item->harga_saat_ini, 0, ',', '.') }}</p>
                            </div>

                            {{-- Aksi (Update Qty & Hapus) --}}
                            <div class="flex flex-col items-center sm:items-end gap-3 w-full sm:w-auto mt-4 sm:mt-0">
                                
                                {{-- Update Form --}}
                                <form action="{{ route('keranjang.update', $item->id_keranjang) }}" method="POST" class="flex items-center">
                                    @csrf
                                    @method('PATCH')
                                    <input type="number" name="jumlah" x-model="qty" min="1" class="w-16 text-center border-gray-300 rounded-l-lg text-sm focus:ring-blue-500 focus:border-blue-500 py-1">
                                    <button type="submit" class="bg-gray-100 hover:bg-gray-200 border border-l-0 border-gray-300 rounded-r-lg px-3 py-1 text-sm text-gray-600 transition">
                                        Update
                                    </button>
                                </form>

                                {{-- Hapus --}}
                                <form action="{{ route('keranjang.destroy', $item->id_keranjang) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-semibold flex items-center gap-1" onclick="return confirm('Hapus item ini?')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- RINGKASAN BELANJA (Sticky) --}}
                <div class="lg:col-span-1">
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 sticky top-24">
                        <h2 class="text-lg font-bold text-gray-800 mb-4">Ringkasan Belanja</h2>
                        
                        <div class="flex justify-between mb-2 text-gray-600">
                            <span>Total Item</span>
                            <span>{{ $keranjang->count() }} Barang</span>
                        </div>
                        <div class="border-t border-gray-200 my-4"></div>
                        
                        <div class="flex justify-between mb-6 items-center">
                            <span class="text-lg font-bold text-gray-800">Total Harga</span>
                            @php
                                $total = $keranjang->sum(fn($i) => $i->harga_saat_ini * $i->jumlah);
                            @endphp
                            <span class="text-2xl font-extrabold text-blue-700">Rp {{ number_format($total, 0, ',', '.') }}</span>
                        </div>

                        {{-- Tombol Checkout (Nanti kita buat rutenya) --}}
                        <form action="{{ route('checkout.index') }}" method="GET">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg shadow-lg transition transform hover:-translate-y-1">
                                Lanjut ke Pembayaran
                            </button>
                        </form>
                        
                        <a href="{{ url('/') }}" class="block text-center text-sm text-gray-500 mt-4 hover:underline">Lanjut Belanja</a>
                    </div>
                </div>

            </div>
        @else
            {{-- KERANJANG KOSONG --}}
            <div class="text-center py-20">
                <div class="bg-gray-100 rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Keranjang Anda Kosong</h2>
                <p class="text-gray-500 mb-6">Wah, sepertinya Anda belum memilih barang apapun.</p>
                <a href="{{ url('/') }}" class="bg-blue-600 text-white font-bold py-3 px-8 rounded-full hover:bg-blue-700 transition shadow-lg">
                    Mulai Belanja Sekarang
                </a>
            </div>
        @endif

    </div>

@endcomponent