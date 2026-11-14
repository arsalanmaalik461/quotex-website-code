<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('app_settings')) {
            Schema::create('app_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('payment_methods')) {
            Schema::table('payment_methods', function (Blueprint $table) {
                if (!Schema::hasColumn('payment_methods', 'provider')) {
                    $table->string('provider')->default('manual')->after('type')->index();
                }
                if (!Schema::hasColumn('payment_methods', 'provider_code')) {
                    $table->string('provider_code')->nullable()->after('provider')->index();
                }
                if (!Schema::hasColumn('payment_methods', 'trade_type')) {
                    $table->string('trade_type')->nullable()->after('provider_code');
                }
                if (!Schema::hasColumn('payment_methods', 'logo_path')) {
                    $table->string('logo_path')->nullable()->after('icon');
                }
            });
        }

        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                if (!Schema::hasColumn('transactions', 'provider')) {
                    $table->string('provider')->nullable()->after('payment_method_id')->index();
                }
                if (!Schema::hasColumn('transactions', 'provider_method')) {
                    $table->string('provider_method')->nullable()->after('provider');
                }
                if (!Schema::hasColumn('transactions', 'merchant_order_id')) {
                    $table->string('merchant_order_id')->nullable()->after('provider_method')->unique();
                }
                if (!Schema::hasColumn('transactions', 'provider_reference')) {
                    $table->string('provider_reference')->nullable()->after('merchant_order_id');
                }
                if (!Schema::hasColumn('transactions', 'provider_status')) {
                    $table->string('provider_status')->nullable()->after('provider_reference');
                }
                if (!Schema::hasColumn('transactions', 'provider_payload')) {
                    $table->json('provider_payload')->nullable()->after('provider_status');
                }
                if (!Schema::hasColumn('transactions', 'gateway_amount')) {
                    $table->decimal('gateway_amount', 15, 2)->nullable()->after('converted_amount');
                }
                if (!Schema::hasColumn('transactions', 'gateway_currency')) {
                    $table->string('gateway_currency', 20)->nullable()->after('gateway_amount');
                }
                if (!Schema::hasColumn('transactions', 'gateway_conversion_rate')) {
                    $table->decimal('gateway_conversion_rate', 15, 4)->nullable()->after('gateway_currency');
                }
                if (!Schema::hasColumn('transactions', 'processed_at')) {
                    $table->timestamp('processed_at')->nullable()->after('provider_payload');
                }
            });
        }

        DB::table('app_settings')->updateOrInsert(
            ['key' => 'lgpay.enabled'],
            ['value' => DB::table('app_settings')->where('key', 'lgpay.enabled')->value('value') ?? '0', 'updated_at' => now(), 'created_at' => now()]
        );

        $methods = [
            [
                'provider_code' => 'easypaisa',
                'name' => 'EasyPaisa',
                'icon' => 'easypaisa',
                'trade_type' => 'PKRPH-EASY',
                'sort_order' => -20,
            ],
            [
                'provider_code' => 'jazzcash',
                'name' => 'JazzCash',
                'icon' => 'jazzcash',
                'trade_type' => 'PKRPH',
                'sort_order' => -10,
            ],
        ];

        if (Schema::hasTable('payment_methods')) {
            foreach ($methods as $method) {
                DB::table('payment_methods')->updateOrInsert(
                    ['provider' => 'lgpay', 'provider_code' => $method['provider_code']],
                    [
                        'type' => 'deposit',
                        'name' => $method['name'],
                        'icon' => $method['icon'],
                        'currency' => 'PKR',
                        'conversion_rate' => 280.0000,
                        'wallet_address' => null,
                        'description' => 'Online payment via LG Pay.',
                        'min_amount' => 10.00,
                        'max_amount' => 50000.00,
                        'is_active' => false,
                        'sort_order' => $method['sort_order'],
                        'trade_type' => $method['trade_type'],
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            foreach (['processed_at', 'provider_payload', 'provider_status', 'provider_reference', 'merchant_order_id', 'provider_method', 'provider'] as $column) {
                if (Schema::hasColumn('transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('payment_methods', function (Blueprint $table) {
            foreach (['trade_type', 'provider_code', 'provider'] as $column) {
                if (Schema::hasColumn('payment_methods', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
