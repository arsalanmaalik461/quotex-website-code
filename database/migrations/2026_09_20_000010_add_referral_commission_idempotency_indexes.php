<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('referral_commissions')) {
            return;
        }

        $this->dedupeByColumn('trade_id');
        $this->dedupeByColumn('transaction_id');

        Schema::table('referral_commissions', function (Blueprint $table) {
            $table->unique('trade_id', 'referral_commissions_trade_id_unique');
            $table->unique('transaction_id', 'referral_commissions_transaction_id_unique');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('referral_commissions')) {
            return;
        }

        Schema::table('referral_commissions', function (Blueprint $table) {
            $table->dropUnique('referral_commissions_trade_id_unique');
            $table->dropUnique('referral_commissions_transaction_id_unique');
        });
    }

    private function dedupeByColumn(string $column): void
    {
        $duplicates = DB::table('referral_commissions')
            ->select($column)
            ->whereNotNull($column)
            ->groupBy($column)
            ->havingRaw('COUNT(*) > 1')
            ->pluck($column);

        foreach ($duplicates as $value) {
            $keepId = DB::table('referral_commissions')
                ->where($column, $value)
                ->orderBy('id')
                ->value('id');

            DB::table('referral_commissions')
                ->where($column, $value)
                ->where('id', '!=', $keepId)
                ->delete();
        }
    }
};
