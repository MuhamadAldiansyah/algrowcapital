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
        Schema::create('ipo_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ipo_id')->constrained()->cascadeOnDelete();
            $table->decimal('sell_price', 15, 2);
            $table->decimal('total_return', 20, 2)->default(0);
            $table->decimal('net_profit', 20, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ipo_sales');
    }
};
