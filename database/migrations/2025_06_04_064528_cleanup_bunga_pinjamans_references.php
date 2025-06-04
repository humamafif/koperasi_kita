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
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::table('pinjamans', function (Blueprint $table) {
            if (Schema::hasColumn('pinjamans', 'bunga_id')) {
                $table->dropForeign(['bunga_id']);
                $table->dropColumn('bunga_id');
            }
        });
        Schema::dropIfExists('bunga_pinjamans');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function down(): void
    {
        Schema::create('bunga_pinjamans', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->decimal('nilai', 5, 2)->comment('dalam persen');
            $table->text('keterangan')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::table('pinjamans', function (Blueprint $table) {
            $table->foreignId('bunga_id')->nullable()->constrained('bunga_pinjamans');
        });
    }
};
