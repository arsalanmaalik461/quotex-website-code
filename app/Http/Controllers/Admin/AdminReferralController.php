<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\ReferralCommission;
use App\Models\ReferralSetting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class AdminReferralController extends Controller
{
    public function index(Request $request)
    {
        $settings = ReferralSetting::getSettings();

        $stats = [
            'total_referrals'           => User::whereNotNull('referred_by')->count(),
            'active_referrers'          => User::has('referrals')->count(),
            'total_commissions_paid'    => (float) ReferralCommission::where('status', 'credited')->sum('commission_amount'),
            'trade_win_commissions'     => (float) ReferralCommission::where('type', 'trade_win')->where('status', 'credited')->sum('commission_amount'),
            'trade_loss_commissions'    => (float) ReferralCommission::where('type', 'trade_loss')->where('status', 'credited')->sum('commission_amount'),
            'deposit_commissions'       => (float) ReferralCommission::where('type', 'deposit')->where('status', 'credited')->sum('commission_amount'),
            'join_commissions'          => (float) ReferralCommission::where('type', 'join')->where('status', 'credited')->sum('commission_amount'),
            'total_commission_events'   => ReferralCommission::count(),
        ];

        // All referrers with at least one direct referral.
        $topReferrers = User::where('is_admin', false)
            ->withCount('referrals')
            ->withSum(['commissionsEarned' => fn($q) => $q->where('status', 'credited')], 'commission_amount')
            ->has('referrals')
            ->orderByDesc('commissions_earned_sum_commission_amount')
            ->get();

        // Paginated Commission History
        $query = ReferralCommission::with(['referrer', 'referredUser', 'trade', 'transaction']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->whereHas('referrer', fn($r) => $r->where('email', 'like', "%{$s}%")->orWhere('name', 'like', "%{$s}%"))
                  ->orWhereHas('referredUser', fn($u) => $u->where('email', 'like', "%{$s}%")->orWhere('name', 'like', "%{$s}%"));
            });
        }

        $commissions = $query->latest()->paginate(25)->withQueryString();

        return view('admin.referrals.index', compact('stats', 'settings', 'topReferrers', 'commissions'));
    }

    public function settings()
    {
        $settings = ReferralSetting::getSettings();
        return view('admin.referrals.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'is_enabled'                    => 'nullable|boolean',
            'trade_win_commission_percent'  => 'required|numeric|min:0|max:100',
            'trade_loss_commission_percent' => 'required|numeric|min:0|max:100',
            'deposit_commission_percent'    => 'required|numeric|min:0|max:100',
            'join_commission_amount'        => 'required|numeric|min:0',
            'min_trade_amount'              => 'required|numeric|min:0.1',
            'auto_credit_to_balance'        => 'nullable|boolean',
            'terms_and_conditions'          => 'nullable|string',
        ]);

        $settings = ReferralSetting::getSettings();
        $settings->update([
            'is_enabled'                    => $request->boolean('is_enabled'),
            'trade_win_commission_percent'  => (float) $request->trade_win_commission_percent,
            'trade_loss_commission_percent' => (float) $request->trade_loss_commission_percent,
            'deposit_commission_percent'    => (float) $request->deposit_commission_percent,
            'join_commission_amount'        => (float) $request->join_commission_amount,
            'min_trade_amount'              => (float) $request->min_trade_amount,
            'auto_credit_to_balance'        => $request->boolean('auto_credit_to_balance'),
            'terms_and_conditions'          => $request->terms_and_conditions,
        ]);

        AdminLog::record(
            'update_referral_settings',
            "Updated referral settings: Win={$settings->trade_win_commission_percent}%, Loss={$settings->trade_loss_commission_percent}%, Deposit={$settings->deposit_commission_percent}%, Join=$" . $settings->join_commission_amount
        );

        return redirect()->route('admin.referrals.settings')->with('success', 'Referral & Commission settings updated successfully!');
    }

    public function userReferrals(User $user)
    {
        $referralIds = $user->referrals()->pluck('id');

        $totalReferralDeposits = $referralIds->isEmpty()
            ? 0.0
            : (float) Transaction::whereIn('user_id', $referralIds)
                ->where('type', 'deposit')
                ->where('status', 'approved')
                ->sum('amount');

        $user->load(['referrals' => fn($q) => $q->withCount([
            'trades',
            'trades as live_trades_count' => fn ($query) => $query->where('account_type', 'live'),
        ])->latest()]);
        $commissions = ReferralCommission::with(['referredUser', 'trade', 'transaction'])
            ->where('referrer_id', $user->id)
            ->latest()
            ->paginate(25);

        return view('admin.referrals.user', compact('user', 'commissions', 'totalReferralDeposits'));
    }
}
