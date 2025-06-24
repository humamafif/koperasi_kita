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
            if (!Schema::hasColumn('pinjamans', 'angsuran_per_bulan')) {
                $table->decimal('angsuran_per_bulan', 15, 2)->nullable()->after('jumlah');
            }
            $table->decimal('total_bunga', 15, 2)->nullable()->after('angsuran_per_bulan');
            if (
                Schema::hasColumn('pinjamans', 'tenor_id') &&
                Schema::hasColumn('pinjamans', 'bunga') &&
                Schema::hasColumn('pinjamans', 'tenor')
            ) {
                $table->dropColumn(['bunga', 'tenor']);
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
            $table->dropColumn(['total_bunga']);

            if (!Schema::hasColumn('pinjamans', 'bunga') && !Schema::hasColumn('pinjamans', 'tenor')) {
                $table->decimal('bunga', 5, 2)->nullable();
                $table->integer('tenor')->nullable();
            }
        });
    }
};
