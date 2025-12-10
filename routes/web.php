<?php

use App\Http\Controllers\Admin\ProdukController;
use App\Http\Controllers\Admin\SatuanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\StokMasukController;
use App\Http\Controllers\Admin\KasirController;
use App\Http\Controllers\Admin\PesananOnlineController;
use App\Http\Controllers\Kasir\TransaksiController;
use App\Http\Controllers\Admin\LaporanPenjualanController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Pelanggan\KeranjangController;
use App\Http\Controllers\Pelanggan\CheckoutController;
use App\Http\Controllers\Pelanggan\RiwayatPesananController;
use App\Http\Controllers\NotifikasiController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;


Route::get('/', [HomeController::class, 'index'])->name('home');

// Ini adalah dasbor default untuk 'pelanggan'
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Ini adalah rute profil bawaan Breeze, biarkan saja
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/pesanan-online', [PesananOnlineController::class, 'index'])->name('pesanan.index');
    Route::get('/pesanan-online/{id}/picking-list', [PesananOnlineController::class, 'cetakPickingList'])->name('pesanan.picking');
    Route::get('/pesanan-online/{id}/proses', [PesananOnlineController::class, 'edit'])->name('pesanan.edit');
    Route::put('/pesanan-online/{id}/selesai-proses', [PesananOnlineController::class, 'update'])->name('pesanan.update');
    // Route::put('/pesanan-online/{id}/selesai', [PesananOnlineController::class, 'selesaikan'])->name('pesanan.selesai');
    // Route::get('/pesanan-online', [PesananOnlineController::class, 'index'])->name('pesanan.index');
    // Route::put('/pesanan-online/{id}/selesai', [PesananOnlineController::class, 'selesaikan'])->name('pesanan.selesai');
    Route::put('/pesanan-online/{id}/batal', [PesananOnlineController::class, 'batalkan'])->name('pesanan.batal');
    Route::get('/notifikasi/baca/{id}', [NotifikasiController::class, 'baca'])->name('notifikasi.baca');
    Route::get('/notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('notifikasi.bacaSemua');
    Route::get('/api/cek-pesanan-baru', [PesananOnlineController::class, 'cekPesananBaru'])->name('api.cekPesananBaru');
    
});

// --- TAMBAKAN BLOK KODE BARU ANDA DI SINI --- //

/*
|--------------------------------------------------------------------------
| Rute Dasbor Multi-Peran
|--------------------------------------------------------------------------
|
| Ini adalah rute untuk dasbor Admin dan Kasir.
|
*/

