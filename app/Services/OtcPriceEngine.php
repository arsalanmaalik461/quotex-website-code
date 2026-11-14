<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetMarketState;
use App\Models\Trade;
use App\Models\TradeSimulationControl;
use Carbon\Carbon;

/**
 * OTC Price Engine
 *
 * Generates 100% deterministic, authentic OHLC candlestick data and synchronized real-time ticks.
 * All clients, tabs, and server settlements share the exact same candle stream.
 * Supports:
 *  - Dynamic precision (Forex 5 decimals, JPY/Exotics 2 decimals, Crypto 2 decimals)
 *  - Deterministic multi-harmonic market cycles with natural trends
 *  - Pair-level control (pump / crash / auto)
 *  - High-precision trade outcome synchronization
 */
class OtcPriceEngine
{
    /**
     * Last-seconds steering: in the final 3 seconds before a forced
     * trade expires, the live price glides gradually towards the
     * forced outcome instead of jumping at settlement. Before that
     * window the market follows its normal organic path.
     */
    public const STEER_SECONDS = 3;
    public const STEER_MARGIN_PIPS = 10;

    private static array $marketStateCache = [];
    private static array $steeringCache = [];

    /**
     * Get accurate decimal precision for an asset price.
     */
    public static function getPrecision(float $price, ?Asset $asset = null): int
    {
        $sym = $asset ? strtoupper($asset->symbol) : '';
        if ($price >= 500 || str_contains($sym, 'BTC') || str_contains($sym, 'ETH') || str_contains($sym, 'GOLD') || str_contains($sym, 'XAU') || str_contains($sym, 'SOL') || str_contains($sym, 'BNB')) {
            return 2;
        }
        if ($price >= 50 || str_contains($sym, 'JPY') || str_contains($sym, 'PKR') || str_contains($sym, 'INR') || str_contains($sym, 'IDR')) {
            return 3;
        }
        if ($asset && isset($asset->digits) && $asset->digits !== null && (int)$asset->digits > 0) {
            if ($price >= 25) return min((int) $asset->digits, 3);
            return min((int) $asset->digits, 5);
        }
        if ($price >= 0.1) {
            return 5;
        }
        return 6;
    }

    /**
     * Get base price for an asset.
     */
    public static function getBasePrice(Asset $asset): float
    {
        if (!empty($asset->base_price_override) && (float)$asset->base_price_override > 0) {
            return (float)$asset->base_price_override;
        }

        $raw = strtoupper($asset->symbol);
        $clean = strtoupper(str_replace(['(OTC)', 'OTC', ' ', '/', '-', '_', '.'], '', $asset->symbol));
        $type = strtolower($asset->type ?? 'forex');

        // Crypto
        if (str_contains($clean, 'BTC') || str_contains($raw, 'BITCOIN')) return 73918.44;
        if (str_contains($clean, 'ETH') || str_contains($raw, 'ETHEREUM')) return 2640.00;
        if (str_contains($clean, 'SOL') || str_contains($raw, 'SOLANA')) return 148.50;
        if (str_contains($clean, 'BNB')) return 721.61;
        if (str_contains($clean, 'XRP') || str_contains($raw, 'RIPPLE')) return 0.5850;
        if (str_contains($clean, 'DOGE')) return 0.1250;
        if (str_contains($clean, 'LTC')) return 68.50;
        if (str_contains($clean, 'AZA')) return 1.2500;

        // Commodities
        if (str_contains($clean, 'XAU') || str_contains($raw, 'GOLD')) return 2680.50;
        if (str_contains($clean, 'XAG') || str_contains($raw, 'SILVER')) return 31.40;
        if (str_contains($clean, 'OIL') || str_contains($raw, 'CRUDE') || str_contains($raw, 'BRENT')) return 74.80;

        // Stocks
        if ($clean === 'AAPL' || str_contains($raw, 'APPLE')) return 228.50;
        if ($clean === 'BA' || str_contains($raw, 'BOEING')) return 155.20;
        if ($clean === 'MSFT' || str_contains($raw, 'MICROSOFT')) return 425.80;
        if ($clean === 'TSLA' || str_contains($raw, 'TESLA')) return 218.40;

        // Indices
        if (str_contains($clean, 'SP500') || str_contains($clean, 'SPX')) return 5750.00;
        if (str_contains($clean, 'NASDAQ') || str_contains($clean, 'NDX')) return 20250.00;
        if (str_contains($clean, 'DOW') || str_contains($clean, 'DJI')) return 42800.00;

        // Major & Cross Forex
        if ($clean === 'EURUSD') return 1.15488;
        if ($clean === 'GBPUSD') return 1.29450;
        if ($clean === 'USDJPY') return 152.65;
        if ($clean === 'USDCHF') return 0.88420;
        if ($clean === 'USDCAD') return 1.36450;
        if ($clean === 'AUDUSD') return 0.65820;
        if ($clean === 'NZDUSD') return 0.58146;
        if ($clean === 'EURGBP') return 0.85280;
        if ($clean === 'EURJPY') return 164.80;
        if ($clean === 'GBPJPY') return 197.50;

        if ($clean === 'AUDCAD') return 0.89780;
        if ($clean === 'AUDCHF') return 0.58350;
        if ($clean === 'AUDJPY') return 98.65;
        if ($clean === 'AUDNZD') return 1.09950;
        if ($clean === 'CADCHF') return 0.64820;
        if ($clean === 'CADJPY') return 111.85;
        if ($clean === 'CHFJPY') return 172.50;
        if ($clean === 'EURAUD') return 1.65050;
        if ($clean === 'EURCAD') return 1.48220;
        if ($clean === 'EURCHF') return 0.96050;
        if ($clean === 'EURNZD') return 1.81500;
        if ($clean === 'GBPAUD') return 1.96650;
        if ($clean === 'GBPCAD') return 1.76600;
        if ($clean === 'GBPCHF') return 1.14450;
        if ($clean === 'GBPNZD') return 2.16250;
        if ($clean === 'NZDCAD') return 0.81650;
        if ($clean === 'NZDCHF') return 0.52950;
        if ($clean === 'NZDJPY') return 91.35;

        // Exotics
        if ($clean === 'USDBDT') return 119.50;
        if ($clean === 'USDBRL') return 5.7520;
        if ($clean === 'USDEGP') return 49.25;
        if ($clean === 'USDIDR') return 17688.37;
        if ($clean === 'USDINR') return 84.40;
        if ($clean === 'USDMXN') return 20.15;
        if ($clean === 'USDNGN') return 1330.18;
        if ($clean === 'USDPHP') return 58.75;
        if ($clean === 'USDPKR') return 278.50;
        if ($clean === 'USDTRY') return 34.35;
        if ($clean === 'USDZAR') return 17.65;

        if (str_contains($clean, 'JPY')) return 152.50;
        if (str_contains($clean, 'CHF')) return 0.88500;

        if ($type === 'crypto') return 125.00;
        if ($type === 'commodity') return 85.00;
        if ($type === 'stock') return 150.00;
        if ($type === 'index') return 5650.00;

        return 1.15488;
    }

