<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nik', 20)->nullable();
            $table->string('alamat')->nullable();
            $table->string('no_telepon')->nullable();
            $table->boolean('is_anggota_tetap')->default(false);
            $table->date('tanggal_menjadi_anggota_tetap')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nik',
                'alamat',
                'no_telepon',
                'is_anggota_tetap',
                'tanggal_menjadi_anggota_tetap'
            ]);
        });
    }
};
