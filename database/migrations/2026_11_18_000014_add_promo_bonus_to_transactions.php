<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * P0-3 (2026-11-18): promo bonus must be credited only when a
     * deposit is approved, not while it is still pending. That needs
     * the pending promo to be stored on the deposit row itself:
     *
     *  - promo_code: the code the user applied on the deposit.
     *  - promo_bonus_amount: the bonus computed at deposit time.
     *
     * AdminTransactionController::approveTransaction() reads these
     * columns and credits the bonus (idempotently); rejectTransaction()
     * reverses any bonus that was already credited under the old
     * immediate-credit behaviour, using the bonus row's notes
     * ("for deposit #<id>") as the link.
     */
    public function up(): void
    {
        if (!Schema::hasTable('transactions')) {
            return;
        }

        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'promo_code')) {
                $table->string('promo_code', 50)->nullable()->after('payment_method_id');
            }
            if (!Schema::hasColumn('transactions', 'promo_bonus_amount')) {
                $table->decimal('promo_bonus_amount', 15, 2)->default(0)->after('promo_code');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('transactions')) {
            return;
        }

        Schema::table('transactions', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('transactions', 'promo_code')) {
                $columns[] = 'promo_code';
            }
            if (Schema::hasColumn('transactions', 'promo_bonus_amount')) {
                $columns[] = 'promo_bonus_amount';
            }
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
