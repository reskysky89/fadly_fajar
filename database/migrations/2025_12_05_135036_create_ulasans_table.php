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
        Schema::create('ulasan', function (Blueprint $table) {
            $table->id('id_ulasan');
            $table->unsignedBigInteger('id_user'); // Pelanggan
            $table->string('id_transaksi', 50);    // Transaksi yang dinilai
            
            $table->integer('rating'); // 1-5 (Wajib)
            $table->text('komentar')->nullable(); // (Boleh Kosong / Tidak Wajib)
            
            $table->timestamps();

            // Foreign Keys
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
            $table->foreign('id_transaksi')->references('id_transaksi')->on('transaksi')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ulasans');
    }
};
