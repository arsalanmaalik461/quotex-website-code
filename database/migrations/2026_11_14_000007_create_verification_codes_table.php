<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Email verification codes for the two-step flows that email a code:
 * sign-in 2FA (purpose 'login') and withdrawal 2FA (purpose
 * 'withdrawal'). See App\Services\VerificationCodeService.
 *
 * Only the hash of a code is stored (bcrypt via Hash::make), never the
 * plain code. A code is valid for 10 minutes, can be tried at most
 * 5 times, and is single-use. Issuing a new code invalidates any
 * earlier unused code for the same user + purpose.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('purpose', 32); // 'login' | 'withdrawal'
            $table->string('code_hash');
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'purpose']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_codes');
    }
};
