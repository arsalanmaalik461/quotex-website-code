<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('transactions')) {
            return;
        }

        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'gateway_amount')) {
                $table->decimal('gateway_amount', 15, 2)->nullable()->after('converted_amount');
            }
            if (!Schema::hasColumn('transactions', 'gateway_currency')) {
                $table->string('gateway_currency', 20)->nullable()->after('gateway_amount');
            }
            if (!Schema::hasColumn('transactions', 'gateway_conversion_rate')) {
                $table->decimal('gateway_conversion_rate', 15, 4)->nullable()->after('gateway_currency');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('transactions')) {
            return;
        }

        Schema::table('transactions', function (Blueprint $table) {
            foreach (['gateway_conversion_rate', 'gateway_currency', 'gateway_amount'] as $column) {
                if (Schema::hasColumn('transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
