<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StokMasukDetail extends Model
{
    use HasFactory;

    /**
     * Memberi tahu Laravel nama tabel yang benar di database.
     * (Karena nama tabel kita 'stok_masuk_detail', bukan 'stok_masuk_details')
     */
    protected $table = 'stok_masuk_detail';

    /**
     * Memberi tahu Laravel primary key kustom kita.
     */
    protected $primaryKey = 'id_detail_stok';
    
    /**
     * Tentukan kolom mana saja yang BOLEH diisi.
     * Ini penting agar 'StokMasukController@store' kita berfungsi.
     */
    protected $fillable = [
        'id_batch_stok',
        'id_produk',
        'jumlah',
        'id_satuan',
        'satuan',
        'harga_beli_satuan',
    ];

    /**
     * Relasi: Satu Detail milik satu Batch (Induk)
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(StokMasukBatch::class, 'id_batch_stok', 'id_batch_stok');
    }

    /**
     * Relasi: Satu Detail milik satu Produk
     */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }
}