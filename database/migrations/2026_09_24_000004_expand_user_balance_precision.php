<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE users MODIFY demo_balance DECIMAL(24,2) NOT NULL DEFAULT 10000.00');
        DB::statement('ALTER TABLE users MODIFY live_balance DECIMAL(24,2) NOT NULL DEFAULT 0.00');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE users MODIFY demo_balance DECIMAL(15,2) NOT NULL DEFAULT 10000.00');
        DB::statement('ALTER TABLE users MODIFY live_balance DECIMAL(15,2) NOT NULL DEFAULT 0.00');
    }
};
