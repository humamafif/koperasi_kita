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
            $table->decimal('persentase_jasa_usaha_adjusted', 8, 4)->nullable()->after('persentase_jasa_usaha');
            $table->decimal('persentase_jasa_modal_adjusted', 8, 4)->nullable()->after('persentase_jasa_modal');
            $table->decimal('persentase_jasa_pinjaman_adjusted', 8, 4)->nullable()->after('persentase_jasa_pinjaman');
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shu_biayas', function (Blueprint $table) {
            $table->dropColumn('persentase_jasa_usaha_adjusted');
            $table->dropColumn('persentase_jasa_modal_adjusted');
            $table->dropColumn('persentase_jasa_pinjaman_adjusted');
        });
    }
};
