<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pastikan nama tabelnya 'keranjangs' (pakai s)
        Schema::create('keranjangs', function (Blueprint $table) {
            $table->id('id_keranjang');
            
            // Kolom-kolom ini yang sebelumnya hilang:
            $table->unsignedBigInteger('id_user');
            $table->string('id_produk', 50);
            $table->integer('jumlah')->default(1);
            $table->string('satuan', 20)->default('PCS'); 
            $table->decimal('harga_saat_ini', 12, 2);

            $table->timestamps();

            // Foreign Keys
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
            $table->foreign('id_produk')->references('id_produk')->on('produk')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keranjangs');
    }
};
