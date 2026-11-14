<?php

namespace App\Services;

use App\Models\ReferralCommission;
use App\Models\ReferralSetting;
use App\Models\Trade;
use App\Models\Transaction;
use App\Models\User;
use App\Support\Money;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReferralService
{
    /**
     * Process referral commission on settled live trade.
     * Supports both Trade WIN profit-share and Trade LOSS RevShare commissions.
     */
    public static function processTradeCommission(Trade $trade): ?ReferralCommission
    {
        try {
            return DB::transaction(function () use ($trade) {
                $trade = Trade::whereKey($trade->id)->with('user')->lockForUpdate()->first();
                if (!$trade || $trade->account_type !== 'live' || !in_array($trade->result, ['win', 'loss'], true)) {
                    return null;
                }

                if (ReferralCommission::where('trade_id', $trade->id)->lockForUpdate()->exists()) {
                    return null;
                }

                $user = $trade->user;
                if (!$user || !$user->referred_by || $user->referred_by === $user->id) {
                    return null;
                }

                $referrer = User::whereKey($user->referred_by)->lockForUpdate()->first();
                if (!$referrer || $referrer->is_blocked || $referrer->id === $user->id) {
                    return null;
                }

                $settings = ReferralSetting::getSettings();
                if (!$settings->is_enabled || Money::greaterThan($settings->min_trade_amount, $trade->investment)) {
                    return null;
                }

                $type = null;
                $baseAmount = '0.00';
                $percent = '0.00';

                if ($trade->result === 'win') {
                    $netProfit = Money::maxZero(Money::subtract($trade->payout, $trade->investment));
                    $percent = (string) $settings->trade_win_commission_percent;
                    if (Money::greaterThan($netProfit, 0) && Money::greaterThan($percent, 0)) {
                        $type = 'trade_win';
                        $baseAmount = $netProfit;
                    }
                } elseif ($trade->result === 'loss') {
                    $percent = (string) $settings->trade_loss_commission_percent;
                    if (Money::greaterThan($trade->investment, 0) && Money::greaterThan($percent, 0)) {
                        $type = 'trade_loss';
                        $baseAmount = (string) $trade->investment;
                    }
                }

                if (!$type) {
                    return null;
                }

                $commission = Money::percent($baseAmount, $percent);
                if (!Money::greaterThan($commission, 0)) {
                    return null;
                }

                if ($settings->auto_credit_to_balance) {
                    $referrer->live_balance = Money::add($referrer->live_balance, $commission);
                    $referrer->save();
                }

                return ReferralCommission::create([
                    'referrer_id'        => $referrer->id,
                    'referred_user_id'   => $user->id,
                    'type'               => $type,
                    'trade_id'           => $trade->id,
                    'transaction_id'     => null,
                    'base_amount'        => $baseAmount,
                    'commission_percent' => $percent,
                    'commission_amount'  => $commission,
                    'status'             => 'credited',
                    'notes'              => ucfirst(str_replace('_', ' ', $type)) . ": {$percent}% on $" . $baseAmount . " (Trade #{$trade->id})",
                ]);
            });
        } catch (QueryException $e) {
            Log::warning('Duplicate referral trade commission ignored', ['trade_id' => $trade->id ?? null, 'error' => $e->getMessage()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Referral trade commission error: ' . $e->getMessage(), [
                'trade_id' => $trade->id ?? null,
                'trace'    => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Process referral commission when a user makes an approved deposit.
     */
    public static function processDepositCommission(Transaction $transaction): ?ReferralCommission
    {
        try {
            return DB::transaction(function () use ($transaction) {
                $transaction = Transaction::whereKey($transaction->id)->with('user')->lockForUpdate()->first();
                if (!$transaction || $transaction->type !== 'deposit' || $transaction->status !== 'approved') {
                    return null;
                }

                if ($transaction->account_type && $transaction->account_type !== 'live') {
                    return null;
                }

                if (ReferralCommission::where('transaction_id', $transaction->id)->lockForUpdate()->exists()) {
                    return null;
                }

                $user = $transaction->user;
                if (!$user || !$user->referred_by || $user->referred_by === $user->id) {
                    return null;
                }

                $referrer = User::whereKey($user->referred_by)->lockForUpdate()->first();
                if (!$referrer || $referrer->is_blocked || $referrer->id === $user->id) {
                    return null;
                }

                $settings = ReferralSetting::getSettings();
                $percent = (string) $settings->deposit_commission_percent;
                if (!$settings->is_enabled || !Money::greaterThan($transaction->amount, 0) || !Money::greaterThan($percent, 0)) {
                    return null;
                }

                $commission = Money::percent($transaction->amount, $percent);
                if (!Money::greaterThan($commission, 0)) {
                    return null;
                }

                if ($settings->auto_credit_to_balance) {
                    $referrer->live_balance = Money::add($referrer->live_balance, $commission);
                    $referrer->save();
                }

                return ReferralCommission::create([
                    'referrer_id'        => $referrer->id,
                    'referred_user_id'   => $user->id,
                    'type'               => 'deposit',
                    'trade_id'           => null,
                    'transaction_id'     => $transaction->id,
                    'base_amount'        => $transaction->amount,
                    'commission_percent' => $percent,
                    'commission_amount'  => $commission,
                    'status'             => 'credited',
                    'notes'              => "Deposit Commission: {$percent}% on deposit $" . $transaction->amount . " (Tx #{$transaction->id})",
                ]);
            });
        } catch (QueryException $e) {
            Log::warning('Duplicate referral deposit commission ignored', ['transaction_id' => $transaction->id ?? null, 'error' => $e->getMessage()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Referral deposit commission error: ' . $e->getMessage(), ['transaction_id' => $transaction->id ?? null]);
            return null;
        }
    }

    /**
     * Process join/registration bonus for referrer when new user signs up.
     */
    public static function processJoinCommission(User $newUser): ?ReferralCommission
    {
        try {
            return DB::transaction(function () use ($newUser) {
                $newUser = User::whereKey($newUser->id)->lockForUpdate()->first();
                if (!$newUser || !$newUser->referred_by || $newUser->referred_by === $newUser->id) {
                    return null;
                }

                if (ReferralCommission::where('referred_user_id', $newUser->id)->where('type', 'join')->lockForUpdate()->exists()) {
                    return null;
                }

                $referrer = User::whereKey($newUser->referred_by)->lockForUpdate()->first();
                if (!$referrer || $referrer->is_blocked || $referrer->id === $newUser->id) {
                    return null;
                }

                $settings = ReferralSetting::getSettings();
                $bonus = (string) $settings->join_commission_amount;
                if (!$settings->is_enabled || !Money::greaterThan($bonus, 0)) {
                    return null;
                }

                if ($settings->auto_credit_to_balance) {
                    $referrer->live_balance = Money::add($referrer->live_balance, $bonus);
                    $referrer->save();
                }

                return ReferralCommission::create([
                    'referrer_id'        => $referrer->id,
                    'referred_user_id'   => $newUser->id,
                    'type'               => 'join',
                    'trade_id'           => null,
                    'transaction_id'     => null,
                    'base_amount'        => $bonus,
                    'commission_percent' => 100.00,
                    'commission_amount'  => $bonus,
                    'status'             => 'credited',
                    'notes'              => "Referral Signup Bonus for inviting {$newUser->email}",
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('Referral join commission error: ' . $e->getMessage(), ['user_id' => $newUser->id ?? null]);
            return null;
        }
    }
}
