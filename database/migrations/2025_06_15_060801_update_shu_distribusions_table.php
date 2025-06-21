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
            $table->boolean('is_claimed')->default(false)->after('status');
            $table->timestamp('claimed_at')->nullable()->after('is_claimed');
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shu_distributions', function (Blueprint $table) {
            $table->dropColumn(['is_claimed', 'claimed_at']);
        });
    }
};
