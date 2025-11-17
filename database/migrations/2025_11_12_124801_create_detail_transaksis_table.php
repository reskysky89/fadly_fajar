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
            
            // --- PERBAIKAN DI SINI ---
            // Ini HARUS 'string' dan panjangnya '50', SAMA PERSIS dengan tabel 'transaksi'
            $table->string('id_transaksi', 50); 
            // -------------------------
            
            $table->string('id_produk', 50); 
            
            $table->integer('jumlah');
            $table->string('satuan', 20);
            $table->decimal('harga_satuan', 12, 2);
            $table->decimal('subtotal', 12, 2);
            
            $table->timestamps();

            // Foreign Keys
            $table->foreign('id_transaksi')
                  ->references('id_transaksi')->on('transaksi')
                  ->onDelete('cascade');
            
            $table->foreign('id_produk')
                  ->references('id_produk')->on('produk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_transaksi');
    }
};