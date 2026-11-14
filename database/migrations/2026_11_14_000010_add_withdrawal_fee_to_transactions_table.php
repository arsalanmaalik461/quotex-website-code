<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Withdrawal-fee columns for the 3% fee from the 3rd same-day
     * withdrawal (see AccountController::processWithdrawal):
     *
     *  - fee_amount: the fee charged on this withdrawal (0 for the
     *    first 2 same-day withdrawals, 3% of amount from the 3rd on).
     *  - net_amount: amount actually sent to the user (amount - fee).
     *
     * The full requested amount is still debited from the live
     * balance at request time; the fee is taken out of what is sent.
     *
     * Also aligns the seeded crypto payment methods with the $50
     * crypto minimum stated in the withdrawal FAQ: any crypto-named
     * method whose min_amount is below $50 is raised to $50, so the
     * "Min. $..." shown on the deposit page matches what the server
     * enforces (the server also floors crypto at $50 regardless,
     * via PaymentMethod::effectiveMinAmount()).
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('fee_amount', 15, 2)->default(0)->after('amount');
            $table->decimal('net_amount', 15, 2)->nullable()->after('fee_amount');
        });

        if (Schema::hasTable('payment_methods') && Schema::hasColumn('payment_methods', 'min_amount')) {
            DB::table('payment_methods')
                ->where('min_amount', '<', 50)
                ->where(function ($query) {
                    foreach (['%crypto%', '%btc%', '%bitcoin%', '%eth%', '%usdt%', '%usdc%', '%ltc%', '%xrp%', '%doge%', '%sol%', '%trx%', '%tron%', '%bnb%', '%binance%', '%ton%'] as $pattern) {
                        $query->orWhere('name', 'like', $pattern);
                    }
                })
                ->update(['min_amount' => 50]);
        }
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['fee_amount', 'net_amount']);
        });
    }
};
