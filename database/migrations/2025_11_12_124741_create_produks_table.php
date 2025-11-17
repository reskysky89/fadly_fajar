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
        Schema::create('produk', function (Blueprint $table) {
        // Data Umum
        $table->string('id_produk', 50)->primary(); // Kode Item / Barcode
        $table->string('nama_produk', 150);
        $table->unsignedBigInteger('id_kategori');
        $table->unsignedBigInteger('id_supplier');
        $table->string('gambar', 255)->nullable();
        $table->text('deskripsi')->nullable();

        // Satuan & Harga Dasar
        $table->unsignedBigInteger('id_satuan_dasar'); // FK ke tabel 'satuan' (e.g., "PCS")
        $table->decimal('harga_pokok_dasar', 12, 2); // Harga Pokok per Satuan Dasar
        $table->decimal('harga_jual_dasar', 12, 2); // Harga Jual per Satuan Dasar

        $table->enum('status_produk', ['aktif', 'nonaktif'])->default('aktif');
        $table->timestamps();

        // Definisi Foreign Key
        $table->foreign('id_kategori')->references('id_kategori')->on('kategori')->onDelete('cascade');
        $table->foreign('id_supplier')->references('id_supplier')->on('supplier')->onDelete('cascade');

        // Relasi baru ke tabel 'satuan'
        $table->foreign('id_satuan_dasar')
              ->references('id_satuan')->on('satuan')
              ->onDelete('restrict'); 
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