    /**
     * Deterministic symbol seed.
     */
    public static function getSymbolSeed(string $symbol): int
    {
        $sym = strtoupper(trim($symbol));
        $hash = 0;
        $len = strlen($sym);
        for ($i = 0; $i < $len; $i++) {
            $hash = (($hash * 31) + ord($sym[$i])) & 0x7FFFFFFF;
        }
        return $hash % 1000000;
    }

    /**
     * Deterministic pseudo-random number generator anchored by key and slot.
     * Returns float in [-1.0, 1.0].
     */
    public static function deterministicHash(string $key, int $slot = 0): float
    {
        $hash = hash('crc32b', "{$key}_{$slot}");
        $val  = hexdec($hash);
        return (($val % 2000000) / 1000000.0) - 1.0;
    }

    public static function deterministicNoise(int $seed, int $slot): float
    {
        $n = ($seed + $slot * 374761393) & 0x7FFFFFFF;
        $n = ($n ^ ($n >> 13)) * 1274126177;
        $n = $n & 0x7FFFFFFF;
        $n = ($n ^ ($n >> 16));
        return (($n % 2000000) / 1000000.0) - 1.0;
    }

    /**
     * Smooth, continuous C2-interpolated noise (Perlin-style) - eliminates all sudden jumps.
     */
    public static function smoothNoise(int $seed, float $t, float $period = 8.0): float
    {
        $pos = $t / $period;
        $k = (int) floor($pos);
        $f = $pos - $k;
        // Quintic smoothstep for continuous derivatives
        $s = $f * $f * $f * ($f * ($f * 6.0 - 15.0) + 10.0);
        $v0 = self::deterministicNoise($seed, $k);
        $v1 = self::deterministicNoise($seed, $k + 1);
        return $v0 + $s * ($v1 - $v0);
    }

    /**
     * Calculate continuous deterministic price for any asset at any timestamp.
     * 100% synchronized across all clients, tabs, and server verification.
     * Generates realistic 5-year macro economic cycles, annual seasonality,
     * quarterly trends, weekly momentum, and smooth micro-ticks.
     */
    public static function getDeterministicPrice(Asset $asset, float $timestamp): float
    {
        self::checkAndResetExpiredControl($asset);

        $basePrice = self::getBasePrice($asset);
        $precision = self::getPrecision($basePrice, $asset);
        $pip = pow(10, -$precision);
        $autoPrice = self::calculateOrganicPrice($asset, $timestamp, $basePrice);
        $state = self::marketStateForTimestamp($asset, $timestamp);

        if ($state && in_array($state->mode, ['pump', 'crash'], true)) {
            $price = self::calculateControlledPrice(
                $asset,
                $timestamp,
                $state->mode,
                (float) $state->anchor_price,
                (int) $state->activated_at->timestamp,
                $basePrice,
                $precision,
                $pip
            );

            return self::applyLastSecondsSteering($asset, $timestamp, $price);
        }

        $mode = $asset->control_mode ?? 'auto';
        if (in_array($mode, ['pump', 'crash'], true) && $asset->control_anchor_price && $asset->control_activated_at) {
            $price = self::calculateControlledPrice(
                $asset,
                $timestamp,
                $mode,
                (float) $asset->control_anchor_price,
                (int) $asset->control_activated_at->timestamp,
                $basePrice,
                $precision,
                $pip
            );

            return self::applyLastSecondsSteering($asset, $timestamp, $price);
        }

        return self::applyLastSecondsSteering($asset, $timestamp, $autoPrice);
    }

