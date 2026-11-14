<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('demo_balance', 15, 2)->default(10000.00)->after('email');
            $table->decimal('live_balance', 15, 2)->default(0.00)->after('demo_balance');
            $table->string('country')->nullable()->after('live_balance');
            $table->enum('account_type', ['demo', 'live'])->default('demo')->after('country');
            $table->boolean('email_verified')->default(false)->after('account_type');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['demo_balance', 'live_balance', 'country', 'account_type', 'email_verified']);
        });
    }
};
