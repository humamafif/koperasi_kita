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
        Schema::table('shu_biayas', function (Blueprint $table) {
            $table->float('persentase_dana_cadangan')->default(40)->after('total_shu');
            $table->float('persentase_jasa_usaha')->default(20)->after('persentase_dana_cadangan');
            $table->float('persentase_jasa_modal')->default(20)->after('persentase_jasa_usaha');
            $table->float('persentase_jasa_pinjaman')->default(20)->after('persentase_jasa_modal');
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shu_biayas', function (Blueprint $table) {
            $table->dropColumn([
                'persentase_dana_cadangan',
                'persentase_jasa_usaha',
                'persentase_jasa_modal',
                'persentase_jasa_pinjaman'
            ]);
        });
    }
};
