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
        DB::table('koperasi_settings')->insert([
            [
                'key' => 'tagihan_cutoff_day',
                'value' => '20',
                'description' => 'Batas hari dalam bulan untuk menentukan apakah tagihan dibuat bulan ini atau bulan depan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'tagihan_due_day_new_member',
                'value' => '25',
                'description' => 'Hari jatuh tempo untuk tagihan Simpanan Wajib',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'tagihan_due_day_regular',
                'value' => '10',
                'description' => 'Hari jatuh tempo untuk tagihan bulanan reguler',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('koperasi_settings')
            ->whereIn('key', ['tagihan_cutoff_day', 'tagihan_due_day_new_member', 'tagihan_due_day_regular'])
            ->delete();
    }
};
