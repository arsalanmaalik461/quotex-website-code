<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Market purchases: one row per promo code a user buys.
 *
 * - unique (user_id, promo_code_id): one purchase per user per code, so
 *   a code can never be bought twice. This also stops the balance-bonus
 *   codes (pay $X, get $Y > $X instantly) from becoming a money printer.
 * - unique idempotency_key: the client sends one key per confirm-modal
 *   open; a rapid double-click replays the same key and the second
 *   submit returns the first purchase instead of charging again. This
 *   is a purchase guard only — trade Buy/Sell stays unguarded per the
 *   user's rule.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('market_purchases')) {
            Schema::create('market_purchases', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('promo_code_id')->constrained('promo_codes')->cascadeOnDelete();
                // Amount actually charged from the live balance (0 for free codes).
                $table->decimal('price_paid', 10, 2)->default(0);
                // Client-generated key, one per confirm-modal open.
                $table->string('idempotency_key', 64)->unique();
                $table->string('status')->default('completed');
                // Human-readable record of what the purchase granted (or that
                // nothing grantable exists for this category yet).
                $table->text('granted_summary')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'promo_code_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('market_purchases');
    }
};
