<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_masuk_detail', function (Blueprint $table) {
            $table->id('id_detail_stok');
            $table->unsignedBigInteger('id_batch_stok');
            $table->string('id_produk', 50); 
            $table->integer('jumlah');
            $table->unsignedBigInteger('id_satuan')->nullable();
            $table->string('satuan', 20); // Misal: "DUS" atau "PCS"
            $table->decimal('harga_beli_satuan', 12, 2); 
            $table->timestamps();

            // Foreign Keys
            $table->foreign('id_batch_stok')
                  ->references('id_batch_stok')->on('stok_masuk_batch')
                  ->onDelete('cascade');
            $table->foreign('id_produk')
                  ->references('id_produk')->on('produk')
                  ->onDelete('cascade');
            $table->foreign('id_satuan')->references('id_satuan')->on('satuan')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_masuk_detail');
    }
};