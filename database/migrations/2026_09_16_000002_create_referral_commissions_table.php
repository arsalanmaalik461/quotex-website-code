<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referrer_id');
            $table->unsignedBigInteger('referred_user_id');
            $table->enum('type', ['trade_win', 'trade_loss', 'deposit', 'join'])->default('trade_win');
            $table->unsignedBigInteger('trade_id')->nullable();
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->decimal('base_amount', 15, 2)->default(0.00); // profit, loss, or deposit amount
            $table->decimal('commission_percent', 5, 2)->default(0.00); // % applied
            $table->decimal('commission_amount', 15, 2)->default(0.00); // final commission earned in $
            $table->enum('status', ['credited', 'pending', 'cancelled'])->default('credited');
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->foreign('referrer_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('referred_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('trade_id')->references('id')->on('trades')->nullOnDelete();
            $table->foreign('transaction_id')->references('id')->on('transactions')->nullOnDelete();

            $table->index(['referrer_id', 'created_at']);
            $table->index(['referred_user_id']);
            $table->index(['type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_commissions');
    }
};