    private static function calculateOrganicPrice(Asset $asset, float $timestamp, ?float $basePrice = null): float
    {
        $basePrice  = $basePrice ?: self::getBasePrice($asset);
        $precision  = self::getPrecision($basePrice, $asset);
        $pip        = pow(10, -$precision);
        $volatility = $basePrice * 0.00030;


        $seed = self::getSymbolSeed($asset->symbol);
        $t = $timestamp;
        $twoPi = 2 * M_PI;

        // --- Multi-Year Macro & Secular Waves (4-5 Year Historical Structure) ---
        // 5-year macro economic cycle (~157,680,000s)
        $w5y = sin((($t + (($seed * 1337) % 157680000)) / 157680000.0) * $twoPi) * 45.0;
        // 2-year macro swing (~63,072,000s)
        $w2y = cos((($t + (($seed * 773) % 63072000)) / 63072000.0) * $twoPi) * 28.0;
        // 1-year annual seasonal wave (~31,536,000s)
        $w1y = sin((($t + (($seed * 521) % 31536000)) / 31536000.0) * $twoPi) * 18.0;
        // 90-day quarterly market cycle (~7,776,000s)
        $w3m = cos((($t + (($seed * 317) % 7776000)) / 7776000.0) * $twoPi) * 10.0;
        // 30-day monthly swing wave (~2,592,000s)
        $w1m = sin((($t + (($seed * 199) % 2592000)) / 2592000.0) * $twoPi) * 6.0;
        // 7-day weekly market trend (~604,800s)
        $w1w = cos((($t + (($seed * 97) % 604800)) / 604800.0) * $twoPi) * 4.0;
        // 24-hour daily session oscillation (~86,400s)
        $w1d = sin((($t + (($seed * 43) % 86400)) / 86400.0) * $twoPi) * 2.5;

        // --- Intraday Intermediate Waves ---
        // 4-hour intermediate momentum (~14,400s)
        $w4h = cos((($t + ($seed % 14400)) / 14400.0) * $twoPi) * 1.5;
        // 1-hour swing (~3,600s)
        $w1h = sin((($t + ($seed % 3600)) / 3600.0) * $twoPi) * 0.9;
        // 15-min macro wave
        $w15m = sin(($t + ($seed % 997)) / 900.0) * 0.6;
        // 4-min swing wave
        $w4m = sin(($t + ($seed % 499)) / 240.0) * 0.4;
        // 75-sec micro wave
        $w75s = cos(($t + ($seed % 251)) / 75.0) * 0.25;

        // --- Multi-Scale Smooth Organic Noise (C2-Continuous) ---
        $nDay = self::smoothNoise($seed + 77, $t, 86400.0) * 4.0;
        $nHr  = self::smoothNoise($seed + 33, $t, 3600.0) * 1.5;
        $nMin = self::smoothNoise($seed, $t, 14.0) * 0.35;
        $nSec = self::smoothNoise($seed + 101, $t, 4.5) * 0.15;

        $totalWave = ($w5y + $w2y + $w1y + $w3m + $w1m + $w1w + $w1d + $w4h + $w1h + $w15m + $w4m + $w75s + $nDay + $nHr + $nMin + $nSec) * $volatility;

        $price = max($pip, $basePrice + $totalWave);
        return round($price, $precision);
    }

    private static function calculateControlledPrice(
        Asset $asset,
        float $timestamp,
        string $mode,
        float $anchorPrice,
        int $activatedAt,
        float $basePrice,
        int $precision,
        float $pip
    ): float {
        if ($timestamp < $activatedAt || $anchorPrice <= 0) {
            return self::calculateOrganicPrice($asset, $timestamp, $basePrice);
        }

        $elapsed = max(0.0, $timestamp - $activatedAt);
        $duration = max(60, ((int) ($asset->pump_duration_minutes ?? 15)) * 60);
        $progress = min(1.0, $elapsed / $duration);
        $smoothProgress = $progress * $progress * (3.0 - (2.0 * $progress));
        $percent = $mode === 'pump'
            ? max(0.0, (float) ($asset->max_pump_percent ?? 0.0))
            : max(0.0, (float) ($asset->max_crash_percent ?? 0.0));

        $autoAtActivation = self::calculateOrganicPrice($asset, (float) $activatedAt, $basePrice);
        $autoAtTimestamp = self::calculateOrganicPrice($asset, $timestamp, $basePrice);
        $organicDelta = $autoAtTimestamp - $autoAtActivation;
        $drift = $anchorPrice * ($percent / 100.0) * $smoothProgress;
        if ($mode === 'crash') {
            $drift *= -1;
        }

        return round(max($pip, $anchorPrice + $organicDelta + $drift), $precision);
    }

