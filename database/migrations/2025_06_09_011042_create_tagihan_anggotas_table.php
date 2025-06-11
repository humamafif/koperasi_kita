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
        Schema::create('tagihan_anggotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('jenis_tagihan', ['simpanan_wajib', 'angsuran_pinjaman']);
            $table->decimal('jumlah', 15, 2);
            $table->date('tanggal_jatuh_tempo');
            $table->enum('status', ['belum_bayar', 'menunggu_verifikasi', 'lunas'])->default('belum_bayar');
            $table->string('bukti_pembayaran')->nullable();
            $table->date('tanggal_pembayaran')->nullable();
            $table->foreignId('pinjaman_id')->nullable()->constrained('pinjamans')->nullOnDelete();
            $table->string('periode')->nullable(); // Format: YYYY-MM
            $table->text('keterangan')->nullable();
            $table->date('tanggal_verifikasi')->nullable();
            $table->string('diverifikasi_oleh')->nullable();
            $table->timestamps();

            // Index untuk performa query
            $table->index(['user_id', 'status']);
            $table->index(['jenis_tagihan', 'periode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tagihan_anggotas');
    }
};
