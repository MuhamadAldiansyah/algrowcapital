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
        Schema::table('ipos', function (Blueprint $table) {
            $table->timestamp('profit_distributed_at')->nullable()->after('ipo_date');
            $table->decimal('mitra_fee_pct', 5, 2)->nullable()->after('profit_distributed_at');
            $table->decimal('platform_fee_pct', 5, 2)->nullable()->after('mitra_fee_pct');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ipos', function (Blueprint $table) {
            $table->dropColumn(['profit_distributed_at', 'mitra_fee_pct', 'platform_fee_pct']);
        });
    }
};
