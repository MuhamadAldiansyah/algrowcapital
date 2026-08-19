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
        Schema::table('users', function (Blueprint $table) {
            $table->string('sekuritas')->nullable();
            $table->string('password_sekuritas')->nullable();
            $table->string('pin_sekuritas')->nullable();
            $table->string('bank')->nullable();
            $table->string('no_rek')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'sekuritas',
                'password_sekuritas',
                'pin_sekuritas',
                'bank',
                'no_rek',
            ]);
        });
    }
};
