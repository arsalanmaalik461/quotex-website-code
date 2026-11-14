<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Trade;
use App\Models\Transaction;
use App\Models\User;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('is_admin', false)
            ->withCount(['trades', 'referrals'])
            ->with('referrer');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('email', 'like', "%$s%")
                  ->orWhere('phone', 'like', "%$s%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'blocked') $query->where('is_blocked', true);
            if ($request->status === 'active')  $query->where('is_blocked', false);
        }

        if ($request->filled('account_type')) {
            $query->where('account_type', $request->account_type);
        }

        $users = $query->latest()->paginate(25)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(Request $request, User $user)
    {
        $accountView = $request->query('account', 'overall');
        if (!in_array($accountView, ['overall', 'live', 'demo'], true)) {
            $accountView = 'overall';
        }

        $accountScope = function ($query) use ($accountView) {
            if ($accountView !== 'overall') {
                $query->where('account_type', $accountView);
            }
        };

        $user->load([
            'trades' => fn ($query) => $accountScope($query->with('asset')->latest()->limit(20)),
            'transactions' => fn ($query) => $accountScope($query->latest()->limit(10)),
            'referrals',
        ]);

        $user->loadCount([
            'trades' => fn ($query) => $accountScope($query),
            'referrals',
        ]);

        $tradesQuery = $user->trades();
        $accountScope($tradesQuery);

        $completedTrades = (clone $tradesQuery)->whereIn('result', ['win', 'loss'])->count();
        $winTrades = (clone $tradesQuery)->where('result', 'win')->count();
        $lossTrades = (clone $tradesQuery)->where('result', 'loss')->count();
        $totalInvested = (float) (clone $tradesQuery)->sum('investment');
        $completedInvested = (float) (clone $tradesQuery)->whereIn('result', ['win', 'loss'])->sum('investment');
        $totalPayout = (float) (clone $tradesQuery)->where('result', 'win')->sum('payout');
        $lossAmount = (float) (clone $tradesQuery)->where('result', 'loss')->sum('investment');

        $tradeStats = [
            'total_trades'    => $user->trades_count,
            'active_trades'   => (clone $tradesQuery)->where('result', 'pending')->count(),
            'completed_trades'=> $completedTrades,
            'win_trades'      => $winTrades,
            'loss_trades'     => $lossTrades,
            'win_rate'        => $completedTrades > 0 ? round(($winTrades / $completedTrades) * 100, 2) : 0,
            'total_invested'  => $totalInvested,
            'completed_invested' => $completedInvested,
            'total_payout'    => $totalPayout,
            'loss_amount'     => $lossAmount,
            'net_profit'      => round($totalPayout - $completedInvested, 2),
            'selected_balance'=> $accountView === 'live'
                ? (float) $user->live_balance
                : ($accountView === 'demo' ? (float) $user->demo_balance : (float) $user->live_balance + (float) $user->demo_balance),
            'account_age'     => $user->getAccountAgeDays(),
            'referral_count'  => $user->referrals_count,
        ];

        return view('admin.users.show', compact('user', 'tradeStats', 'accountView'));
    }

    public function update(Request $request, User $user)
    {
        $traceId = (string) \Illuminate\Support\Str::uuid();
        Log::info('ADMIN_USER_UPDATE_TRACE', [
            'trace_id' => $traceId,
            'stage' => 'CONTROLLER_START',
            'request_id' => $request->headers->get('x-hcdn-request-id') ?: $request->headers->get('x-request-id'),
            'admin_id' => Auth::id(),
            'target_user_id' => $user->id,
            'method' => $request->method(),
            'path' => $request->path(),
        ]);

        $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email,'.$user->id,
            'country'      => 'nullable|string|max:100',
            'phone'        => 'nullable|string|max:30',
            'account_type' => 'required|in:demo,live',
        ]);

        Log::info('ADMIN_USER_UPDATE_TRACE', [
            'trace_id' => $traceId,
            'stage' => 'VALIDATION_COMPLETE',
            'admin_id' => Auth::id(),
            'target_user_id' => $user->id,
        ]);

        Log::info('ADMIN_USER_UPDATE_TRACE', [
            'trace_id' => $traceId,
            'stage' => 'DB_OPERATION_START',
            'admin_id' => Auth::id(),
            'target_user_id' => $user->id,
        ]);

        $user->update($request->only(['name', 'email', 'country', 'phone', 'account_type']));

        Log::info('ADMIN_USER_UPDATE_TRACE', [
            'trace_id' => $traceId,
            'stage' => 'DB_OPERATION_COMPLETE',
            'admin_id' => Auth::id(),
            'target_user_id' => $user->id,
        ]);

        if ($request->filled('password')) {
            Log::info('ADMIN_USER_UPDATE_TRACE', [
                'trace_id' => $traceId,
                'stage' => 'PASSWORD_UPDATE_START',
                'admin_id' => Auth::id(),
                'target_user_id' => $user->id,
            ]);
            $user->update(['password' => Hash::make($request->password)]);
            Log::info('ADMIN_USER_UPDATE_TRACE', [
                'trace_id' => $traceId,
                'stage' => 'PASSWORD_UPDATE_COMPLETE',
                'admin_id' => Auth::id(),
                'target_user_id' => $user->id,
            ]);
        }

        Log::info('ADMIN_USER_UPDATE_TRACE', [
            'trace_id' => $traceId,
            'stage' => 'ADMIN_LOG_START',
            'admin_id' => Auth::id(),
            'target_user_id' => $user->id,
        ]);
        AdminLog::record('user_update', "Updated user profile: {$user->email}", 'user', $user->id);
        Log::info('ADMIN_USER_UPDATE_TRACE', [
            'trace_id' => $traceId,
            'stage' => 'ADMIN_LOG_COMPLETE',
            'admin_id' => Auth::id(),
            'target_user_id' => $user->id,
        ]);

        Log::info('ADMIN_USER_UPDATE_TRACE', [
            'trace_id' => $traceId,
            'stage' => 'REDIRECT_START',
            'admin_id' => Auth::id(),
            'target_user_id' => $user->id,
        ]);
        $response = back()->with('success', 'User updated successfully.');
        Log::info('ADMIN_USER_UPDATE_TRACE', [
            'trace_id' => $traceId,
            'stage' => 'RESPONSE_READY',
            'admin_id' => Auth::id(),
            'target_user_id' => $user->id,
        ]);

        return $response;
    }

    public function block(User $user)
    {
        $user->update(['is_blocked' => true]);
        AdminLog::record('user_block', "Blocked user: {$user->email}", 'user', $user->id);
        return back()->with('success', 'User blocked.');
    }

    public function unblock(User $user)
    {
        $user->update(['is_blocked' => false]);
        AdminLog::record('user_unblock', "Unblocked user: {$user->email}", 'user', $user->id);
        return back()->with('success', 'User unblocked.');
    }

    public function destroy(User $user)
    {
        if ($user->is_admin) {
            return back()->with('error', 'Cannot delete admin accounts.');
        }
        $email = $user->email;
        $user->delete();
        AdminLog::record('user_delete', "Deleted user: {$email}", 'user', $user->id);
        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }

    public function addFunds(Request $request, User $user)
    {
        $request->validate([
            'amount'       => 'required|numeric',
            'account_type' => 'required|in:demo,live',
            'note'         => 'nullable|string|max:255',
        ]);

        $amount = $request->amount; // can be negative

        DB::transaction(function () use ($request, $user, $amount) {
            $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            if ($request->account_type === 'live') {
                $lockedUser->live_balance = Money::maxZero(Money::add($lockedUser->live_balance, $amount));
            } else {
                $lockedUser->demo_balance = Money::maxZero(Money::add($lockedUser->demo_balance, $amount));
            }
            $lockedUser->save();

            if (Money::toCents($amount) !== 0) {
                Transaction::create([
                    'user_id' => $lockedUser->id,
                    'account_type' => $request->account_type,
                    'type'    => Money::toCents($amount) > 0 ? 'deposit' : 'withdrawal',
                    'amount'  => Money::fromCents(abs(Money::toCents($amount))),
                    'method'  => 'admin',
                    'status'  => 'approved',
                    'processed_at' => now(),
                    'notes'   => $request->note ?? 'Admin fund adjustment',
                ]);
            }
        });

        AdminLog::record(
            'fund_adjust',
            "Adjusted {$request->account_type} balance by {$amount} for {$user->email}. Note: {$request->note}",
            'user',
            $user->id
        );

        return back()->with('success', 'Balance updated successfully.');
    }

    public function loginAs(User $user)
    {
        if ($user->is_admin) {
            return back()->with('error', 'Cannot impersonate admin accounts.');
        }

        // Store admin session
        session([
            'admin_impersonating' => true,
            'admin_original_id'   => Auth::id(),
        ]);

        AdminLog::record('login_as', "Impersonated user: {$user->email}", 'user', $user->id);

        Auth::loginUsingId($user->id);

        return redirect()->route('trade')->with('info', 'You are now logged in as '.$user->name);
    }

    public function returnToAdmin()
    {
        $adminId = session('admin_original_id');

        if (!$adminId) {
            return redirect()->route('admin.dashboard');
        }

        Auth::loginUsingId($adminId);

        session()->forget(['admin_impersonating', 'admin_original_id']);

        return redirect()->route('admin.dashboard')->with('success', 'Returned to admin account.');
    }
}
