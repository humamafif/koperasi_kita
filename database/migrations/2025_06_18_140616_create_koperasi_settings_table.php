<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('koperasi_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default values
        DB::table('koperasi_settings')->insert([
            [
                'key' => 'simpanan_pokok_amount',
                'value' => '100000',
                'description' => 'Jumlah simpanan pokok untuk anggota baru (Rp)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'simpanan_wajib_amount',
                'value' => '50000',
                'description' => 'Jumlah simpanan wajib bulanan (Rp)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'biaya_admin_percent',
                'value' => '1.5',
                'description' => 'Persentase biaya admin untuk transaksi produk (%)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('koperasi_settings');
    }
};
