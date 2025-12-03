<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_transaksi', function (Blueprint $table) {
            $table->id('id_detail');
            
            $table->string('id_transaksi', 50); 
            $table->string('id_produk', 50); 
            
            $table->integer('jumlah');
            
            // --- PERBAIKAN DATABASE ---
            // Kita simpan ID-nya untuk referensi pasti (Edit)
            $table->unsignedBigInteger('id_satuan')->nullable(); 
            // Kita TETAP simpan Namanya untuk sejarah (History) jika data master dihapus
            $table->string('satuan', 20); 
            // --------------------------

            $table->decimal('harga_satuan', 12, 2);
            $table->decimal('subtotal', 12, 2);
            
            $table->timestamps();

            $table->foreign('id_transaksi')->references('id_transaksi')->on('transaksi')->onDelete('cascade');
            $table->foreign('id_produk')->references('id_produk')->on('produk');
            
            // Optional: Foreign key ke tabel satuan (set null jika satuan dihapus master)
            $table->foreign('id_satuan')->references('id_satuan')->on('satuan')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_transaksi');
    }
};