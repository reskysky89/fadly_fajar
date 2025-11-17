<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produk extends Model
{
    use HasFactory;
    
    // Memberi tahu Laravel nama tabel & PK yang benar
    protected $table = 'produk';
    protected $primaryKey = 'id_produk';
    public $incrementing = false;
    protected $keyType = 'string';

    // Kolom yang boleh diisi untuk tabel 'produk'
    protected $fillable = [
        'id_produk', 'nama_produk', 'id_kategori', 'id_supplier',
        'deskripsi', 'gambar', 'status_produk',
        'id_satuan_dasar', 'harga_pokok_dasar', 'harga_jual_dasar'
    ];
    
    // Relasi ke tabel 'produk_konversi'
    public function produkKonversis(): HasMany
    {
        return $this->hasMany(ProdukKonversi::class, 'id_produk', 'id_produk');
    }
    
    // Relasi lain
    public function kategori(): BelongsTo {
        return $this->belongsTo(Kategori::class, 'id_kategori');
    }
    public function supplier(): BelongsTo {
        return $this->belongsTo(Supplier::class, 'id_supplier');
    }
    public function satuanDasar(): BelongsTo {
         return $this->belongsTo(Satuan::class, 'id_satuan_dasar');
    }
    public function stokMasukDetails(): HasMany
    {
        // 'id_produk' adalah foreign key di tabel 'stok_masuk_detail'
        return $this->hasMany(StokMasukDetail::class, 'id_produk', 'id_produk');
    }
    public function detailTransaksis(): HasMany
    {
        // 'id_produk' adalah foreign key di tabel 'detail_transaksi'
        return $this->hasMany(DetailTransaksi::class, 'id_produk', 'id_produk');
    }
}