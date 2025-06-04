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
        Schema::table('tenor_pinjamans', function (Blueprint $table) {
            $table->decimal('bunga', 5, 2)->after('nama');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenor_pinjamans', function (Blueprint $table) {
            $table->dropColumn('bunga');
        });
    }
};
