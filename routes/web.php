<?php

use App\Http\Controllers\Admin\ProdukController;
use App\Http\Controllers\Admin\SatuanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\StokMasukController;
use App\Http\Controllers\Admin\KasirController;
use App\Http\Controllers\Kasir\TransaksiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Ini adalah dasbor default untuk 'pelanggan'
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Ini adalah rute profil bawaan Breeze, biarkan saja
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
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
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    
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

});

// Rute untuk Kasir
Route::middleware(['auth', 'verified'])->prefix('kasir')->name('kasir.')->group(function () {
    
    //1. Halaman Utama Kasir (POS / Penjualan)
    Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
    Route::get('/transaksi/cari-produk', [TransaksiController::class, 'cariProduk'])->name('transaksi.cariProduk');
    Route::post('/transaksi', [TransaksiController::class, 'store'])->name('transaksi.store');

    // 2. Jika Kasir mencoba akses /dashboard, lempar ke /transaksi
    Route::get('/dashboard', function () {
        return redirect()->route('kasir.transaksi.index');
    })->name('dashboard');

    Route::get('/riwayat', [TransaksiController::class, 'riwayat'])->name('riwayat.index');
    Route::get('/transaksi/detail', [TransaksiController::class, 'show'])->name('transaksi.show');
    Route::get('/transaksi/cetak', [TransaksiController::class, 'cetak'])->name('transaksi.cetak');

});

// --- AKHIR BLOK KODE BARU --- //


// Baris ini harus selalu ada di paling bawah
require __DIR__.'/auth.php';