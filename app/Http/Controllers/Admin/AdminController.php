<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Asset;
use App\Models\Trade;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function dashboard()
    {
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        $stats = [
            'total_users'          => User::where('is_admin', false)->count(),
            'active_users'         => User::where('is_admin', false)->where('is_blocked', false)->count(),
            'blocked_users'        => User::where('is_blocked', true)->count(),
            'total_assets'         => Asset::count(),
            'active_assets'        => Asset::where('is_active', true)->count(),
            'total_trades_today'   => Trade::whereDate('created_at', today())->count(),
            'total_trades'         => Trade::count(),
            'active_trades'        => Trade::where('result', 'pending')->count(),
            'total_volume_today'   => Trade::whereDate('created_at', today())->sum('investment'),
            'total_volume'         => Trade::sum('investment'),
            'pending_deposits'     => Transaction::where('type', 'deposit')->where('status', 'pending')->count(),
            'pending_withdrawals'  => Transaction::where('type', 'withdrawal')->where('status', 'pending')->count(),
            'total_deposit_amount' => Transaction::where('type', 'deposit')->where('status', 'approved')->sum('amount'),
            'today_deposit_amount' => Transaction::where('type', 'deposit')
                ->where('status', 'approved')
                ->whereBetween('processed_at', [$todayStart, $todayEnd])
                ->sum('amount'),
            'today_approved_withdrawal_amount' => Transaction::where('type', 'withdrawal')
                ->where('status', 'approved')
                ->whereBetween('processed_at', [$todayStart, $todayEnd])
                ->sum('amount'),
            'total_users_balance'  => User::where('is_admin', false)->sum('live_balance'),
            'pending_kyc'          => $this->getPendingKycCount(),
        ];

        $recentTrades = Trade::with(['user', 'asset'])
            ->latest()
            ->limit(10)
            ->get();

        $recentUsers = User::where('is_admin', false)
            ->latest()
            ->limit(5)
            ->get();

        $recentLogs = AdminLog::with('admin')
            ->latest()
            ->limit(10)
            ->get();

        // Trade volume by day (last 7 days)
        $tradeVolumeChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $tradeVolumeChart[] = [
                'date'   => $date,
                'volume' => (float) Trade::whereDate('created_at', $date)->sum('investment'),
                'count'  => Trade::whereDate('created_at', $date)->count(),
            ];
        }

        return view('admin.dashboard', compact(
            'stats', 'recentTrades', 'recentUsers', 'recentLogs', 'tradeVolumeChart'
        ));
    }

    public function logs(Request $request)
    {
        $logs = AdminLog::with('admin')
            ->when($request->filled('action'), fn($q) => $q->where('action', 'like', '%'.$request->action.'%'))
            ->latest()
            ->paginate(50);

        return view('admin.logs', compact('logs'));
    }

    public function loginForm()
    {
        if (Auth::check() && Auth::user()->is_admin) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.auth.login');
    }

    public function loginSubmit(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);
        $credentials['email'] = strtolower(trim($credentials['email']));

        $remember = $request->boolean('remember');

        try {
            $authenticated = Auth::attempt($credentials, $remember);
        } catch (\RuntimeException $e) {
            report($e);
            return back()->withErrors(['email' => 'Invalid email address or password.'])->withInput();
        }

        if ($authenticated) {
            $user = Auth::user();
            if (!$user->is_admin) {
                Auth::logout();
                return back()->withErrors(['email' => 'Access denied. This account does not have administrator privileges.'])->withInput();
            }

            if ($user->is_blocked) {
                Auth::logout();
                return back()->withErrors(['email' => 'This admin account has been suspended.'])->withInput();
            }

            $request->session()->regenerate();
            AdminLog::record('admin_login', 'Admin logged in through /admin/login');
            return redirect()->route('admin.dashboard')->with('success', 'Welcome back, ' . $user->name . '!');
        }

        return back()->withErrors(['email' => 'Invalid email address or password.'])->withInput();
    }

    public function profile()
    {
        $admin = Auth::user();
        return view('admin.profile', compact('admin'));
    }

    public function updateProfile(Request $request)
    {
        $admin = Auth::user();

        $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|max:255|unique:users,email,' . $admin->id,
            'current_password'      => 'nullable|required_with:password',
            'password'              => 'nullable|min:6|confirmed',
        ]);

        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $admin->password)) {
                return back()->withErrors(['current_password' => 'The provided current password is incorrect.'])->withInput();
            }
            $admin->password = Hash::make($request->password);
        }

        $admin->name  = $request->name;
        $admin->email = $request->email;
        $admin->save();

        AdminLog::record('update_profile', 'Admin updated name/email/password');

        return redirect()->route('admin.profile')->with('success', 'Profile and login details updated successfully!');
    }

    private function getPendingKycCount(): int
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('kyc_verifications')) {
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            } catch (\Throwable $e) {
                return 0;
            }
        }
        return \Illuminate\Support\Facades\Schema::hasTable('kyc_verifications')
            ? \App\Models\KycVerification::where('status', 'pending')->count()
            : 0;
    }
}
