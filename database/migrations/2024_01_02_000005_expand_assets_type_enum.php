<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite does not support ALTER COLUMN for enums.
        // We recreate assets table with expanded type enum.
        // For MySQL this would be: ALTER TABLE assets MODIFY COLUMN type ENUM(...)
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: drop and recreate with string column instead of enum
            // (SQLite stores enums as strings anyway, just remove CHECK constraint)
            DB::statement('PRAGMA foreign_keys = OFF');

            DB::statement('
                CREATE TABLE assets_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    symbol VARCHAR(255) NOT NULL,
                    name VARCHAR(255) NOT NULL,
                    payout_percent INTEGER NOT NULL DEFAULT 85,
                    type VARCHAR(20) NOT NULL DEFAULT \'forex\',
                    is_otc BOOLEAN NOT NULL DEFAULT 0,
                    is_active BOOLEAN NOT NULL DEFAULT 1,
                    flag VARCHAR(255) NULL,
                    market_volume DECIMAL(20,2) NOT NULL DEFAULT 1000000,
                    trading_capital DECIMAL(20,2) NOT NULL DEFAULT 500000,
                    max_pump_percent DECIMAL(8,2) NOT NULL DEFAULT 5.00,
                    max_crash_percent DECIMAL(8,2) NOT NULL DEFAULT 5.00,
                    pump_duration_minutes INTEGER NOT NULL DEFAULT 15,
                    control_mode VARCHAR(10) NOT NULL DEFAULT \'auto\',
                    control_expires_at DATETIME NULL,
                    win_side VARCHAR(30) NOT NULL DEFAULT \'none\',
                    base_price_override DECIMAL(20,8) NULL,
                    category VARCHAR(255) NOT NULL DEFAULT \'forex\',
                    created_at DATETIME NULL,
                    updated_at DATETIME NULL
                )
            ');

            DB::statement('
                INSERT INTO assets_new 
                SELECT id, symbol, name, payout_percent, type, is_otc, is_active, flag,
                       COALESCE(market_volume, 1000000),
                       COALESCE(trading_capital, 500000),
                       COALESCE(max_pump_percent, 5),
                       COALESCE(max_crash_percent, 5),
                       COALESCE(pump_duration_minutes, 15),
                       COALESCE(control_mode, \'auto\'),
                       control_expires_at,
                       COALESCE(win_side, \'none\'),
                       base_price_override,
                       COALESCE(category, \'forex\'),
                       created_at, updated_at
                FROM assets
            ');

            DB::statement('DROP TABLE assets');
            DB::statement('ALTER TABLE assets_new RENAME TO assets');
            DB::statement('PRAGMA foreign_keys = ON');

        } else {
            // MySQL: just modify the column
            DB::statement("ALTER TABLE assets MODIFY COLUMN type ENUM('forex','crypto','commodity','index','otc','signal') NOT NULL DEFAULT 'forex'");
        }
    }

    public function down(): void
    {
        // Revert to original enum values (data with otc/signal type will be lost)
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE assets MODIFY COLUMN type ENUM('forex','crypto','commodity','index') NOT NULL DEFAULT 'forex'");
        }
    }
};
