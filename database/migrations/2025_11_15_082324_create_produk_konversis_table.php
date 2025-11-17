<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Buat tabel 'produk_konversi' (singular)
        Schema::create('produk_konversi', function (Blueprint $table) {
            $table->id('id_konversi');

            // Foreign Key ke tabel 'produk'
            $table->string('id_produk', 50); 

            // Foreign Key ke tabel 'satuan'
            $table->unsignedBigInteger('id_satuan_konversi'); 

            $table->integer('nilai_konversi'); // e.g., 40 (pcs)
            $table->decimal('harga_pokok_konversi', 12, 2); // e.g., 120000 (modal per dus)
            $table->decimal('harga_jual_konversi', 12, 2); // e.g., 135000 (jual per dus)

            $table->timestamps();

            // Definisi Foreign Key
            $table->foreign('id_produk')
                  ->references('id_produk')->on('produk')
                  ->onDelete('cascade'); // Jika produk dihapus, konversinya ikut terhapus

            $table->foreign('id_satuan_konversi')
                  ->references('id_satuan')->on('satuan')
                  ->onDelete('restrict'); // Larang hapus 'satuan' jika masih dipakai di sini
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk_konversi');
    }
};