    /**
     * Generate historical candlestick data for chart display.
     * Guaranteed 100% identical and synchronized for every user, session, and refresh.
     * Supports multi-year history (up to 5+ years of historical candles).
     */
    public static function generateCandles(Asset $asset, ?int $count = null, int $timeframeSec = 60, ?int $endTime = null): array
    {
        self::checkAndResetExpiredControl($asset);

        $now = $endTime ? (int)$endTime : time();
        $tf  = max(5, $timeframeSec);

        // Smart default count based on timeframe if not explicitly provided
        if ($count === null || $count <= 0) {
            if ($tf >= 86400) {
                $count = 1825; // 5 full years of daily candles (365 * 5)
            } elseif ($tf >= 14400) {
                $count = 2000; // ~11 months of 4-hour candles
            } elseif ($tf >= 3600) {
                $count = 1500; // ~2 months of 1-hour candles
            } elseif ($tf >= 1800) {
                $count = 1200; // 25 days of 30-min candles
            } elseif ($tf >= 900) {
                $count = 1200; // 12.5 days of 15-min candles
            } elseif ($tf >= 300) {
                $count = 1000; // ~3.5 days of 5-min candles
            } elseif ($tf >= 60) {
                $count = 1000; // ~16.6 hours of 1-min candles
            } else {
                $count = 800;  // 800 micro candles
            }
        } else {
            $count = min(5000, max(10, $count));
        }

        $currentCandleStart = (int) floor($now / $tf) * $tf;
        $basePrice = self::getBasePrice($asset);
        $precision = self::getPrecision($basePrice, $asset);
        $pip = pow(10, -$precision);
        $seed = self::getSymbolSeed($asset->symbol);

        $candles = [];
        $prevClose = null;

        for ($i = $count - 1; $i >= 0; $i--) {
            $cTime = $currentCandleStart - ($i * $tf);

            // Strictly continuous Open
            if ($prevClose !== null) {
                $open = $prevClose;
            } else {
                $open = self::getDeterministicPrice($asset, (float) $cTime);
            }

            // Close
            if ($i === 0 && !$endTime) {
                $close = self::getDeterministicPrice($asset, (float) time());
            } else {
                $close = self::getDeterministicPrice($asset, (float) ($cTime + $tf));
            }

            // Sample continuous price path inside the candle
            $p1 = self::getDeterministicPrice($asset, (float) ($cTime + $tf * 0.20));
            $p2 = self::getDeterministicPrice($asset, (float) ($cTime + $tf * 0.45));
            $p3 = self::getDeterministicPrice($asset, (float) ($cTime + $tf * 0.70));
            $p4 = self::getDeterministicPrice($asset, (float) ($cTime + $tf * 0.88));

            $bodyMax = max($open, $close);
            $bodyMin = min($open, $close);
            $body = $bodyMax - $bodyMin;

            $trajHigh = max($bodyMax, $p1, $p2, $p3, $p4);
            $trajLow  = min($bodyMin, $p1, $p2, $p3, $p4);

            // Bounded realistic micro-wick extensions
            $uHash = abs(self::deterministicNoise($seed, (int)($cTime * 3 + 1)));
            $lHash = abs(self::deterministicNoise($seed, (int)($cTime * 5 + 3)));

            $wickUnit = max($pip * 1.0, min($pip * 3.5, $body * 0.22));
            $extraUpper = round($wickUnit * (0.2 + 0.8 * $uHash), $precision);
            $extraLower = round($wickUnit * (0.2 + 0.8 * $lHash), $precision);

            $high = round(max($bodyMax, $trajHigh + $extraUpper), $precision);
            $low  = round(max($pip, min($bodyMin, $trajLow - $extraLower)), $precision);

            if ($high < $bodyMax) $high = $bodyMax;
            if ($low > $bodyMin) $low = $bodyMin;

            $candles[] = [
                'time'  => $cTime,
                'open'  => $open,
                'high'  => $high,
                'low'   => $low,
                'close' => $close,
            ];

            $prevClose = $close;
        }

        return $candles;
    }

    /**
     * Generate real-time tick for live chart synchronization.
     */
    public static function generateLiveTick(Asset $asset, ?float $lastClose = null): array
    {
        self::checkAndResetExpiredControl($asset);

        $now   = time();
        $price = self::getDeterministicPrice($asset, $now);
        $precision = self::getPrecision($price);

        return [
            'symbol'       => $asset->symbol,
            'price'        => $price,
            'precision'    => $precision,
            'control_mode' => $asset->control_mode ?? 'auto',
            'win_side'     => $asset->win_side ?? 'none',
            'timestamp'    => $now,
        ];
    }

    /**
     * Simulate a single market price at the current moment.
     */
    public static function simulatePrice(Asset $asset): float
    {
        self::checkAndResetExpiredControl($asset);
        return self::getDeterministicPrice($asset, time());
    }

    // -----------------------------------------------------------------
    // Real payout / change / sentiment (P0 fix, Team 4).
    //
    // There is no candles table in this codebase: a candle close IS
    // getDeterministicPrice() evaluated at a candle-end timestamp
    // (see generateCandles(): close = getDeterministicPrice(cTime+tf),
    // and the forming candle's close = getDeterministicPrice(now)).
    // The changes below are therefore computed from exactly the same
    // closes the chart renders, by evaluating the price at the
    // relevant 1-minute candle boundaries instead of regenerating
    // whole candle series. No hash / random derivation is used
    // anywhere in this section.
    // -----------------------------------------------------------------

    /**
     * Documented fallback when an asset has no stored payout.
     * Matches the assets migration default (85).
     */
    public const DEFAULT_PAYOUT_PERCENT = 85;

    /**
     * Stored admin-configured payout %, or the documented default.
     * Never hash/random-derived.
     */
    public static function payoutPercent(Asset $asset): int
    {
        $pct = (int) ($asset->payout_percent ?? 0);
        return $pct > 0 ? min(99, $pct) : self::DEFAULT_PAYOUT_PERCENT;
    }

