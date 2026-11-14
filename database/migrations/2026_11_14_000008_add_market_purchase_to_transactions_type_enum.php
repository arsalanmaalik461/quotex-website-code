<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Widen transactions.type so Market purchases can be recorded.
 *
 * Before this migration the enum is
 * ('deposit','withdrawal','tournament_entry','bonus') (P0 migration
 * 2026_11_14_000006), while MarketPurchaseController inserts
 * type='market_purchase' — on MySQL that insert throws and the whole
 * purchase transaction rolls back.
 *
 * MySQL (production): real ALTER TABLE ... MODIFY COLUMN ... ENUM(...).
 * SQLite (local dev only): enums are enforced by a CHECK constraint and
 * SQLite cannot ALTER COLUMN, so the table is rebuilt with a VARCHAR
 * type column, following the pattern in
 * 2026_11_14_000006_widen_transactions_type_enum.php. The column list
 * below is the production schema from the live dump, so the rebuild
 * copies every column; if a column is missing the INSERT fails and the
 * migration aborts with the original table untouched.
 */
return new class extends Migration
{
    /** New enum members, in addition to the existing ones. */
    private const NEW_TYPES = "'deposit','withdrawal','tournament_entry','bonus','market_purchase'";

    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->rebuildForSqlite();
            return;
        }

        DB::statement(
            "ALTER TABLE transactions MODIFY COLUMN type ENUM(" . self::NEW_TYPES . ") NOT NULL"
        );
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // The SQLite rebuild widened `type` to a plain VARCHAR; leaving
            // it that wide on rollback loses nothing (no data is deleted).
            return;
        }

        DB::statement(
            "ALTER TABLE transactions MODIFY COLUMN type ENUM('deposit','withdrawal','tournament_entry','bonus') NOT NULL"
        );
    }

    private function rebuildForSqlite(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        DB::statement('
            CREATE TABLE transactions_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                account_type VARCHAR(10) NULL,
                type VARCHAR(20) NOT NULL,
                amount DECIMAL(15,2) NOT NULL,
                method VARCHAR(255) NOT NULL DEFAULT \'card\',
                status VARCHAR(20) NOT NULL DEFAULT \'pending\',
                notes TEXT NULL,
                proof_image VARCHAR(255) NULL,
                transaction_id VARCHAR(255) NULL,
                wallet_address VARCHAR(255) NULL,
                converted_amount DECIMAL(15,2) NULL,
                gateway_amount DECIMAL(15,2) NULL,
                gateway_currency VARCHAR(20) NULL,
                gateway_conversion_rate DECIMAL(15,4) NULL,
                currency VARCHAR(20) NULL,
                payment_method_id INTEGER NULL,
                provider VARCHAR(255) NULL,
                provider_method VARCHAR(255) NULL,
                merchant_order_id VARCHAR(255) NULL,
                provider_reference VARCHAR(255) NULL,
                provider_status VARCHAR(255) NULL,
                provider_payload TEXT NULL,
                processed_at DATETIME NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
            )
        ');

        DB::statement('
            INSERT INTO transactions_new
            SELECT id, user_id, account_type, type, amount, method, status,
                   notes, proof_image, transaction_id, wallet_address,
                   converted_amount, gateway_amount, gateway_currency,
                   gateway_conversion_rate, currency, payment_method_id,
                   provider, provider_method, merchant_order_id,
                   provider_reference, provider_status, provider_payload,
                   processed_at, created_at, updated_at
            FROM transactions
        ');

        DB::statement('DROP TABLE transactions');
        DB::statement('ALTER TABLE transactions_new RENAME TO transactions');
        DB::statement('PRAGMA foreign_keys = ON');
    }
};
