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
        Schema::table('pinjamans', function (Blueprint $table) {
            // Periksa apakah kolom yang akan dibuat sudah ada
            if (!Schema::hasColumn('pinjamans', 'tenor_pinjaman_id') && Schema::hasColumn('pinjamans', 'tenor_id')) {
                $table->renameColumn('tenor_id', 'tenor_pinjaman_id');
            }

            // Tambahkan kolom total_bunga jika belum ada
            if (!Schema::hasColumn('pinjamans', 'total_bunga')) {
                $table->decimal('total_bunga', 15, 2)->nullable()->after('jumlah');
            }

            // Pastikan kolom angsuran_per_bulan ada
            if (!Schema::hasColumn('pinjamans', 'angsuran_per_bulan')) {
                $table->decimal('angsuran_per_bulan', 15, 2)->nullable()->after('jumlah');
            }
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pinjamans', function (Blueprint $table) {
            if (Schema::hasColumn('pinjamans', 'tenor_pinjaman_id')) {
                $table->renameColumn('tenor_pinjaman_id', 'tenor_id');
            }

            if (Schema::hasColumn('pinjamans', 'total_bunga')) {
                $table->dropColumn('total_bunga');
            }
        });
    }
};
