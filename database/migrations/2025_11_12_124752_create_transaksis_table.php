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
        Schema::create('transaksi', function (Blueprint $table) {
            $table->string('id_transaksi', 50)->primary();

            // FK Kasir (boleh null jika kasir sudah dihapus, tetap aman)
            $table->unsignedBigInteger('id_user_kasir')->nullable();
            $table->foreign('id_user_kasir')
                ->references('id_user')->on('users')
                ->onDelete('set null');

            // Backup nama kasir (agar transaksi tetap tampil meski akun terhapus)
            $table->string('nama_kasir');

            // FK Pelanggan (boleh null karena defaultnya "Umum")
            $table->unsignedBigInteger('id_user_pelanggan')->nullable();
            $table->foreign('id_user_pelanggan')
                ->references('id_user')->on('users')
                ->onDelete('set null');

            // Backup nama pelanggan (default = Umum)
            $table->string('nama_pelanggan')->default('Umum');

            $table->date('tanggal_transaksi');
            $table->time('waktu_transaksi');

            $table->enum('jenis_transaksi', ['online', 'offline']);
            $table->enum('tipe_transaksi', ['penjualan', 'restock']);
            $table->enum('metode_bayar', ['cash', 'transfer']);
            $table->enum('status_pesanan', ['diproses', 'selesai', 'batal']);

            $table->decimal('total_harga', 12, 2);
            $table->decimal('bayar', 12, 2)->default(0);      // Uang Konsumen
            $table->decimal('kembalian', 12, 2)->default(0);  // Kembalian
            // -------------------------------

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
