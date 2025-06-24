<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('koperasi_settings', function (Blueprint $table) {
            DB::table('koperasi_settings')->insert([
                [
                    'key' => 'rekening_koperasi',
                    'value' => '1234567890',
                    'description' => 'Nomor rekening koperasi untuk pembayaran',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'key' => 'bank_koperasi',
                    'value' => 'BCA',
                    'description' => 'Nama bank rekening koperasi',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'key' => 'nama_pemilik_rekening',
                    'value' => 'Koperasi Kita',
                    'description' => 'Nama pemilik rekening koperasi',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('koperasi_settings', function (Blueprint $table) {
            DB::table('koperasi_settings')
                ->whereIn('key', ['rekening_koperasi', 'bank_koperasi', 'nama_pemilik_rekening'])
                ->delete();
        });
    }
};
