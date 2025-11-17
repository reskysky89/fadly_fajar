<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StokMasukBatch extends Model
{
    use HasFactory;

    /**
     * Memberi tahu Laravel nama tabel yang benar di database.
     * (Karena nama tabel kita 'stok_masuk_batch', bukan 'stok_masuk_batches')
     */
    protected $table = 'stok_masuk_batch';

    /**
     * Memberi tahu Laravel primary key kustom kita.
     */
    protected $primaryKey = 'id_batch_stok';
    
    /**
     * Tentukan kolom mana saja yang BOLEH diisi.
     */
    protected $fillable = [
        'id_user',
        'id_supplier',
        'no_faktur_supplier',
        'tanggal_masuk',
        'total_nilai_faktur',
        'keterangan',
        'id_user_diubah',
    ];

    /**
     * Relasi untuk mengambil nama 'User' yang menginput
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    /**
     * Relasi untuk mengambil nama 'Supplier'
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'id_supplier', 'id_supplier');
    }

    /**
     * Relasi untuk mengambil nama 'User' yang mengubah
     */
    public function userDiubah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user_diubah', 'id_user');
    }

    public function details(): HasMany
    {
        // 'id_batch_stok' adalah foreign key di tabel 'stok_masuk_detail'
        return $this->hasMany(StokMasukDetail::class, 'id_batch_stok', 'id_batch_stok');
    }
    
    /**
     * (Opsional) Relasi: Satu Batch punya Banyak Detail
     */
    // public function details()
    // {
    //    return $this->hasMany(StokMasukDetail::class, 'id_batch_stok');
    // }
}