// Rute untuk Admin
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    
    // Rute: /admin/dashboard
    // Nama Rute: admin.dashboard
    Route::get('/dashboard', function () {
        return view('admin.dashboard'); // <-- Memanggil file view yang cantik
    })->name('dashboard');

    // Tambahkan rute admin lainnya (kelola produk, laporan, dll) di sini
    // Ini akan otomatis membuat 7 rute CRUD untuk produk
    Route::resource('/produk', ProdukController::class);
    // --- AKHIR BARIS BARU ---
    // Rute untuk CRUD "Manajemen Satuan"
    Route::resource('/satuan', SatuanController::class);
    // Contoh: Route::get('/produk', [ProdukController::class, 'index'])->name('produk.index');
    // --- INI KODE YANG BENAR ---
    Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
    Route::post('/supplier', [SupplierController::class, 'store'])->name('supplier.store');
    // 1. Rute untuk menampilkan halaman utama "Riwayat Stok Masuk"
    Route::get('/stok-masuk', [StokMasukController::class, 'index'])->name('stok.index');
    // 2. Rute yang akan dipanggil AJAX untuk mencari produk
    Route::get('/stok-masuk/cari-produk', [StokMasukController::class, 'cariProduk'])->name('stok.cariProduk');
    // 2. Rute untuk menampilkan form "Input Stok Masuk"
    Route::get('/stok-masuk/create', [StokMasukController::class, 'create'])->name('stok.create');
    // 3. Rute untuk menyimpan transaksi stok masuk
    Route::post('/stok-masuk', [StokMasukController::class, 'store'])->name('stok.store');
    Route::get('/produk/{produk}/toggle-status', [ProdukController::class, 'toggleStatus'])
     ->name('produk.toggleStatus');
    
    Route::get('/stok-masuk/{batch}/edit', [StokMasukController::class, 'edit'])->name('stok.edit');    
    // 6. Rute untuk MENYIMPAN perubahan dari halaman edit
    Route::put('/stok-masuk/{batch}', [StokMasukController::class, 'update'])->name('stok.update');
    // 1. Resource controller (tanpa destroy/show karena kita custom)
    Route::resource('/kasir', KasirController::class)->except(['show', 'destroy']);
    
    // 2. Rute khusus untuk Toggle Status (Pengganti Delete)
    Route::patch('/kasir/{id}/toggle-status', [KasirController::class, 'toggleStatus'])->name('kasir.toggleStatus');
    // Laporan Penjualan
    Route::get('/laporan-penjualan', [LaporanPenjualanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan-penjualan/edit-transaksi', [LaporanPenjualanController::class, 'edit'])->name('laporan.edit');
    Route::put('/laporan-penjualan/update-transaksi', [LaporanPenjualanController::class, 'update'])->name('laporan.update');
    
});
// 2. RUTE TRANSAKSI / POS (Bisa Diakses Admin & Kasir)
// ==============================================================================
// Kita keluarkan dari grup 'kasir' agar Admin juga bisa akses.
// Tapi kita tetap pakai prefix 'kasir' dan name 'kasir.' agar Script JS tidak error.
Route::middleware('auth')->prefix('kasir')->name('kasir.')->group(function () {
    
    // Halaman Utama Penjualan
    Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
    
    // API & Action Penjualan
    Route::get('/transaksi/cari-produk', [TransaksiController::class, 'cariProduk'])->name('transaksi.cariProduk');
    Route::post('/transaksi', [TransaksiController::class, 'store'])->name('transaksi.store');

    // Riwayat & Detail
    Route::get('/riwayat', [TransaksiController::class, 'riwayat'])->name('riwayat.index');
    Route::get('/transaksi/detail', [TransaksiController::class, 'show'])->name('transaksi.show');
    Route::get('/transaksi/cetak', [TransaksiController::class, 'cetak'])->name('transaksi.cetak');

});

// Rute untuk Kasir
Route::middleware('auth')->prefix('kasir')->name('kasir.')->group(function () {
    
    Route::get('/dashboard', function () {
        return redirect()->route('kasir.transaksi.index');
    })->name('dashboard');

});
// RUTE KHUSUS PELANGGAN (Wajib Login & Wajib Verifikasi)
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Halaman-halaman ini HANYA bisa diakses kalau sudah klik email
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/keranjang', [KeranjangController::class, 'index'])->name('keranjang.index'); // Nanti kita buat
    Route::post('/keranjang/tambah', [KeranjangController::class, 'tambah'])->name('keranjang.tambah');
    Route::patch('/keranjang/{id}', [KeranjangController::class, 'update'])->name('keranjang.update');
    Route::delete('/keranjang/{id}', [KeranjangController::class, 'destroy'])->name('keranjang.destroy');
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store'); 
    Route::get('/riwayat-pesanan', [RiwayatPesananController::class, 'index'])->name('pelanggan.riwayat');
    Route::put('/riwayat-pesanan/{id}/batal', [RiwayatPesananController::class, 'batalkanPesanan'])->name('pelanggan.riwayat.batal');
    Route::post('/ulasan', [App\Http\Controllers\Pelanggan\UlasanController::class, 'store'])->name('ulasan.store');

});
Route::get('/api/cek-stok/{id}', [App\Http\Controllers\HomeController::class, 'cekStok'])->name('api.cekStok');

// --- AKHIR BLOK KODE BARU --- //


// Baris ini harus selalu ada di paling bawah
require __DIR__.'/auth.php';