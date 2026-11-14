<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Real pending orders (By Price / By Time).
     *
     * Before this table existed the "Pending Trade" toggle just placed an
     * instant trade, so a pending order was never stored anywhere.
     * Creating an order here only records the intent — no trade is placed
     * and no balance is touched until the trigger fires (see
     * PendingOrderExecutor, run by `pending:execute` and by the
     * opportunistic POST /pending-orders/check-due hook).
     */
    public function up(): void
    {
        if (!Schema::hasTable('pending_orders')) {
            Schema::create('pending_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
                // up = Buy, down = Sell (same vocabulary as trades).
                $table->string('direction');
                $table->decimal('investment', 10, 2);
                $table->integer('duration_seconds');
                // price = "By Price", time = "By Time".
                $table->string('order_type');
                // By Price only: execute when the market price crosses this.
                $table->decimal('trigger_price', 18, 8)->nullable();
                // By Time only: execute when this moment is reached.
                $table->timestamp('trigger_time')->nullable();
                // Snapshot of the user's account at creation (demo/live).
                // The trade is executed against the same account type.
                $table->string('account_type')->default('demo');
                // pending / executed / cancelled / expired
                $table->string('status')->default('pending');
                // The trade created when the order executed (null until then).
                $table->foreignId('trade_id')->nullable()->constrained('trades')->nullOnDelete();
                $table->timestamps();

                $table->index(['user_id', 'status']);
                $table->index(['status', 'order_type']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_orders');
    }
};
