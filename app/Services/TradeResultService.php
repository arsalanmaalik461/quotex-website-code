<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Trade;
use App\Models\TradeSimulationControl;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

            // 3b. Live 4/6 cycle (live trades only, and only when no
            // admin / pair / global control forced this trade).
            // In every 10 settled live trades: 4 wins, 6 losses.
            // See LiveCycleService: same structure as the demo 8/2
            // cycle — hash-derived positions, no counter, no reset.
            if ($forcedSide === null && $trade->account_type === 'live') {
                $settledLiveCount = LiveCycleService::settledLiveCount((int) $trade->user_id);
                $forcedSide = LiveCycleService::forcedSideForTrade($trade, $settledLiveCount);
            }

            [$closePrice, $spikeInjected] = self::calculateClosePrice($trade, $asset, $forcedSide, $openPrice, $precision, $pip);

            // 4. Determine result based on close price vs open price.
            // Equal prices are a draw: the investment is refunded in
            // full (see actualPayoutFor) and the trade counts as neither
            // a win nor a loss (demo 8/2 / live 4/6 cycle, stats, referrals).
            if ($trade->direction === 'up') {
                $settlementResult = $closePrice > $openPrice ? 'win' : ($closePrice < $openPrice ? 'loss' : 'draw');
            } else {
                $settlementResult = $closePrice < $openPrice ? 'win' : ($closePrice > $openPrice ? 'loss' : 'draw');
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
                // Signed P/L, not the gross payout: win = payout -
                // investment, loss = -investment (or buyout - investment
                // for an early sell), draw = 0. The UI must display this
                // wherever it says "profit".
                'profit' => self::profitFor($trade, $settlementResult, $payout),
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
                    'profit' => 0.0,
                    'spike_injected' => false,
                    'balance' => $trade->user ? $trade->user->getBalance() : 0,
                    'processed_referral' => false,
                ];
            }

            $user = User::whereKey($trade->user_id)->lockForUpdate()->first();

            // A forced Win/Loss also decides an early sell's win/loss
            // result (otherwise the user could dodge a forced loss by
            // selling early). The steering itself is untouched — only
            // the buyout below changed. Note the buyout is priced off
            // the CURRENT market price, not the steered close price, so
            // a forced trade sold early while the market is against the
            // forced side pays the market value; the result still counts
            // as the forced outcome.
            // P0-2 (2026-11-18): the client-supplied close price is NEVER
            // trusted here. It used to decide both the win/loss result
            // and the buyout price, so a live user could POST
            // early_sell=1 with a far in-the-money close_price and be
            // paid near-full payout. The server price is the only price:
            // simulatePrice() is the same source the normal settlement
            // path uses. $clientClosePrice is kept in the signature for
            // callers but is deliberately ignored.
            $serverPrice = OtcPriceEngine::simulatePrice($trade->asset);
            $forcedSide = OtcPriceEngine::forcedSideForTrade($trade, $trade->asset);
            if ($forcedSide !== null) {
                $isWinning = $trade->direction === $forcedSide;
                $closePrice = OtcPriceEngine::steeringTargetPrice($trade, $forcedSide);
            } else {
                $closePrice = $serverPrice;
                $isWinning = $trade->direction === 'up'
                    ? $closePrice > (float) $trade->open_price
                    : $closePrice < (float) $trade->open_price;
            }

            // Market-priced buyout: depends on the time still remaining,
            // how far the current market price is in/out of the money,
            // and the payout %. Replaces the old flat formula (15% of
            // the investment when winning, 5% when losing, rounded to
            // whole dollars). See calculateEarlySellPayout().
            // Priced off the server price (see P0-2 above), never the
            // client-supplied price.
            $marketPrice = $serverPrice;
            $buyout = self::calculateEarlySellPayout($trade, $marketPrice);
            $result = $isWinning ? 'win' : 'loss';

            $trade->update([
                'close_price' => $closePrice,
                'result' => $result,
                'payout' => $buyout,
                'closed_at' => now(),
            ]);

            // Mark the row so later profit maths knows the stored payout
            // is the buyout, not the gross payout offered at placement.
            // Direct assignment (not mass assignment) so no Trade model
            // change is needed; guarded so this is safe to run before
            // the migration that adds the column has been applied.
            if (Schema::hasColumn('trades', 'early_sold')) {
                $trade->early_sold = true;
                $trade->save();
            }

            if ($buyout > 0 && $user) {
                self::creditUserBalance($user, $trade->account_type, $buyout);
            }

            return [
                'success' => true,
                'result' => $result,
                'close_price' => $closePrice,
                'payout' => $buyout,
                'profit' => self::profitFor($trade, $result, $buyout),
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

    /**
     * Market-priced early-sell offer.
     *
     * Replaces the old flat formula (15% of the investment when
     * winning, 5% when losing, rounded to whole dollars), which
     * ignored the time remaining, the price distance and the payout %.
     *
     *   offer = investment x pWin x (1 + payout%)
     *
     * pWin is a logistic estimate of the chance the trade still wins,
     * from the signed distance in the money (in pips) against the move
     * expected over the time still remaining (about 1 pip per
     * sqrt(second), so the offer converges to the full payout when far
     * in the money and to 0 when far out of it as expiry approaches):
     *
     *   z    = signedPipsInTheMoney / sqrt(max(secondsRemaining, 1))
     *   pWin = 1 / (1 + exp(-1.7 x z))
     *
     * At 50% elapsed while winning the offer sits between the
     * investment and the full payout; while losing it sits below the
     * investment but above 0. The public/js/trade.js preview
     * (calculateEarlySellValue) uses the same formula; the server
     * value is authoritative at sell time.
     *
     * @param Trade $trade
     * @param float|null $currentPrice   Current market price (never the steered close price)
     * @param int|null $elapsedSeconds  Seconds since the trade opened (derived when null)
     */
    public static function calculateEarlySellPayout(Trade $trade, ?float $currentPrice = null, ?int $elapsedSeconds = null): float
    {
        $investment = (float) $trade->investment;
        $asset = $trade->asset;
        $payoutPercent = $asset ? (float) OtcPriceEngine::payoutPercent($asset) : 0.0;
        $grossPayout = $investment * (1 + $payoutPercent / 100);
        $openPrice = (float) $trade->open_price;

        $currentPrice ??= $asset ? OtcPriceEngine::simulatePrice($asset) : $openPrice;

        if ($elapsedSeconds === null) {
            $openedAt = $trade->opened_at ?: $trade->created_at;
            $elapsedSeconds = $openedAt ? max(0, now()->diffInSeconds($openedAt)) : 0;
        }

        $duration = (int) ($trade->duration_seconds ?: 60);
        $remaining = max(0, $duration - $elapsedSeconds);

        $pip = pow(10, -OtcPriceEngine::getPrecision($openPrice, $asset));
        $signedPips = $trade->direction === 'up'
            ? ($currentPrice - $openPrice) / $pip
            : ($openPrice - $currentPrice) / $pip;

        $sigma = sqrt(max($remaining, 1));
        $pWin = 1.0 / (1.0 + exp(-1.7 * ($signedPips / $sigma)));

        $offer = $investment * $pWin * (1 + $payoutPercent / 100);

        return round(min(max($offer, 0.0), $grossPayout), 2);
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
                'profit' => self::profitFor($trade, $settlementResult, $payout),
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

        if ($result === 'draw' || $result === 'tie') {
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

    /**
     * Signed P/L for a trade: what the user actually gained or lost
     * against the investment. Win: credited payout minus investment.
     * Loss: credited amount (0, or the early-sell buyout) minus
     * investment. Draw: 0 — the investment is refunded in full.
     */
    private static function profitFor(Trade $trade, string $result, float $creditedPayout): float
    {
        if ($result === 'draw' || $result === 'tie') {
            return 0.0;
        }

        return round($creditedPayout - (float) $trade->investment, 2);
    }

    /**
     * Amount actually credited back to the balance for an
     * already-settled trade row. The stored `payout` column holds the
     * gross payout offered at placement for a normally settled win,
     * and the buyout for an early-sold trade — but for a normally
     * settled loss it still holds the (never paid) gross payout, so
     * it cannot be read raw for losses.
     */
    public static function creditedPayoutForSettled(Trade $trade): float
    {
        $result = (string) $trade->result;

        if ($result === 'win') {
            return (float) $trade->payout;
        }

        if ($result === 'draw' || $result === 'tie') {
            return (float) $trade->investment;
        }

        if ($result === 'loss') {
            // Early-sold losses were paid the stored buyout; normal
            // losses were paid nothing. Pre-migration rows have no
            // early_sold flag, so they read as normal losses (their
            // buyout, if any, is lost to history — see the migration).
            return $trade->early_sold ? (float) $trade->payout : 0.0;
        }

        return 0.0;
    }

    /**
     * Signed P/L for an already-settled trade row (history, CSV,
     * today's profit). Win: credited payout minus investment. Loss:
     * credited amount (0, or the early-sell buyout) minus investment.
     * Draw: 0.
     */
    public static function settledProfit(Trade $trade): float
    {
        return self::profitFor($trade, (string) $trade->result, self::creditedPayoutForSettled($trade));
    }

    private static function settledResponse(Trade $trade): array
    {
        $trade->loadMissing('user');

        $payout = self::creditedPayoutForSettled($trade);

        return [
            'result' => $trade->result,
            'close_price' => (float) $trade->close_price,
            'payout' => $payout,
            'profit' => self::profitFor($trade, (string) $trade->result, $payout),
            'spike_injected' => (bool) $trade->spike_injected,
            'balance' => $trade->user ? $trade->user->getBalance() : 0,
            'processed_referral' => false,
        ];
    }
}
