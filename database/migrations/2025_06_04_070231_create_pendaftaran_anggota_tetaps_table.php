<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendaftaran_anggota_tetaps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nik', 20);
            $table->string('alamat');
            $table->string('no_telepon');
            $table->string('bukti_pembayaran');
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->string('keterangan')->nullable();
            $table->date('tanggal_pengajuan');
            $table->date('tanggal_verifikasi')->nullable();
            $table->string('diverifikasi_oleh')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_anggota_tetaps');
    }
};
