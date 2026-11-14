<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kyc_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('full_name');
            $table->string('phone');
            $table->string('id_card_front');
            $table->string('id_card_back');
            $table->string('live_selfie');
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('user_id');
        });

        if (!Schema::hasColumn('users', 'kyc_status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('kyc_status')->default('unverified')->after('email_verified');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kyc_verifications');

        if (Schema::hasColumn('users', 'kyc_status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('kyc_status');
            });
        }
    }
};
