<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('investor_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investor_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 20, 2); 
            $table->enum('type', ['DEPOSIT', 'WITHDRAW']);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Data Migration: Move existing total_capital to transactions
        $investors = DB::table('investors')->get();
        foreach ($investors as $investor) {
            if ($investor->total_capital > 0) {
                DB::table('investor_transactions')->insert([
                    'investor_id' => $investor->id,
                    'amount' => $investor->total_capital,
                    'type' => 'DEPOSIT',
                    'description' => 'Initial Capital (Migration)',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        Schema::table('investors', function (Blueprint $table) {
            $table->dropColumn('total_capital');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investors', function (Blueprint $table) {
            $table->decimal('total_capital', 20, 2)->default(0);
        });

        // Reverse Data Migration
        $transactions = DB::table('investor_transactions')->where('type', 'DEPOSIT')->get();
        foreach ($transactions as $tx) {
            DB::table('investors')->where('id', $tx->investor_id)->increment('total_capital', $tx->amount);
        }

        Schema::dropIfExists('investor_transactions');
    }
};
