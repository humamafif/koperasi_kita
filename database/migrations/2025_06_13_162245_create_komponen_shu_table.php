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
        Schema::create('komponen_shu', function (Blueprint $table) {
            $table->id();
            $table->year('tahun');
            $table->decimal('saldo_koperasi', 12, 2);
            $table->decimal('biaya_operasional', 12, 2);
            $table->decimal('pajak', 12, 2);
            $table->decimal('dana_cadangan', 12, 2);
            $table->decimal('biaya_lainnya', 12, 2)->default(0);
            $table->text('keterangan_biaya')->nullable();
            $table->decimal('total_biaya', 12, 2);
            $table->decimal('total_shu', 12, 2);
            $table->engine = 'InnoDB';
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('komponen_shu');
    }
};