    /**
     * % change between the latest 1-min candle close (the live price
     * now) and the 1-min candle close $secondsAgo seconds earlier:
     * (latest - reference) / reference * 100.
     *
     * The reference is the close of the 1-min candle containing the
     * instant ($now - $secondsAgo), i.e. getDeterministicPrice() at
     * that candle's end timestamp. Returns null when the reference
     * price is not positive.
     */
    public static function changePercent(Asset $asset, int $secondsAgo, ?int $now = null): ?float
    {
        $now = $now ?? time();
        $refEnd = (int) floor(($now - $secondsAgo) / 60) * 60 + 60;
        if ($refEnd >= $now) {
            return 0.0;
        }
        $latest = self::getDeterministicPrice($asset, (float) $now);
        $ref = self::getDeterministicPrice($asset, (float) $refEnd);
        if ($ref <= 0) {
            return null;
        }
        return round((($latest - $ref) / $ref) * 100, 2);
    }

    /**
     * Buy vs Sell volume (by investment) for trades opened on this
     * asset in the last hour, grouped by direction. Returns
     * [asset_id => ['up' => vol, 'down' => vol, 'count' => n]].
     * One query for any number of assets (bulk path).
     */
    public static function recentTradeVolumes(array $assetIds, ?int $now = null): array
    {
        $now = $now ?? time();
        $assetIds = array_values(array_unique(array_map('intval', $assetIds)));
        $map = [];
        foreach ($assetIds as $id) {
            $map[$id] = ['up' => 0.0, 'down' => 0.0, 'count' => 0];
        }
        if ($assetIds === []) {
            return $map;
        }

        $since = Carbon::createFromTimestamp($now - 3600);
        $rows = Trade::whereIn('asset_id', $assetIds)
            ->where('opened_at', '>=', $since)
            ->selectRaw('asset_id, direction, SUM(investment) as vol, COUNT(*) as cnt')
            ->groupBy('asset_id', 'direction')
            ->get();

        foreach ($rows as $row) {
            $id = (int) $row->asset_id;
            if (!isset($map[$id])) {
                continue;
            }
            $dir = $row->direction === 'down' ? 'down' : 'up';
            $map[$id][$dir] += (float) $row->vol;
            $map[$id]['count'] += (int) $row->cnt;
        }

        return $map;
    }

    /**
     * Traders sentiment for one asset.
     *
     * Primary source: the platform's OWN trades — volume-weighted
     * Buy (up) vs Sell (down) share among trades opened on this
     * asset in the last hour. Fallback (no recent trades): trend of
     * the last 20 completed 1-min candles — share of closes that
     * rose vs fell; a flat market (no rises/falls) reads 50/50.
     *
     * Returns ['buy_pct', 'sell_pct', 'source' ('trades'|'candles'),
     * 'trade_count', 'buy_volume', 'sell_volume', 'up_candles',
     * 'down_candles']. $tradeVolumes is the pre-aggregated bulk map
     * entry for this asset (see recentTradeVolumes()); pass null to
     * query it for this asset only.
     */
    public static function sentimentForAsset(Asset $asset, ?int $now = null, ?array $tradeVolumes = null): array
    {
        $now = $now ?? time();

        if ($tradeVolumes === null) {
            $tradeVolumes = self::recentTradeVolumes([(int) $asset->id], $now)[(int) $asset->id]
                ?? ['up' => 0.0, 'down' => 0.0, 'count' => 0];
        }

        $buyVol = (float) ($tradeVolumes['up'] ?? 0);
        $sellVol = (float) ($tradeVolumes['down'] ?? 0);
        $count = (int) ($tradeVolumes['count'] ?? 0);

        if ($count > 0 && ($buyVol + $sellVol) > 0) {
            $buyPct = (int) round($buyVol / ($buyVol + $sellVol) * 100);
            return [
                'buy_pct' => $buyPct,
                'sell_pct' => 100 - $buyPct,
                'source' => 'trades',
                'trade_count' => $count,
                'buy_volume' => round($buyVol, 2),
                'sell_volume' => round($sellVol, 2),
                'up_candles' => null,
                'down_candles' => null,
            ];
        }

        // Fallback: last 20 completed 1-min candles (closes at the
        // 1-min boundaries ending at/before now).
        $up = 0;
        $down = 0;
        $prevClose = null;
        for ($k = 20; $k >= 0; $k--) {
            $end = (int) floor($now / 60) * 60 - ($k * 60);
            $close = self::getDeterministicPrice($asset, (float) $end);
            if ($prevClose !== null) {
                if ($close > $prevClose) {
                    $up++;
                } elseif ($close < $prevClose) {
                    $down++;
                }
            }
            $prevClose = $close;
        }

        $total = $up + $down;
        $buyPct = $total > 0 ? (int) round($up / $total * 100) : 50;

        return [
            'buy_pct' => $buyPct,
            'sell_pct' => 100 - $buyPct,
            'source' => 'candles',
            'trade_count' => 0,
            'buy_volume' => 0.0,
            'sell_volume' => 0.0,
            'up_candles' => $up,
            'down_candles' => $down,
        ];
    }

