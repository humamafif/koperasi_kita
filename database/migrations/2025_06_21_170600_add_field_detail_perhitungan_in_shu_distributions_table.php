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
        Schema::table('shu_distributions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->json('detail_perhitungan')->nullable()->after('jumlah_shu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shu_distributions', function (Blueprint $table) {
            $table->dropColumn('detail_perhitungan');
        });
    }
};
