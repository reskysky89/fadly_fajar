<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- PASTIKAN INI DITAMBAHKAN

class ProdukKonversi extends Model
{
    use HasFactory;
    
    // Memberi tahu Laravel nama tabel & PK yang benar
    protected $table = 'produk_konversi';
    protected $primaryKey = 'id_konversi';
    
    // Kolom yang boleh diisi untuk tabel 'produk_konversi'
    protected $fillable = [
        'id_produk',
        'id_satuan_konversi',
        'nilai_konversi',
        'harga_pokok_konversi',
        'harga_jual_konversi'
    ];

    // --- INI FUNGSI YANG HILANG & MENYEBABKAN ERROR ---
    /**
     * Relasi: Satu Konversi milik satu Satuan
     */
    public function satuan(): BelongsTo
    {
        return $this->belongsTo(Satuan::class, 'id_satuan_konversi', 'id_satuan');
    }
    // --- AKHIR FUNGSI BARU ---

    /**
     * Relasi: Satu Konversi milik satu Produk (Induk)
     */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }
}