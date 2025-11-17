<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use HasFactory;

    // Tentukan primary key kustom Anda
    protected $table = 'kategori';
    protected $primaryKey = 'id_kategori'; 
    protected $fillable = ['nama_kategori', 'deskripsi'];

    // Tentukan relasi: Satu Kategori punya Banyak Produk
    public function produks()
    {
        // 'id_kategori' adalah foreign key di tabel 'produks'
        return $this->hasMany(Produk::class, 'id_kategori'); 
    }
}