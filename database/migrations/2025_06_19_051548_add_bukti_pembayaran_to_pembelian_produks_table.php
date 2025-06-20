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
        Schema::table('pembelian_produks', function (Blueprint $table) {
            $table->string('bukti_pembayaran')->nullable()->after('biaya_admin');
            $table->enum('status_pembayaran', ['belum_bayar', 'menunggu_verifikasi', 'terverifikasi'])->default('belum_bayar')->after('bukti_pembayaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembelian_produks', function (Blueprint $table) {
            $table->dropColumn('bukti_pembayaran');
            $table->dropColumn('status_pembayaran');
        });
    }
};
