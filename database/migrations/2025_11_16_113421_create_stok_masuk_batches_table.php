<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_masuk_batch', function (Blueprint $table) {
            $table->id('id_batch_stok');
            
            // Siapa yang menginput? (Kolom 'User')
            $table->unsignedBigInteger('id_user'); 
            
            // --- PERUBAHAN (Dibuat Opsional) ---
            $table->unsignedBigInteger('id_supplier')->nullable(); 
            $table->string('no_faktur_supplier')->nullable(); 
            $table->decimal('total_nilai_faktur', 12, 2)->nullable();
            // --- AKHIR PERUBAHAN ---
            
            $table->date('tanggal_masuk');
            $table->text('keterangan')->nullable(); // Sesuai permintaan Anda
            $table->unsignedBigInteger('id_user_diubah')->nullable(); // Sesuai permintaan Anda
            
            $table->timestamps(); // Ini akan membuat 'created_at' (Waktu)

            // Foreign Keys
            $table->foreign('id_user')->references('id_user')->on('users');
            $table->foreign('id_supplier')->references('id_supplier')->on('supplier');
            $table->foreign('id_user_diubah')->references('id_user')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_masuk_batch');
    }
};