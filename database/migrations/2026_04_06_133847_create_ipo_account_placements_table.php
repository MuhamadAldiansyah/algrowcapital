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
        Schema::create('ipo_account_placements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ipo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mitra_account_id')->constrained()->cascadeOnDelete();
            $table->decimal('capital_allocated', 20, 2);
            $table->integer('est_lot')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ipo_account_placements');
    }
};
