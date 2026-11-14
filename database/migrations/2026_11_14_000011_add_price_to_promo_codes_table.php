<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add a purchase price to promo_codes (Market items).
 *
 * The Market page sells promo codes, but the table never had a price, so
 * no purchase could ever be charged. Nullable: codes created before this
 * migration (or by the admin panel, which has no price field yet) have
 * NULL price, treated as $0.00 (free) by PromoCode::priceAmount().
 * The Market seeder backfills the seeded codes' prices where NULL.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            if (!Schema::hasColumn('promo_codes', 'price')) {
                $table->decimal('price', 10, 2)->nullable()->after('reward_value');
            }
        });
    }

    public function down(): void
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            if (Schema::hasColumn('promo_codes', 'price')) {
                $table->dropColumn('price');
            }
        });
    }
};
