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
            $table->foreignId('bunga_id')->after('tenor')->nullable()->constrained('bunga_pinjamans');
            $table->foreignId('tenor_id')->after('tenor')->nullable()->constrained('tenor_pinjamans');

            $table->decimal('bunga', 5, 2)->nullable()->change();
            $table->integer('tenor')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pinjamans', function (Blueprint $table) {
            $table->dropForeign(['bunga_id']);
            $table->dropForeign(['tenor_id']);
            $table->dropColumn(['bunga_id', 'tenor_id']);

            $table->decimal('bunga', 5, 2)->nullable(false)->change();
            $table->integer('tenor')->nullable(false)->change();
        });
    }
};
