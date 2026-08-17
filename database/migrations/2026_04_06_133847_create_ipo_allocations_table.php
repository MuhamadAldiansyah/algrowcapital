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
        Schema::create('ipo_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ipo_account_placement_id')->constrained('ipo_account_placements')->cascadeOnDelete();
            $table->integer('lot_allocated');
            $table->decimal('final_price_ipo', 15, 2);
            $table->decimal('total_used', 20, 2)->default(0);
            $table->decimal('remaining_capital', 20, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ipo_allocations');
    }
};
