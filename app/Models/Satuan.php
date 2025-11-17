<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Satuan extends Model
{
    use HasFactory;

    /**
     * Memberi tahu Laravel nama tabel yang benar di database.
     * (Karena nama tabel kita 'satuan', bukan 'satuans')
     */
    protected $table = 'satuan';

    /**
     * Memberi tahu Laravel primary key kustom kita.
     * (Karena kita menggunakan 'id_satuan', bukan 'id')
     */
    protected $primaryKey = 'id_satuan';

    /**
     * Tentukan kolom mana saja yang BOLEH diisi melalui form.
     * Ini penting untuk keamanan (Mass Assignment) dan agar fungsi store() kita berhasil.
     */
    protected $fillable = [
        'nama_satuan',
        'keterangan',
    ];

    /**
     * (Opsional) Relasi: Satu Satuan bisa dipakai di banyak Produk (sebagai satuan dasar)
     */
    // public function produksSatuanDasar()
    // {
    //     return $this->hasMany(Produk::class, 'id_satuan_dasar');
    // }
}