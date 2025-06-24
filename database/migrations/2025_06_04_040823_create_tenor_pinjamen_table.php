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
        Schema::create('tenor_pinjamans', function (Blueprint $table) {
            $table->id();
            $table->integer('durasi')->comment('dalam bulan');
            $table->string('nama');
            $table->text('keterangan')->nullable();
            $table->boolean('aktif')->default(true);
            $table->unique('durasi');
            $table->engine = 'InnoDB';
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenor_pinjamans');
    }
};