    /**
     * All real per-asset values the UI needs in one array.
     *
     * payout_1m_pct / payout_5m_pct are BOTH the stored
     * admin-configured payout: there is no per-expiry payout in the
     * DB, and the old 5-min value (stored payout minus a crc32-derived
     * 0-4) was fiction. change_5m_pct / change_60m_pct /
     * change_24h_pct come from actual candle closes (see changePercent()).
     */
    public static function assetStats(Asset $asset, ?int $now = null, ?array $tradeVolumes = null): array
    {
        $now = $now ?? time();
        $payout = self::payoutPercent($asset);
        $sentiment = self::sentimentForAsset($asset, $now, $tradeVolumes);

        return [
            'asset_id' => (int) $asset->id,
            'symbol' => $asset->symbol,
            'payout_1m_pct' => $payout,
            'payout_5m_pct' => $payout,
            'change_5m_pct' => self::changePercent($asset, 300, $now),
            'change_60m_pct' => self::changePercent($asset, 3600, $now),
            'change_24h_pct' => self::changePercent($asset, 86400, $now),
            'sentiment_buy_pct' => $sentiment['buy_pct'],
            'sentiment_sell_pct' => $sentiment['sell_pct'],
            'sentiment_source' => $sentiment['source'],
            'sentiment_trade_count' => $sentiment['trade_count'],
        ];
    }

    /**
     * assetStats() for a whole asset list with a single trades query.
     */
    public static function assetStatsBulk($assets, ?int $now = null): array
    {
        $now = $now ?? time();
        $ids = [];
        foreach ($assets as $asset) {
            $ids[] = (int) $asset->id;
        }
        $volumes = self::recentTradeVolumes($ids, $now);

        $out = [];
        foreach ($assets as $asset) {
            $out[] = self::assetStats(
                $asset,
                $now,
                $volumes[(int) $asset->id] ?? ['up' => 0.0, 'down' => 0.0, 'count' => 0]
            );
        }
        return $out;
    }

    /**
     * Auto-reset control mode when duration expires.
     */
    public static function checkAndResetExpiredControl(Asset $asset): void
    {
        if ($asset->exists && $asset->control_mode !== 'auto' && $asset->control_expires_at) {
            if (now()->gte($asset->control_expires_at)) {
                $expiredAt = $asset->control_expires_at;
                $currentPrice = self::getDeterministicPriceWithoutExpiryCheck($asset, (float) $expiredAt->timestamp);
                self::closeActiveMarketState($asset, $expiredAt);
                $asset->update([
                    'control_mode' => 'auto',
                    'control_expires_at' => null,
                    'control_anchor_price' => null,
                    'control_activated_at' => null,
                    'control_version' => ((int) ($asset->control_version ?? 0)) + 1,
                    'base_price_override' => $currentPrice,
                ]);
                $asset->refresh();
                self::clearMarketStateCache($asset->id);
            }
        }
    }

    public static function prepareControlTransition(Asset $asset, string $newMode, ?Carbon $expiresAt = null): array
    {
        $now = now();
        $currentMode = $asset->control_mode ?? 'auto';
        $version = ((int) ($asset->control_version ?? 0)) + 1;
        $updates = [
            'control_mode' => $newMode,
            'control_expires_at' => $expiresAt,
            'control_version' => $version,
        ];

        if (in_array($newMode, ['pump', 'crash'], true)) {
            $continuingSameControl = $currentMode === $newMode
                && $asset->control_anchor_price
                && $asset->control_activated_at;

            if ($continuingSameControl) {
                return [
                    'control_mode' => $newMode,
                    'control_expires_at' => $expiresAt,
                ];
            }

            $anchorPrice = self::getDeterministicPriceWithoutExpiryCheck($asset, (float) time());
            self::closeActiveMarketState($asset, $now);
            AssetMarketState::create([
                'asset_id' => $asset->id,
                'mode' => $newMode,
                'anchor_price' => $anchorPrice,
                'activated_at' => $now,
                'ended_at' => null,
                'version' => $version,
            ]);

            $updates['control_anchor_price'] = $anchorPrice;
            $updates['control_activated_at'] = $now;
            self::clearMarketStateCache($asset->id);

            return $updates;
        }

        if (in_array($currentMode, ['pump', 'crash'], true)) {
            $currentPrice = self::getDeterministicPriceWithoutExpiryCheck($asset, (float) time());
            self::closeActiveMarketState($asset, $now);
            $updates['base_price_override'] = $currentPrice;
        }

        $updates['control_anchor_price'] = null;
        $updates['control_activated_at'] = null;
        self::clearMarketStateCache($asset->id);

        return $updates;
    }

    private static function getDeterministicPriceWithoutExpiryCheck(Asset $asset, float $timestamp): float
    {
        $basePrice = self::getBasePrice($asset);
        $precision = self::getPrecision($basePrice, $asset);
        $pip = pow(10, -$precision);
        $mode = $asset->control_mode ?? 'auto';

        if (in_array($mode, ['pump', 'crash'], true) && $asset->control_anchor_price && $asset->control_activated_at) {
            return self::calculateControlledPrice(
                $asset,
                $timestamp,
                $mode,
                (float) $asset->control_anchor_price,
                (int) $asset->control_activated_at->timestamp,
                $basePrice,
                $precision,
                $pip
            );
        }

        $state = self::marketStateForTimestamp($asset, $timestamp);
        if ($state && in_array($state->mode, ['pump', 'crash'], true)) {
            return self::calculateControlledPrice(
                $asset,
                $timestamp,
                $state->mode,
                (float) $state->anchor_price,
                (int) $state->activated_at->timestamp,
                $basePrice,
                $precision,
                $pip
            );
        }

        return self::calculateOrganicPrice($asset, $timestamp, $basePrice);
    }

