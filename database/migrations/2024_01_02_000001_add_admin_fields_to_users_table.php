<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('email_verified');
            $table->boolean('is_blocked')->default(false)->after('is_admin');
            $table->string('referral_code')->nullable()->unique()->after('is_blocked');
            $table->unsignedBigInteger('referred_by')->nullable()->after('referral_code');
            $table->string('phone')->nullable()->after('referred_by');
            $table->string('avatar')->nullable()->after('phone');
            $table->foreign('referred_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referred_by']);
            $table->dropColumn(['is_admin', 'is_blocked', 'referral_code', 'referred_by', 'phone', 'avatar']);
        });
    }
};
