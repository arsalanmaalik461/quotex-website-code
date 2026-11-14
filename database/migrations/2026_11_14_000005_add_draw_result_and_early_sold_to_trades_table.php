<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Workstream B (money math): make a draw storable and mark early sells.
 *
 * 1. `trades.result` is ENUM('win','loss','pending') — it has no draw/tie
 *    value at all, so the settlement code's tie path could never persist:
 *    on MySQL (production) storing 'tie' throws and the settlement
 *    transaction rolls back. Widen the enum with 'draw' and settle draws
 *    as 'draw' from now on.
 *
 * 2. Add `trades.early_sold` (default false), set when a trade is closed
 *    through the early-sell path. Profit maths needs to know whether the
 *    stored `payout` is the gross payout offered at placement or the
 *    early-sell buyout: for a normally settled loss the stored gross
 *    payout was never paid (0 was), while an early-sold loss was paid
 *    the buyout. See TradeResultService::creditedPayoutForSettled().
 *
 * Run on the server after deploy: php artisan migrate
 *
 * MySQL (production): ALTER TABLE ... MODIFY COLUMN + addColumn.
 * SQLite (local dev only): enums are CHECK constraints and SQLite
 * cannot ALTER COLUMN, so the table is rebuilt, following the pattern
 * in 2026_11_14_000006_widen_transactions_type_enum.php. The column
 * list below is the production schema, so the rebuild copies every
 * column; if a column is missing the INSERT fails and the migration
 * aborts with the original table untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->rebuildForSqlite();
            return;
        }

        DB::statement(
            "ALTER TABLE `trades` MODIFY `result` ENUM('win', 'loss', 'draw', 'pending') NOT NULL DEFAULT 'pending'"
        );

        if (!Schema::hasColumn('trades', 'early_sold')) {
            Schema::table('trades', function (Blueprint $table) {
                $table->boolean('early_sold')->default(false)->after('payout');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('trades', 'early_sold')) {
            Schema::table('trades', function (Blueprint $table) {
                $table->dropColumn('early_sold');
            });
        }

        if (DB::getDriverName() === 'sqlite') {
            // The SQLite rebuild widened `result` to a plain VARCHAR;
            // leaving it that wide on rollback loses nothing.
            return;
        }

        // Only safe when no 'draw' rows remain; a stale draw row makes
        // this statement fail, which is the safe outcome.
        DB::statement(
            "ALTER TABLE `trades` MODIFY `result` ENUM('win', 'loss', 'pending') NOT NULL DEFAULT 'pending'"
        );
    }

    private function rebuildForSqlite(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        DB::statement('
            CREATE TABLE trades_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                asset_id INTEGER NOT NULL,
                direction VARCHAR(10) NOT NULL,
                investment DECIMAL(15,2) NOT NULL,
                payout DECIMAL(15,2) NOT NULL DEFAULT 0,
                early_sold TINYINT(1) NOT NULL DEFAULT 0,
                open_price DECIMAL(20,8) NOT NULL DEFAULT 0,
                close_price DECIMAL(20,8) NULL,
                duration_seconds INTEGER NOT NULL DEFAULT 60,
                result VARCHAR(10) NOT NULL DEFAULT \'pending\',
                account_type VARCHAR(10) NOT NULL DEFAULT \'demo\',
                opened_at DATETIME NULL,
                closed_at DATETIME NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                admin_override VARCHAR(20) NOT NULL DEFAULT \'none\',
                spike_injected TINYINT(1) NOT NULL DEFAULT 0,
                override_set_at DATETIME NULL,
                FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
                FOREIGN KEY (asset_id) REFERENCES assets (id) ON DELETE CASCADE
            )
        ');

        DB::statement('
            INSERT INTO trades_new
            (id, user_id, asset_id, direction, investment, payout,
             open_price, close_price, duration_seconds, result,
             account_type, opened_at, closed_at, created_at, updated_at,
             admin_override, spike_injected, override_set_at)
            SELECT id, user_id, asset_id, direction, investment, payout,
                   open_price, close_price, duration_seconds, result,
                   account_type, opened_at, closed_at, created_at, updated_at,
                   admin_override, spike_injected, override_set_at
            FROM trades
        ');

        DB::statement('DROP TABLE trades');
        DB::statement('ALTER TABLE trades_new RENAME TO trades');
        DB::statement('PRAGMA foreign_keys = ON');
    }
};
