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
        if (Schema::hasTable('mitra_accounts') && !Schema::hasColumn('mitra_accounts', 'phone')) {
            Schema::table('mitra_accounts', function (Blueprint $table) {
                $table->string('phone')->nullable()->after('device');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('mitra_accounts') && Schema::hasColumn('mitra_accounts', 'phone')) {
            Schema::table('mitra_accounts', function (Blueprint $table) {
                $table->dropColumn('phone');
            });
        }
    }
};

