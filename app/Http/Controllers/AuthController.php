<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
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

        if ($authenticated) {
            $request->session()->regenerate();
            return redirect()->route('trade');
        }

        return back()->withErrors(['email' => 'Invalid email or password.'])->withInput();
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
