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
        Schema::create('mitra_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investor_id')->constrained()->cascadeOnDelete();
            $table->string('owner_name');
            $table->string('platform');
            $table->string('username');
            $table->string('password');
            $table->string('pin')->nullable();
            $table->string('nik')->nullable();
            $table->string('bank_rdn')->nullable();
            $table->string('rdn_account')->nullable();
            $table->string('personal_bank')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->string('device')->nullable();
            $table->string('browser')->nullable();
            $table->string('chrome_profile')->nullable();
            $table->string('location')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mitra_accounts');
    }
};
