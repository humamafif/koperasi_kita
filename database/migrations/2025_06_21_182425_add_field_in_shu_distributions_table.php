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
            $table->decimal('total_biaya_admin', 15, 2)->default(0)->after('total_simpanan');
            $table->decimal('total_bunga_pinjaman', 15, 2)->default(0)->after('total_biaya_admin');
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shu_distributions', function (Blueprint $table) {
            $table->dropColumn('total_biaya_admin');
            $table->dropColumn('total_bunga_pinjaman');
        });
    }
};
