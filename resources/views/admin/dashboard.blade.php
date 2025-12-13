<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('DASHBOARD INFORMASI') }}
        </h2>
    </x-slot>

    <div class="py-6 space-y-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- 1. KARTU STATISTIK (SUMMARY) --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="relative overflow-hidden bg-gradient-to-br from-blue-600 to-blue-800 rounded-2xl shadow-xl text-white p-6">
                    <div class="relative z-10">
                        <p class="text-blue-100 text-sm font-semibold uppercase tracking-wider">Omset Hari Ini</p>
                        <h3 class="text-3xl font-extrabold mt-1">Rp {{ number_format($omsetHariIni, 0, ',', '.') }}</h3>
                    </div>
                    <div class="absolute right-0 top-0 h-full w-24 bg-white opacity-5 transform skew-x-12"></div>
                    <svg class="absolute bottom-4 right-4 w-12 h-12 text-blue-400 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>

                <div class="relative overflow-hidden bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-2xl shadow-xl text-white p-6">
                    <div class="relative z-10">
                        <p class="text-emerald-100 text-sm font-semibold uppercase tracking-wider">Transaksi Selesai</p>
                        <h3 class="text-3xl font-extrabold mt-1">{{ $transaksiHariIni }} <span class="text-lg font-medium">Nota</span></h3>
                    </div>
                    <div class="absolute right-0 top-0 h-full w-24 bg-white opacity-5 transform skew-x-12"></div>
                    <svg class="absolute bottom-4 right-4 w-12 h-12 text-emerald-300 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                </div>

                <div class="relative overflow-hidden bg-gradient-to-br from-purple-600 to-purple-800 rounded-2xl shadow-xl text-white p-6">
                    <div class="relative z-10">
                        <p class="text-purple-100 text-sm font-semibold uppercase tracking-wider">Estimasi Cuan</p>
                        <h3 class="text-3xl font-extrabold mt-1">Rp {{ number_format($keuntunganHariIni, 0, ',', '.') }}</h3>
                    </div>
                    <div class="absolute right-0 top-0 h-full w-24 bg-white opacity-5 transform skew-x-12"></div>
                    <svg class="absolute bottom-4 right-4 w-12 h-12 text-purple-400 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
            </div>

            {{-- 2. GRAFIK DENYUT NADI TOKO --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                        PENDAPATAN TOKO (7 Hari Terakhir)
                    </h3>
                </div>
                {{-- Container Chart --}}
                <div id="salesChart" class="w-full h-80"></div>
            </div>

            {{-- 3. ANALISA PERFORMA SUPPLIER (ALL IN ONE) --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden" x-data="supplierAnalysis()">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white">🔍 Analisa Performa Supplier</h3>
                        <p class="text-sm text-gray-500">Lihat Produk Terlaris & Produk Tidak Laku</p>
                    </div>
                    
                    {{-- Dropdown Supplier --}}
                    <select x-model="supplierId" @change="fetchData()" 
                            class="w-full md:w-64 border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 transition">
                        <option value="">-- Pilih Supplier --</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id_supplier }}">{{ $s->nama_supplier }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="p-6">
                    {{-- STATE: LOADING --}}
                    <div x-show="loading" class="text-center py-12" style="display: none;">
                        <svg class="animate-spin h-10 w-10 text-indigo-600 mx-auto mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <p class="text-gray-500">Sedang membedah data...</p>
                    </div>

                    {{-- STATE: EMPTY (BELUM PILIH) --}}
                    <div x-show="!supplierId" class="text-center py-12 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
                        <div class="text-5xl mb-3">🚚</div>
                        <h4 class="text-gray-800 font-bold">Pilih Supplier Dulu</h4>
                        <p class="text-gray-500 text-sm">Pilih supplier di atas untuk melihat data rahasia.</p>
                    </div>

                    {{-- STATE: HASIL DATA --}}
                    <div x-show="!loading && supplierId" style="display: none;">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            
                            {{-- KOLOM KIRI: TOP PRODUK (JAWARA) --}}
                            <div class="bg-green-50 dark:bg-green-900/20 rounded-xl p-5 border border-green-100">
                                <h4 class="font-bold text-green-700 dark:text-green-400 flex items-center mb-4">
                                    <span class="text-xl mr-2">🔥</span> Produk Paling Laris
                                </h4>
                                
                                <template x-if="data.top.length === 0">
                                    <p class="text-sm text-gray-500 italic">Belum ada penjualan untuk supplier ini.</p>
                                </template>

                                <ul class="space-y-3">
                                    <template x-for="(item, index) in data.top" :key="index">
                                        <li class="flex justify-between items-center bg-white p-3 rounded shadow-sm">
                                            <div class="flex items-center">
                                                <span class="w-6 h-6 rounded-full bg-green-100 text-green-700 text-xs font-bold flex items-center justify-center mr-3" x-text="index + 1"></span>
                                                <span class="text-sm font-medium text-gray-800" x-text="item.nama_produk"></span>
                                            </div>
                                            <span class="text-sm font-bold text-green-600" x-text="item.total_qty + ' Pcs'"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>

                            {{-- KOLOM KANAN: PRODUK ZOMBIE (MANGKRAK) --}}
                            <div class="bg-red-50 dark:bg-red-900/20 rounded-xl p-5 border border-red-100">
                                <h4 class="font-bold text-red-700 dark:text-red-400 flex items-center mb-4">
                                    <span class="text-xl mr-2">🧟</span> Produk Tidak Laku
                                    <span class="ml-2 text-[10px] bg-red-200 text-red-800 px-2 py-0.5 rounded-full">7 Hari Tanpa Jual</span>
                                </h4>

                                <template x-if="data.zombie.length === 0">
                                    <p class="text-sm text-gray-500 italic">Aman! Tidak ada produk mangkrak.</p>
                                </template>

                                <ul class="space-y-3">
                                    <template x-for="(item, index) in data.zombie" :key="index">
                                        <li class="flex justify-between items-center bg-white p-3 rounded shadow-sm opacity-75 hover:opacity-100 transition">
                                            <div class="flex items-center">
                                                <span class="text-xl mr-3">⚠️</span>
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-medium text-gray-800" x-text="item.nama_produk"></span>
                                                    <span class="text-[10px] text-gray-500">Stok Nyangkut: <b x-text="item.stok"></b></span>
                                                </div>
                                            </div>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- SCRIPTS (ApexCharts & Alpine Logic) --}}
    @push('scripts')
    {{-- 1. CDN APEXCHARTS (GACOR CHART) --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        // --- A. LOGIKA CHART DENYUT NADI ---
        document.addEventListener('DOMContentLoaded', function () {
            var options = {
                series: [{
                    name: 'Omset Penjualan',
                    data: @json($chartData) // Data dari Controller
                }],
                chart: {
                    type: 'area', // Area chart lebih sexy
                    height: 320,
                    fontFamily: 'Figtree, sans-serif',
                    toolbar: { show: false },
                    zoom: { enabled: false }
                },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 3 }, // Garis Lengkung
                xaxis: {
                    categories: @json($chartLabels), // Label Tanggal
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: {
                        formatter: function (value) {
                            return "Rp " + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.7,
                        opacityTo: 0.2,
                        stops: [0, 90, 100]
                    }
                },
                colors: ['#4F46E5'], // Warna Indigo Gacor
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return "Rp " + new Intl.NumberFormat('id-ID').format(val)
                        }
                    }
                }
            };

            var chart = new ApexCharts(document.querySelector("#salesChart"), options);
            chart.render();
        });

        // --- B. LOGIKA ANALISA SUPPLIER (ALPINE) ---
        function supplierAnalysis() {
            return {
                supplierId: '',
                loading: false,
                data: { top: [], zombie: [] },

                async fetchData() {
                    if (!this.supplierId) return;
                    
                    this.loading = true;
                    try {
                        // 1. Panggil URL
                        const url = `{{ route('admin.api.supplierAnalysis') }}?id_supplier=${this.supplierId}`;
                        const response = await fetch(url);

                        // 2. Cek apakah Sukses (Status 200) atau Error (Status 500)
                        if (!response.ok) {
                            // Kalau Error, ambil pesan errornya
                            const errorText = await response.text(); 
                            throw new Error(errorText);
                        }

                        // 3. Kalau Sukses, olah data
                        const result = await response.json();
                        this.data = result;

                    } catch (error) {
                        console.error('Error Detail:', error);
                        // TAMPILKAN ERROR ASLI KE LAYAR (Biar kita tahu salahnya dimana)
                        // Kita potong biar gak kepanjangan, ambil intinya aja
                        let msg = error.message;
                        if (msg.includes('SQLSTATE')) {
                            alert('ERROR DATABASE: \n' + msg.substring(0, 150));
                        } else {
                            alert('ERROR SISTEM: ' + msg.substring(0, 100));
                        }
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>