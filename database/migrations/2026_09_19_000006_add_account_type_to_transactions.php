<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('transactions')) {
            return;
        }

        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'account_type')) {
                $table->enum('account_type', ['demo', 'live'])->nullable()->after('user_id')->index();
            }
        });

        $backfillQuery = DB::table('transactions')
            ->whereNull('account_type')
            ->whereIn('type', ['deposit', 'withdrawal'])
            ->where(function ($query) {
                $query->whereNull('method')
                    ->orWhere('method', '!=', 'admin');
            });

        if (Schema::hasColumn('transactions', 'provider')) {
            $backfillQuery->orWhere(function ($query) {
                $query->whereNull('account_type')
                    ->where('provider', 'lgpay');
            });
        }

        $backfillQuery->update(['account_type' => 'live']);
    }

    public function down(): void
    {
        if (!Schema::hasTable('transactions') || !Schema::hasColumn('transactions', 'account_type')) {
            return;
        }

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('account_type');
        });
    }
};
