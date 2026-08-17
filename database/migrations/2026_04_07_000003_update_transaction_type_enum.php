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
        // Enums in MySQL are tricky to update. Using a raw statement if needed, 
        // but for SQLite/standard Laravel let's just re-declare the column.
        Schema::table('investor_transactions', function (Blueprint $table) {
            $table->string('type')->change(); // Temporary change to string to allow any value
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investor_transactions', function (Blueprint $table) {
            $table->enum('type', ['DEPOSIT', 'WITHDRAW'])->change();
        });
    }
};