    private static function closeActiveMarketState(Asset $asset, Carbon $endedAt): void
    {
        if (!$asset->exists) {
            return;
        }

        AssetMarketState::where('asset_id', $asset->id)
            ->whereNull('ended_at')
            ->update(['ended_at' => $endedAt]);
    }

    private static function marketStateForTimestamp(Asset $asset, float $timestamp): ?AssetMarketState
    {
        if (!$asset->exists) {
            return null;
        }

        $assetId = (int) $asset->id;
        if (!array_key_exists($assetId, self::$marketStateCache)) {
            self::$marketStateCache[$assetId] = AssetMarketState::where('asset_id', $assetId)
                ->orderBy('activated_at')
                ->get();
        }

        $target = Carbon::createFromTimestamp((int) floor($timestamp));
        $match = null;
        foreach (self::$marketStateCache[$assetId] as $state) {
            if ($state->activated_at->lte($target) && (!$state->ended_at || $state->ended_at->gt($target))) {
                $match = $state;
            }
        }

        return $match;
    }

    private static function clearMarketStateCache(int $assetId): void
    {
        unset(self::$marketStateCache[$assetId]);
    }

    /**
     * Which way must the price move for this trade to get its forced
     * outcome? Returns 'up', 'down', or null when nothing is forced.
     *
     * Priority (same as TradeResultService::settle):
     *  1. per-trade override (admin Win/Loss button on a running trade)
     *  2. pair control_mode: force_win / force_loss / pump / crash
     *  3. global simulation mode (top Win/Loss button in Live Trades)
     * win_side rules are deliberately left out here: they depend on
     * the other pending trades and are still enforced at settlement.
     */
    public static function forcedSideForTrade(Trade $trade, ?Asset $asset = null, ?string $globalMode = null): ?string
    {
        $direction = $trade->direction;
        $opposite = $direction === 'up' ? 'down' : 'up';

        $override = $trade->admin_override ?? 'none';
        if ($override === 'force_win') {
            return $direction;
        }
        if ($override === 'force_loss') {
            return $opposite;
        }

        $asset = $asset ?? $trade->asset;
        $pairMode = $asset ? ($asset->control_mode ?? 'auto') : 'auto';
        if ($pairMode === 'force_win' || $pairMode === 'win') {
            return $direction;
        }
        if ($pairMode === 'force_loss' || $pairMode === 'loss') {
            return $opposite;
        }
        if ($pairMode === 'pump') {
            return 'up';
        }
        if ($pairMode === 'crash') {
            return 'down';
        }

        $globalMode = $globalMode ?? TradeSimulationControl::currentMode();
        if ($globalMode === TradeSimulationControl::MODE_SIMULATION_WIN) {
            return $direction;
        }
        if ($globalMode === TradeSimulationControl::MODE_SIMULATION_LOSS) {
            return $opposite;
        }

        return null;
    }

    /**
     * The exact close price a forced trade must end at: just past its
     * open price (10 pips) on the forced side. Settlement uses this
     * same target, so the chart and the result always agree.
     */
    public static function steeringTargetPrice(Trade $trade, string $forcedSide, ?int $precision = null): float
    {
        $openPrice = (float) $trade->open_price;
        $precision = $precision ?? self::getPrecision($openPrice, $trade->asset ?? null);
        $pip = pow(10, -$precision);
        $margin = self::STEER_MARGIN_PIPS * $pip;
        $target = $forcedSide === 'up' ? $openPrice + $margin : $openPrice - $margin;

        return round(max($pip, $target), $precision);
    }

    /**
     * Glide the live price towards the forced outcome in the last
     * STEER_SECONDS before a forced trade expires.
     *
     * price(t) = organic(t) + progress * (target - organic(t)),
     * progress 0 at expiry-3s and 1 at expiry, so the move is
     * gradual and the price ends exactly on the target. Outside
     * the window the organic price is returned untouched.
     *
     * Full per-trade control: the shared price can only move one
     * way. If the trades expiring in this window need opposite
     * directions (e.g. an UP trade and a DOWN trade both forced
     * to lose), there is no steering at all — the chart stays on
     * its organic path and each trade is settled on its own
     * per-trade close price instead. That is how both can lose.
     * When all active trades agree on one side, the target is
     * chosen so every one of them wins/loses correctly: above
     * the highest open for 'up', below the lowest open for 'down'.
     */
    private static function applyLastSecondsSteering(Asset $asset, float $timestamp, float $price): float
    {
        if (!$asset->exists || !$asset->id) {
            return $price;
        }

        // Steering is a live-market effect only. Historical candles
        // are far from "now", so skip the database lookup for them.
        $now = time();
        if ($timestamp < $now - 5 || $timestamp > $now + 120) {
            return $price;
        }

        $candidates = self::steeringCandidates($asset);
        if ($candidates === []) {
            return $price;
        }

        $precision = self::getPrecision($price, $asset);
        $pip = pow(10, -$precision);

        $forcedSide = null;
        $targetOpen = null;
        $maxProgress = 0.0;

        foreach ($candidates as $candidate) {
            $expiresAt = $candidate['expires_at'];
            $windowStart = $expiresAt - self::STEER_SECONDS;
            if ($timestamp < $windowStart || $timestamp > $expiresAt + 1) {
                continue;
            }

            $progress = max(0.0, min(1.0, ($timestamp - $windowStart) / self::STEER_SECONDS));
            if ($progress <= 0.0) {
                continue;
            }

            if ($forcedSide === null) {
                $forcedSide = $candidate['forced_side'];
                $targetOpen = $candidate['open_price'];
            } elseif ($candidate['forced_side'] !== $forcedSide) {
                // Opposite forced outcomes on the same pair:
                // do not steer the shared price either way.
                return $price;
            } elseif ($forcedSide === 'up') {
                $targetOpen = max($targetOpen, $candidate['open_price']);
            } else {
                $targetOpen = min($targetOpen, $candidate['open_price']);
            }

            $maxProgress = max($maxProgress, $progress);
        }

        if ($forcedSide === null || $targetOpen === null || $maxProgress <= 0.0) {
            return $price;
        }

        $margin = self::STEER_MARGIN_PIPS * $pip;
        $target = $forcedSide === 'up'
            ? $targetOpen + $margin
            : $targetOpen - $margin;
        $target = max($pip, $target);

        return round($price + $maxProgress * ($target - $price), $precision);
    }

