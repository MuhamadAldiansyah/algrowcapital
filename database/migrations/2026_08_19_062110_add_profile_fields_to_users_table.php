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
            if (!Schema::hasColumn('users', 'sekuritas')) {
                $table->string('sekuritas')->nullable();
            }
            if (!Schema::hasColumn('users', 'password_sekuritas')) {
                $table->string('password_sekuritas')->nullable();
            }
            if (!Schema::hasColumn('users', 'pin_sekuritas')) {
                $table->string('pin_sekuritas')->nullable();
            }
            if (!Schema::hasColumn('users', 'bank')) {
                $table->string('bank')->nullable();
            }
            if (!Schema::hasColumn('users', 'no_rek')) {
                $table->string('no_rek')->nullable();
            }
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
