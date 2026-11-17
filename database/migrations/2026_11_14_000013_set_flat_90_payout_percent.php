<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Flat 90% payout policy (user decision, 2026-11-14): whoever
     * wins gets 90% profit — a $1 win credits $1.90 total (+$0.90
     * profit), never double ($2.00). Previously the payout varied
     * per pair (seeded 20–94%, column default 85%), so:
     *
     *  - assets.payout_percent column default 85 → 90
     *  - every existing asset row is set to 90
     *  - signals.payout_percent column default 85.00 → 90.00
     *    (signals fall back to the asset payout, so the default
     *    must match or a signal with no stored payout would show 85%)
     *
     * The win formula itself is NOT changed here: placement still
     * credits investment × (1 + payout_percent/100)
     * (TradeController::placeTrade) and the early-sell offer still
     * prices off the same % (TradeResultService::earlySellOffer).
     * The admin's per-pair payout control (validation 1–99) is
     * left in place; this migration only sets the current values.
     */
    public function up(): void
    {
        if (Schema::hasTable('assets') && Schema::hasColumn('assets', 'payout_percent')) {
            DB::statement('ALTER TABLE assets MODIFY payout_percent INT NOT NULL DEFAULT 90');
            DB::table('assets')->update(['payout_percent' => 90]);
        }

        if (Schema::hasTable('signals') && Schema::hasColumn('signals', 'payout_percent')) {
            DB::statement('ALTER TABLE signals MODIFY payout_percent DECIMAL(5,2) NOT NULL DEFAULT 90.00');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('assets') && Schema::hasColumn('assets', 'payout_percent')) {
            DB::statement('ALTER TABLE assets MODIFY payout_percent INT NOT NULL DEFAULT 85');
        }

        if (Schema::hasTable('signals') && Schema::hasColumn('signals', 'payout_percent')) {
            DB::statement('ALTER TABLE signals MODIFY payout_percent DECIMAL(5,2) NOT NULL DEFAULT 85.00');
        }

        // The per-row data update is intentionally not reverted:
        // the pre-migration per-pair percentages are not recoverable
        // from this migration alone.
    }
};
