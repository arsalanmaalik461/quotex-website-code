<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\VerificationCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login', [
            // Set (as flash) when a 2FA code challenge is in progress, so
            // the login page shows the code-entry step instead of the
            // email/password form.
            'showTwoFactor'   => (bool) session('showTwoFactor', false),
            'twoFactorEmail'  => session('two_factor_email', old('email', '')),
            'twoFactorResent' => (bool) session('twoFactorResent', false),
        ]);
    }

    public function login(Request $request)
    {
        // Step 2 of a 2FA sign-in (or a code resend): the request carries
        // a verification code, not a password. The user is still logged
        // out here; the pending user id lives in the session.
        if ($request->filled('verification_code') || $request->filled('resend_code')) {
            return $this->handleTwoFactorStep($request);
        }

        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $remember = $request->boolean('remember');

        try {
            $authenticated = Auth::attempt(['email' => $request->email, 'password' => $request->password], $remember);
        } catch (\RuntimeException $e) {
            report($e);
            return back()->withErrors(['email' => 'Invalid email or password.'])->withInput();
        }

        if (!$authenticated) {
            return back()->withErrors(['email' => 'Invalid email or password.'])->withInput();
        }

        $user = Auth::user();

        if ($user->login_2fa) {
            // Password is correct, but access is NOT granted yet. Log
            // the just-authenticated session straight back out (this
            // also clears the remember-me cookie Auth::attempt set),
            // hold the user id in the session, and email a 6-digit
            // code. Only a correct, unexpired code completes the login.
            Auth::logout();

            $code = VerificationCodeService::issue($user, VerificationCodeService::PURPOSE_LOGIN);
            if (!VerificationCodeService::sendCodeEmail($user, VerificationCodeService::PURPOSE_LOGIN, $code)) {
                return back()->withErrors(['email' => 'We could not send the verification code to your email. Please try signing in again.'])->withInput();
            }

            $request->session()->put('two_factor_user_id', $user->id);
            $request->session()->put('two_factor_remember', $remember);
            $request->session()->put('two_factor_email', $user->email);

            return redirect()->route('login')->with('showTwoFactor', true);
        }

        $request->session()->regenerate();
        return redirect()->route('trade');
    }

    /**
     * Second step of a 2FA sign-in: verify the emailed 6-digit code
     * (or send a fresh one). The password was already verified in
     * login(); the pending user id in the session binds this step to
     * that password check. A wrong/expired code never logs anyone in.
     */
    private function handleTwoFactorStep(Request $request)
    {
        $request->validate([
            'email'             => 'required|email',
            'verification_code' => 'nullable|string|max:10',
        ]);

        $pendingUserId = $request->session()->get('two_factor_user_id');
        $pendingEmail = $request->session()->get('two_factor_email');
        $user = $pendingUserId ? User::find($pendingUserId) : null;

        if (!$user || strcasecmp($user->email, (string) $request->email) !== 0
            || strcasecmp((string) $pendingEmail, (string) $request->email) !== 0) {
            return redirect()->route('login')
                ->withErrors(['verification_code' => 'Your sign-in session has expired. Please sign in again.'])
                ->withInput();
        }

        if ($request->filled('resend_code')) {
            $code = VerificationCodeService::issue($user, VerificationCodeService::PURPOSE_LOGIN);
            if (!VerificationCodeService::sendCodeEmail($user, VerificationCodeService::PURPOSE_LOGIN, $code)) {
                return redirect()->route('login')
                    ->withErrors(['verification_code' => 'We could not send a new code. Please try again.'])
                    ->withInput()
                    ->with('showTwoFactor', true);
            }

            return redirect()->route('login')
                ->with('showTwoFactor', true)
                ->with('twoFactorResent', true);
        }

        if (!VerificationCodeService::verify($user, VerificationCodeService::PURPOSE_LOGIN, (string) $request->verification_code)) {
            return redirect()->route('login')
                ->withErrors(['verification_code' => 'The verification code is incorrect or has expired.'])
                ->withInput()
                ->with('showTwoFactor', true);
        }

        Auth::login($user, (bool) $request->session()->get('two_factor_remember', false));
        $request->session()->forget(['two_factor_user_id', 'two_factor_remember', 'two_factor_email']);
        $request->session()->regenerate();

        return redirect()->route('trade');
    }

    public function showRegister(Request $request)
    {
        $refCode = $request->query('ref') ?: $request->query('r');
        if ($refCode) {
            session(['referral_code' => Str::upper(trim($refCode))]);
        }
        $refCode = session('referral_code', $refCode);

        return view('auth.register', compact('refCode'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'email'         => 'required|email|unique:users',
            'password'      => 'required|min:6',
            'country-code'  => 'nullable|string',
            'country'       => 'nullable|string',
            'referral_code' => 'nullable|string',
            'ref'           => 'nullable|string',
        ]);

        $name = explode('@', $request->email)[0];
        $country = $request->input('country-code') ?: $request->input('country') ?: 'Pakistan';

        $refCode = Str::upper(trim($request->input('referral_code') ?: $request->input('ref') ?: session('referral_code', '')));

        $user = DB::transaction(function () use ($request, $name, $country, $refCode) {
            $referredBy = null;
            if ($refCode !== '') {
                $referrer = User::where('referral_code', $refCode)->lockForUpdate()->first();
                if ($referrer && strcasecmp($referrer->email, (string) $request->email) !== 0) {
                    $referredBy = $referrer->id;
                }
            }

            do {
                $newRefCode = 'QX' . Str::upper(Str::random(6));
            } while (User::where('referral_code', $newRefCode)->exists());

            return User::create([
                'name'          => $name,
                'email'         => strtolower(trim($request->email)),
                'password'      => Hash::make($request->password),
                'country'       => $country,
                'demo_balance'  => 10000.00,
                'live_balance'  => 0.00,
                'account_type'  => 'demo',
                'referral_code' => $newRefCode,
                'referred_by'   => $referredBy,
            ]);
        });

        session()->forget('referral_code');

        if ($user->referred_by) {
            \App\Services\ReferralService::processJoinCommission($user);
        }

        Auth::login($user);

        return redirect()->route('trade')->with('success', 'Account created! You have $10,000 demo balance.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
