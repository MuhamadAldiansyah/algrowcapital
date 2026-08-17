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
        Schema::table('ipo_account_placements', function (Blueprint $table) {
            $table->decimal('mitra_share_pct', 5, 2)->default(50.00)->after('est_lot');
        });

        Schema::table('investor_fundings', function (Blueprint $table) {
            $table->decimal('share_pct', 5, 2)->default(50.00)->after('amount_funded');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ipo_account_placements', function (Blueprint $table) {
            $table->dropColumn('mitra_share_pct');
        });

        Schema::table('investor_fundings', function (Blueprint $table) {
            $table->dropColumn('share_pct');
        });
    }
};
