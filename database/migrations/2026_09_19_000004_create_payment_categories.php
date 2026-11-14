<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payment_categories')) {
            Schema::create('payment_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('icon')->nullable();
                $table->string('image_path')->nullable();
                $table->string('short_label')->nullable();
                $table->text('description')->nullable();
                $table->integer('sort_order')->default(0)->index();
                $table->boolean('is_active')->default(true)->index();
                $table->boolean('show_on_deposit')->default(true)->index();
                $table->string('country')->nullable()->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('payment_category_method')) {
            Schema::create('payment_category_method', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payment_category_id')->constrained()->cascadeOnDelete();
                $table->foreignId('payment_method_id')->constrained()->cascadeOnDelete();
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->unique(['payment_category_id', 'payment_method_id'], 'payment_category_method_unique');
                $table->index(['payment_category_id', 'sort_order'], 'payment_category_method_order_index');
            });
        }

        $now = now();
        $defaults = [
            ['name' => 'Popular', 'slug' => 'popular', 'icon' => 'fire', 'short_label' => 'Popular in your region', 'sort_order' => 1],
            ['name' => 'E-Pay', 'slug' => 'e-pay', 'icon' => 'wallet', 'short_label' => 'E-Pay', 'sort_order' => 2],
            ['name' => 'Banks', 'slug' => 'banks', 'icon' => 'card', 'short_label' => 'Banks', 'sort_order' => 3],
            ['name' => 'Crypto', 'slug' => 'crypto', 'icon' => 'coin', 'short_label' => 'Crypto', 'sort_order' => 4],
        ];

        foreach ($defaults as $category) {
            DB::table('payment_categories')->updateOrInsert(['slug' => $category['slug']], array_merge($category, [
                'is_active' => true,
                'show_on_deposit' => true,
                'updated_at' => $now,
            ]));
        }

        if (!Schema::hasTable('payment_methods')) {
            return;
        }

        $categories = DB::table('payment_categories')->pluck('id', 'slug');
        $methods = DB::table('payment_methods')->select('id', 'name', 'currency', 'provider', 'provider_code', 'sort_order')->get();

        foreach ($methods as $method) {
            $tokens = Str::lower(implode(' ', [
                $method->name,
                $method->currency,
                $method->provider,
                $method->provider_code,
            ]));

            $slugs = ['popular'];

            if (Str::contains($tokens, ['easy', 'easypaisa', 'jazz', 'jazzcash', 'raast', 'cashmaal'])) {
                $slugs[] = 'e-pay';
            }

            if (Str::contains($tokens, ['bank', 'card', 'transfer', 'account'])) {
                $slugs[] = 'banks';
            }

            if (Str::contains($tokens, ['crypto', 'binance', 'usdt', 'btc', 'bitcoin', 'eth', 'ethereum', 'ltc', 'trx', 'trc', 'bep'])) {
                $slugs[] = 'crypto';
            }

            foreach (array_unique($slugs) as $slug) {
                if (!$categories->has($slug)) {
                    continue;
                }

                DB::table('payment_category_method')->insertOrIgnore([
                    'payment_category_id' => $categories[$slug],
                    'payment_method_id' => $method->id,
                    'sort_order' => (int) ($method->sort_order ?? 0),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_category_method');
        Schema::dropIfExists('payment_categories');
    }
};
