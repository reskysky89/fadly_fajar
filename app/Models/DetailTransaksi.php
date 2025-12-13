<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailTransaksi extends Model
{
    use HasFactory;

    protected $table = 'detail_transaksi';
    protected $primaryKey = 'id_detail';

    protected $fillable = [
        'id_transaksi',
        'id_produk',
        'jumlah',
        'satuan',
        'harga_satuan', // Harga JUAL saat transaksi terjadi
        'subtotal'
    ];
    public function transaksi()
    {
        // belongsTo artinya: "Detail ini MILIK satu Transaksi"
        // Parameter: (Model Tujuan, Key Penghubung di tabel ini, Key Utama di tabel sana)
        return $this->belongsTo(Transaksi::class, 'id_transaksi', 'id_transaksi');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }
}