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
        Schema::table('ipo_sales', function (Blueprint $table) {
            $table->foreignId('ipo_account_placement_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->foreignId('ipo_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ipo_sales', function (Blueprint $table) {
            $table->dropForeign(['ipo_account_placement_id']);
            $table->dropColumn('ipo_account_placement_id');
            $table->foreignId('ipo_id')->nullable(false)->change();
        });
    }
};
