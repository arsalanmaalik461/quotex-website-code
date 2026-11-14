<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('symbol');
            $table->string('name');
            $table->integer('payout_percent')->default(85);
            $table->enum('type', ['forex', 'crypto', 'commodity', 'index'])->default('forex');
            $table->boolean('is_otc')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('flag')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
