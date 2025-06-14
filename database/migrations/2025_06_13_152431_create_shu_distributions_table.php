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
        Schema::create('shu_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->year('tahun');
            $table->decimal('total_simpanan', 12, 2);
            $table->decimal('persentase_kontribusi', 5, 4);
            $table->decimal('jumlah_shu', 12, 2);
            $table->enum('status', ['pending', 'dibagikan', 'ditolak'])->default('pending');
            $table->timestamp('tanggal_distribusi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shu_distributions');
    }
};
