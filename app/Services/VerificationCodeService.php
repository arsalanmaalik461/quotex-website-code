<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Email verification codes for two-step verification (NOT TOTP).
 *
 * Used by:
 *  - sign-in 2FA: AuthController issues a code after the password
 *    checks out and only completes the login once the code verifies
 *    (purpose 'login').
 *  - withdrawal 2FA: AccountController issues a code and only creates
 *    the withdrawal once the code verifies (purpose 'withdrawal').
 *
 * Rules: 6 digits, 10-minute expiry, at most 5 attempts, single use.
 * Only the bcrypt hash is stored — the plain code is returned once to
 * the caller (which emails it) and never persisted. Issuing a new code
 * invalidates any earlier unused code for the same user + purpose.
 *
 * Mail is sent with the same Mail::html style as the email-verification
 * flow in AccountController::resendVerificationEmail.
 */
class VerificationCodeService
{
    public const PURPOSE_LOGIN = 'login';
    public const PURPOSE_WITHDRAWAL = 'withdrawal';

    public const CODE_TTL_MINUTES = 10;
    public const MAX_ATTEMPTS = 5;

    /**
     * Generate a fresh code for the user + purpose and store its hash.
     * Returns the plain 6-digit code (caller must email it immediately).
     */
    public static function issue(User $user, string $purpose): string
    {
        $code = (string) random_int(100000, 999999);

        // Invalidate any earlier unused code for this user + purpose.
        DB::table('verification_codes')
            ->where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        DB::table('verification_codes')->insert([
            'user_id'    => $user->id,
            'purpose'    => $purpose,
            'code_hash'  => Hash::make($code),
            'expires_at' => now()->addMinutes(self::CODE_TTL_MINUTES),
            'attempts'   => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $code;
    }

    /**
     * Check a submitted code. Returns true only for the latest unused,
     * unexpired code that matches. A wrong code counts as an attempt;
     * after MAX_ATTEMPTS the code is burnt. A correct code is consumed
     * (single use).
     */
    public static function verify(User $user, string $purpose, string $code): bool
    {
        $row = DB::table('verification_codes')
            ->where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (!$row) {
            return false;
        }

        $attempts = (int) $row->attempts + 1;
        $ok = Hash::check(trim($code), $row->code_hash);

        DB::table('verification_codes')
            ->where('id', $row->id)
            ->update([
                'attempts'   => $attempts,
                'used_at'    => ($ok || $attempts >= self::MAX_ATTEMPTS) ? now() : null,
                'updated_at' => now(),
            ]);

        return $ok;
    }

    /**
     * Email a just-issued code. Returns false (and logs) when sending
     * fails so the caller can refuse to proceed instead of stranding
     * the user on a code step they can never complete.
     */
    public static function sendCodeEmail(User $user, string $purpose, string $code): bool
    {
        if ($purpose === self::PURPOSE_WITHDRAWAL) {
            $subject = 'Your withdrawal confirmation code';
            $action = 'confirm your withdrawal';
        } else {
            $subject = 'Your sign-in verification code';
            $action = 'sign in to your account';
        }

        try {
            Mail::html(
                '<p>Hello ' . e($user->name ?? 'trader') . ',</p>'
                . '<p>Your verification code to ' . $action . ' is:</p>'
                . '<p style="font-size:24px; font-weight:700; letter-spacing:4px;">' . e($code) . '</p>'
                . '<p>This code expires in ' . self::CODE_TTL_MINUTES . ' minutes. If you did not request this, please ignore this email.</p>',
                function ($message) use ($user, $subject) {
                    $message->to($user->email)->subject($subject);
                }
            );
        } catch (\Throwable $e) {
            Log::warning('Verification code email failed to send', [
                'user_id' => $user->id,
                'purpose' => $purpose,
                'error'   => $e->getMessage(),
            ]);

            return false;
        }

        return true;
    }
}
