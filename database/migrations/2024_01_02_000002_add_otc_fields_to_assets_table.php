<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->decimal('market_volume', 20, 2)->default(1000000)->after('flag');
            $table->decimal('trading_capital', 20, 2)->default(500000)->after('market_volume');
            $table->decimal('max_pump_percent', 8, 2)->default(5.00)->after('trading_capital');
            $table->decimal('max_crash_percent', 8, 2)->default(5.00)->after('max_pump_percent');
            $table->integer('pump_duration_minutes')->default(15)->after('max_crash_percent');
            $table->enum('control_mode', ['auto', 'pump', 'crash'])->default('auto')->after('pump_duration_minutes');
            $table->timestamp('control_expires_at')->nullable()->after('control_mode');
            $table->enum('win_side', [
                'none',
                'majority_investment',
                'minority_investment',
                'majority_users',
                'minority_users'
            ])->default('none')->after('control_expires_at');
            $table->decimal('base_price_override', 20, 8)->nullable()->after('win_side');
            $table->string('category')->default('forex')->after('base_price_override');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn([
                'market_volume', 'trading_capital', 'max_pump_percent',
                'max_crash_percent', 'pump_duration_minutes', 'control_mode',
                'control_expires_at', 'win_side', 'base_price_override', 'category'
            ]);
        });
    }
};
