<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';
    
    // PENTING: Karena ID kita String (misal: 0001/KSR/...), bukan Angka Auto-Increment
    public $incrementing = false; 
    protected $keyType = 'string';

    // DAFTARKAN SEMUA KOLOM DI SINI AGAR BISA DISIMPAN
    protected $fillable = [
        'id_transaksi',
        'id_user_kasir',
        'id_user_pelanggan',
        'nama_kasir',
        'nama_pelanggan',
        'tanggal_transaksi', // <-- TAMBAHKAN INI
        'waktu_transaksi',
        'total_harga',
        'bayar',      // <-- Pastikan ada
        'kembalian',  // <-- Pastikan ada
        'metode_bayar',
        'status_pesanan',
        'jenis_transaksi',
        'tipe_transaksi'
    ];

    // Relasi ke Detail Barang
    public function details(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class, 'id_transaksi', 'id_transaksi');
    }
    
    // Relasi ke User Kasir
    public function kasir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user_kasir', 'id_user');
    }
}