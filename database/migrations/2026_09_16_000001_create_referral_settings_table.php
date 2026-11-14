<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_enabled')->default(true);
            $table->decimal('join_commission_amount', 10, 2)->default(0.00); // Fixed bonus on sign up ($)
            $table->decimal('deposit_commission_percent', 5, 2)->default(5.00); // % on deposit
            $table->decimal('trade_win_commission_percent', 5, 2)->default(3.00); // % of net profit on trade win
            $table->decimal('trade_loss_commission_percent', 5, 2)->default(10.00); // % of loss on trade loss (RevShare)
            $table->decimal('min_trade_amount', 10, 2)->default(1.00);
            $table->boolean('auto_credit_to_balance')->default(true);
            $table->text('terms_and_conditions')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_settings');
    }
};
