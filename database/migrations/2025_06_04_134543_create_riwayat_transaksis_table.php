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
        Schema::create('riwayat_transaksis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('jenis_transaksi');
            $table->unsignedBigInteger('referensi_id')->nullable();
            $table->string('referensi_tipe');
            $table->decimal('jumlah', 15, 2);
            $table->date('tanggal');
            $table->string('status')->default('pending');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->index('user_id');
            $table->index('jenis_transaksi');
            $table->index(['referensi_id', 'referensi_tipe']);
            $table->index('tanggal');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_transaksis');
    }
};
