<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payment_methods')) {
            Schema::create('payment_methods', function (Blueprint $table) {
                $table->id();
                $table->string('type')->default('both'); // deposit, withdrawal, both
                $table->string('name');
                $table->string('icon')->nullable();
                $table->string('currency')->default('PKR');
                $table->decimal('conversion_rate', 15, 4)->default(280.0000);
                $table->string('wallet_address')->nullable();
                $table->text('description')->nullable();
                $table->decimal('min_amount', 15, 2)->default(10.00);
                $table->decimal('max_amount', 15, 2)->default(50000.00);
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->index('type');
                $table->index('is_active');
            });

            // Seed default payment methods
            DB::table('payment_methods')->insert([
                [
                    'type' => 'both',
                    'name' => 'Easypaisa',
                    'icon' => '🟢',
                    'currency' => 'PKR',
                    'conversion_rate' => 280.0000,
                    'wallet_address' => '03001234567',
                    'description' => "Account Title: Official Trade\nAccount Number: 03001234567\nPlease send exact amount and upload screenshot or enter Transaction ID.",
                    'min_amount' => 10.00,
                    'max_amount' => 5000.00,
                    'is_active' => true,
                    'sort_order' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'type' => 'both',
                    'name' => 'JazzCash',
                    'icon' => '🔴',
                    'currency' => 'PKR',
                    'conversion_rate' => 280.0000,
                    'wallet_address' => '03211234567',
                    'description' => "Account Title: Official Trade\nAccount Number: 03211234567\nPlease send payment through JazzCash app and submit receipt or TRX ID.",
                    'min_amount' => 10.00,
                    'max_amount' => 5000.00,
                    'is_active' => true,
                    'sort_order' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'type' => 'both',
                    'name' => 'USDT (TRC-20)',
                    'icon' => '🪙',
                    'currency' => 'USDT',
                    'conversion_rate' => 1.0000,
                    'wallet_address' => 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t',
                    'description' => "Deposit USDT on TRON (TRC20) network only.\nAddress: TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t\nEnter Blockchain Hash / TxID or upload payment slip.",
                    'min_amount' => 10.00,
                    'max_amount' => 50000.00,
                    'is_active' => true,
                    'sort_order' => 3,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'type' => 'both',
                    'name' => 'Binance Pay',
                    'icon' => '🟡',
                    'currency' => 'USDT',
                    'conversion_rate' => 1.0000,
                    'wallet_address' => '93090578',
                    'description' => "Binance Pay ID: 93090578\nPlease send USDT and enter Binance Order ID or upload receipt screenshot.",
                    'min_amount' => 10.00,
                    'max_amount' => 25000.00,
                    'is_active' => true,
                    'sort_order' => 4,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'type' => 'both',
                    'name' => 'Bank Transfer (Meezan Bank)',
                    'icon' => '🏦',
                    'currency' => 'PKR',
                    'conversion_rate' => 280.0000,
                    'wallet_address' => '0101010239485761',
                    'description' => "Bank: Meezan Bank Ltd\nAccount Title: Trade Market Pvt\nAccount No: 0101010239485761\nIBAN: PK12MEZN0001010102394857\nUpload IBFT receipt or reference number.",
                    'min_amount' => 20.00,
                    'max_amount' => 100000.00,
                    'is_active' => true,
                    'sort_order' => 5,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        // Add proof and method details to transactions table
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'proof_image')) {
                $table->string('proof_image')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('transactions', 'transaction_id')) {
                $table->string('transaction_id')->nullable()->after('proof_image');
            }
            if (!Schema::hasColumn('transactions', 'wallet_address')) {
                $table->string('wallet_address')->nullable()->after('transaction_id');
            }
            if (!Schema::hasColumn('transactions', 'converted_amount')) {
                $table->decimal('converted_amount', 15, 2)->nullable()->after('wallet_address');
            }
            if (!Schema::hasColumn('transactions', 'currency')) {
                $table->string('currency', 20)->nullable()->after('converted_amount');
            }
            if (!Schema::hasColumn('transactions', 'payment_method_id')) {
                $table->unsignedBigInteger('payment_method_id')->nullable()->after('currency');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');

        Schema::table('transactions', function (Blueprint $table) {
            $cols = ['proof_image', 'transaction_id', 'wallet_address', 'converted_amount', 'currency', 'payment_method_id'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('transactions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
