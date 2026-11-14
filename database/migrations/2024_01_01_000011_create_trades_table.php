<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->enum('direction', ['up', 'down']);
            $table->decimal('investment', 15, 2);
            $table->decimal('payout', 15, 2)->default(0);
            $table->decimal('open_price', 20, 8)->default(0);
            $table->decimal('close_price', 20, 8)->nullable();
            $table->integer('duration_seconds')->default(60);
            $table->enum('result', ['win', 'loss', 'pending'])->default('pending');
            $table->enum('account_type', ['demo', 'live'])->default('demo');
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};
