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
            $table->decimal('biaya_admin', 15, 2)->default(0)->after('total');
            $table->decimal('total_penjual', 15, 2)->default(0)->after('biaya_admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembelian_produks', function (Blueprint $table) {
            $table->dropColumn(['biaya_admin', 'total_penjual']);
        });
    }
};