    /**
     * Pending forced trades on this asset, soonest expiry first.
     * Cached once per request: generateCandles() calls
     * getDeterministicPrice() thousands of times per page load.
     *
     * The forced side comes from admin / pair / global control, or
     * from the demo 8/2 cycle for demo trades with no other force.
     * Demo positions are peeked in expiry order, counting settled
     * demo trades plus earlier-pending demo trades for the same
     * user, so the peek matches what settle() will decide later.
     */
    private static function steeringCandidates(Asset $asset): array
    {
        $assetId = (int) $asset->id;
        if (array_key_exists($assetId, self::$steeringCache)) {
            return self::$steeringCache[$assetId];
        }

        $candidates = [];
        try {
            $globalMode = TradeSimulationControl::currentMode();
            $trades = Trade::where('asset_id', $assetId)
                ->where('result', 'pending')
                ->orderBy('id')
                ->limit(20)
                ->get();

            // Sort by expiry so demo positions are assigned in the
            // order the trades will actually settle.
            $trades = $trades->sortBy(function (Trade $trade) {
                $openedAt = $trade->opened_at ?: $trade->created_at;
                return ($openedAt ? (int) $openedAt->timestamp : 0) + (int) ($trade->duration_seconds ?: 60);
            })->values();

            $demoUserIds = $trades
                ->filter(fn (Trade $t) => $t->account_type === 'demo' && self::forcedSideForTrade($t, $asset, $globalMode) === null)
                ->pluck('user_id')->unique()->values()->all();
            $settledDemoCounts = [];
            if ($demoUserIds !== []) {
                $settledDemoCounts = Trade::whereIn('user_id', $demoUserIds)
                    ->where('account_type', 'demo')
                    ->whereIn('result', ['win', 'loss'])
                    ->selectRaw('user_id, COUNT(*) as cnt')
                    ->groupBy('user_id')
                    ->pluck('cnt', 'user_id')->all();
            }
            $demoOffsets = [];

            foreach ($trades as $trade) {
                $forcedSide = self::forcedSideForTrade($trade, $asset, $globalMode);

                if ($trade->account_type === 'demo') {
                    $userId = (int) $trade->user_id;
                    $effectiveCount = (int) ($settledDemoCounts[$userId] ?? 0) + (int) ($demoOffsets[$userId] ?? 0);
                    $demoOffsets[$userId] = (int) ($demoOffsets[$userId] ?? 0) + 1;

                    if ($forcedSide === null) {
                        $forcedSide = DemoCycleService::forcedSideForTrade($trade, $effectiveCount);
                    }
                }

                if ($forcedSide === null) {
                    continue;
                }

                $openedAt = $trade->opened_at ?: $trade->created_at;
                if (!$openedAt) {
                    continue;
                }

                $candidates[] = [
                    'open_price' => (float) $trade->open_price,
                    'forced_side' => $forcedSide,
                    'expires_at' => (int) $openedAt->timestamp + (int) ($trade->duration_seconds ?: 60),
                ];
            }

            usort($candidates, fn ($a, $b) => $a['expires_at'] <=> $b['expires_at']);
        } catch (\Throwable) {
            // Steering must never break the chart: on any DB error,
            // fall back to the normal organic price.
            $candidates = [];
        }

        return self::$steeringCache[$assetId] = $candidates;
    }

    /**
     * Determine which side should win based on win_side rule.
     */
    public static function determineWinningSide(
        string $winSide,
        float  $upInvestment,
        float  $downInvestment,
        int    $upUsers,
        int    $downUsers
    ): ?string {
        return match ($winSide) {
            'majority_investment' => $upInvestment >= $downInvestment ? 'up' : 'down',
            'minority_investment' => $upInvestment < $downInvestment  ? 'up' : 'down',
            'majority_users'      => $upUsers >= $downUsers           ? 'up' : 'down',
            'minority_users'      => $upUsers < $downUsers            ? 'up' : 'down',
            default               => null, // 'none'
        };
    }
}
