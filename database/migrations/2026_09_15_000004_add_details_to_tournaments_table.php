<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tournaments')) {
            Schema::table('tournaments', function (Blueprint $table) {
                if (!Schema::hasColumn('tournaments', 'rebuy_cost')) {
                    $table->decimal('rebuy_cost', 10, 2)->nullable()->default(10.00)->after('entry_fee');
                }
                if (!Schema::hasColumn('tournaments', 'number_of_rebuys')) {
                    $table->integer('number_of_rebuys')->nullable()->default(100)->after('rebuy_cost');
                }
                if (!Schema::hasColumn('tournaments', 'timezone_label')) {
                    $table->string('timezone_label')->nullable()->default('UTC+05:00')->after('end_date');
                }
                if (!Schema::hasColumn('tournaments', 'prize_distribution')) {
                    $table->json('prize_distribution')->nullable()->after('rules');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tournaments')) {
            Schema::table('tournaments', function (Blueprint $table) {
                $table->dropColumn(['rebuy_cost', 'number_of_rebuys', 'timezone_label', 'prize_distribution']);
            });
        }
    }
};
