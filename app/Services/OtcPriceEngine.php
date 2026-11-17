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

    /** Lazily built CRC-32/BZIP2 table for self::crc32bHex(). */
    private static ?array $crc32bTable = null;

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
     * Pure-PHP CRC-32/BZIP2 (poly 0x04C11DB7, init 0xFFFFFFFF,
     * refin=false, refout=false, xorout=0xFFFFFFFF), table-driven.
     *
     * Hostinger's PHP returns NON-STANDARD values from hash('crc32b')
     * on every PHP version 8.1-8.5 (crc32b('123456789') = cbf43926
     * instead of the standard fc891918), so the engine can no longer
     * trust the built-in hash. This returns the standard values —
     * identical to hash('crc32b') on a correct PHP — so the price
     * stream is byte-identical to the stream verified by the Node
     * harness. Returns the same 8-char lowercase hex string
     * hash('crc32b') returns: '123456789' -> 'fc891918',
     * '' -> '00000000'.
     */
    public static function crc32bHex(string $data): string
    {
        if (self::$crc32bTable === null) {
            $table = [];
            for ($i = 0; $i < 256; $i++) {
                $c = $i << 24;
                for ($j = 0; $j < 8; $j++) {
                    $c = ($c & 0x80000000) !== 0
                        ? ((($c << 1) ^ 0x04C11DB7) & 0xFFFFFFFF)
                        : (($c << 1) & 0xFFFFFFFF);
                }
                $table[$i] = $c;
            }
            self::$crc32bTable = $table;
        }

        $crc = 0xFFFFFFFF;
        $len = strlen($data);
        for ($i = 0; $i < $len; $i++) {
            $crc = ((($crc << 8) & 0xFFFFFFFF) ^ self::$crc32bTable[(($crc >> 24) ^ ord($data[$i])) & 0xFF]) & 0xFFFFFFFF;
        }

        return sprintf('%08x', ($crc ^ 0xFFFFFFFF) & 0xFFFFFFFF);
    }

    /**
     * Deterministic pseudo-random number generator anchored by key and slot.
     * Returns float in [-1.0, 1.0].
     */
    public static function deterministicHash(string $key, int $slot = 0): float
    {
        $hash = self::crc32bHex("{$key}_{$slot}");
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

    // -----------------------------------------------------------------
    // Team A — realistic organic price process (chart-realism phase).
    //
    // The organic price is a deterministic multi-scale random walk
    // built from dyadic "tent" functions (Lévy–Ciesielski / Schauder
    // construction of Brownian motion): at every level n the price
    // picks up exactly one tent containing the queried instant, so
    // the price is a pure function of (symbol, timestamp) — identical
    // for every client, tab and settlement — yet the path is jagged,
    // aperiodic, and has real runs, trends and volatility clustering.
    //
    // Level map (tent width in seconds is 2^(31-n), so level 0 is
    // ~68 years wide and level 30 is 1 second wide):
    //  * levels 0..6   — multi-year tents; damped 0.05x so the price
    //                  level MEAN-REVERTS to base over ~year
    //                  horizons instead of wandering with time since
    //                  the 2019 epoch (at full weight, multiplied by
    //                  a crypto scale of ~5.5, the level could drift
    //                  by orders of magnitude). Multi-week/month
    //                  trends (levels 7..13) are untouched.
    //  * hard cap      — the total log-return is also capped by a
    //                  smooth tanh at 30x the asset's daily vol, so
    //                  the level can NEVER drift by orders of
    //                  magnitude for any asset, however large its
    //                  scale. The cap almost never binds (typical
    //                  |log-return| is ~1/6 of it).
    //  * levels 10..12 — regime drift bias (4–17 day tents): a
    //                  trending regime's bias moves the price ~1–2%
    //                  over the regime, then it corrects.
    //  * levels 14..30 — local volatility: each tent's amplitude is
    //                  scaled by sigma_local at the tent's centre
    //                  (regime multiplier x volatility-clustering
    //                  multiplier). These levels carry almost none
    //                  of the point value, so scaling them moves the
    //                  *increments*, not the price level.
    //  * levels 24..30 — tick levels (<= ~2 min), additionally
    //                  boosted 1.5x so 1-minute candles have a
    //                  realistic size relative to the daily move.
    //  * clustering    — sigma_local depends on realised movement of
    //                  the unmodulated core walk over 1 h / 6 h /
    //                  1 d lookbacks, so quiet and stormy stretches
    //                  both occur.
    //  * jumps         — rare hash-triggered news spikes (~7/year):
    //                  a 45 s smooth ramp, then partial mean
    //                  reversion (a permanent step of the remainder).
    //
    // All seeded-hash based; NO unseeded randomness anywhere.
    //
    // Team B hook: samplePathExtrema() returns the true high/low of
    // the path over an interval — call it from generateCandles()
    // instead of the fabricated wick offsets.
    //
    // NOTE FOR DEPLOY: this replaces the old sine-wave model, so the
    // price LEVEL steps (by up to a few %) at deploy and the whole
    // regenerated history changes shape. That is inherent to a
    // price-model redesign; there is no candles table to migrate.
    // -----------------------------------------------------------------

    public const ORGANIC_EPOCH = 1546300800;             // 2019-01-01 00:00:00 UTC
    public const ORGANIC_SPAN_SECONDS = 2147483648;      // 2^31 s, 2019 -> ~2087
    public const ORGANIC_MAX_LEVEL = 30;                 // finest tents 1 s wide
    public const ORGANIC_CORE_MIN_LEVEL = 8;             // core walk: tents <= ~97 d
    public const ORGANIC_LONG_DAMP_MAX_LEVEL = 6;        // multi-year tents damped
    public const ORGANIC_LONG_DAMP_FACTOR = 0.05;        // ~removed: the level must
    // mean-revert to base. With these levels at full weight the
    // point value of the walk grows with time since the 2019 epoch
    // and, multiplied by a crypto scale of ~5.5, the level could
    // drift by orders of magnitude (Team B saw BTC x420). At 0.05
    // the level is dominated by tents of <= ~1.1 y width, so it is
    // bounded and reverts to base over ~year horizons; multi-week
    // and multi-month trends (levels 7..13) are untouched.
    // Belt and braces: the total log-return is also hard-capped
    // (see ORGANIC_LOGRET_CAP_MULT in calculateOrganicPrice).
    public const ORGANIC_BIAS_MIN_LEVEL = 10;            // regime drift: 17 d tents
    public const ORGANIC_BIAS_MAX_LEVEL = 12;            // regime drift: 4.3 d tents
    public const ORGANIC_SIGMA_MIN_LEVEL = 14;           // vol scaling from 1.5 d down
    public const ORGANIC_TICK_MIN_LEVEL = 24;            // tick boost from ~2 min down
    public const ORGANIC_TICK_BOOST = 1.2;
    public const ORGANIC_REGIME_SECONDS = 432000;        // 5-day regimes
    public const ORGANIC_JUMP_PROB_PER_DAY = 0.019;      // ~7 jumps / year
    public const ORGANIC_JUMP_RAMP_SECONDS = 45.0;
    public const ORGANIC_JUMP_REVERT_TAU = 1500.0;       // 25 min mean-reversion time
    // Calibration for the average sigma_local inflation of the daily
    // variance (regime mix x clustering); verified by the Node
    // harness so the realised daily sd lands on the target daily vol.
    public const ORGANIC_SCALE_CAL = 0.79;
    // Hard bound on the total log-return |log(price/base)|, as a
    // multiple of the asset's daily vol: 30x gives ~±18% for major
    // forex (0.0055 -> 0.165), ~±35% for gold, ~x2.9 for crypto
    // (0.035 -> 1.05). Applied as a smooth tanh cap so the function
    // stays continuous and differentiable; in normal operation the
    // level sits far inside the cap (typical |log-return| is
    // ~1/6 of it), so the cap almost never binds.
    public const ORGANIC_LOGRET_CAP_MULT = 30.0;

    private static array $organicRegimeCache = [];
    private static array $organicSigmaCache = [];
    private static array $organicCoreCache = [];
    private static ?array $organicHeights = null;

    /**
     * Unsigned 32-bit hash of a key string: CRC-32/BZIP2 followed by
     * a splitmix32-style avalanche finalizer.
     *
     * The finalizer is essential: CRC is linear over GF(2), so raw
     * CRCs of same-length keys (e.g. two symbols, or adjacent tent
     * indices) are related by a constant XOR mask and correlate
     * numerically. The finalizer breaks that linearity.
     * JS mirrors must use the same algorithm — check value for
     * "123456789" is 0xFC891918 for the CRC step; the finalizer is
     * the splitmix32 sequence below (verify in the Node harness).
     */
    /**
     * Exact low 32 bits of the product of two unsigned 32-bit
     * values, without overflow.
     *
     * PHP integers are signed 64-bit, so a direct 32-bit x 32-bit
     * multiply can overflow PHP_INT_MAX (9,223,372,036,854,775,807)
     * — e.g. 0xFFFFFFFF x 0x846CA68B = 9,542,184,824,200,190,325 —
     * and PHP then silently evaluates it as an IEEE-754 double,
     * losing the low bits. Splitting both operands into 16-bit
     * halves keeps every partial product below 2^32, so the result
     * is exact. Matches JS Math.imul(a, b) >>> 0 in the Node
     * harness (price-process-verify.mjs).
     */
    private static function mul32(int $a, int $b): int
    {
        $aLo = $a & 0xFFFF;
        $aHi = ($a >> 16) & 0xFFFF;
        $bLo = $b & 0xFFFF;
        $bHi = ($b >> 16) & 0xFFFF;
        $cross = ($aLo * $bHi + $aHi * $bLo) & 0xFFFF;
        return (($aLo * $bLo) + ($cross << 16)) & 0xFFFFFFFF;
    }

    private static function pxHashU32(string $key): int
    {
        $h = (int) hexdec(self::crc32bHex($key));
        $h = self::mul32($h ^ ($h >> 16), 0x7FEB352D);
        $h = self::mul32($h ^ ($h >> 15), 0x846CA68B);
        $h = ($h ^ ($h >> 16)) & 0xFFFFFFFF;
        return $h;
    }

    /**
     * Public accessor for pxHashU32(), for the `engine:verify-hash`
     * deploy-gate command only. The price path itself uses the
     * private method; this wrapper exists so the gate can print
     * sample outputs from the engine's own code.
     */
    public static function pxHashU32For(string $key): int
    {
        return self::pxHashU32($key);
    }

    private static function pxUniform01(string $key): float
    {
        return self::pxHashU32($key) / 4294967296.0;
    }

    /**
     * Approx standard normal from a single hash: the sum of its two
     * 16-bit halves is triangular with unit variance (range ±2.45);
     * across the ~31 tent levels the sum is close to normal.
     */
    private static function pxNormal(string $key): float
    {
        $h = self::pxHashU32($key);
        $u1 = ($h & 0xFFFF) / 65536.0;
        $u2 = (($h >> 16) & 0xFFFF) / 65536.0;
        return ($u1 + $u2 - 1.0) * 2.449489742783178;
    }

    private static function organicHeight(int $n): float
    {
        if (self::$organicHeights === null) {
            self::$organicHeights = [];
            for ($i = 0; $i <= self::ORGANIC_MAX_LEVEL; $i++) {
                self::$organicHeights[$i] = pow(2, -$i / 2 - 1);
            }
        }
        return self::$organicHeights[$n];
    }

    /**
     * Typical one-day log-volatility for a symbol (fraction of price).
     * Symbol-based so the clustering lookbacks (which only know the
     * symbol) calibrate identically to the price itself.
     */
    private static function organicDailyVol(string $symbol): float
    {
        $clean = strtoupper(str_replace(['(OTC)', 'OTC', ' ', '/', '-', '_', '.'], '', $symbol));

        // Crypto
        if (str_contains($clean, 'BTC') || str_contains($clean, 'ETH') || str_contains($clean, 'SOL') || str_contains($clean, 'BNB') || str_contains($clean, 'XRP') || str_contains($clean, 'DOGE') || str_contains($clean, 'LTC') || str_contains($clean, 'AZA')) {
            return 0.035;
        }
        // Commodities
        if (str_contains($clean, 'XAG') || str_contains($clean, 'SILVER')) return 0.016;
        if (str_contains($clean, 'OIL') || str_contains($clean, 'BRENT') || str_contains($clean, 'CRUDE')) return 0.024;
        if (str_contains($clean, 'XAU') || str_contains($clean, 'GOLD')) return 0.010;
        // Stocks
        if ($clean === 'AAPL' || $clean === 'BA' || $clean === 'MSFT' || $clean === 'TSLA') return 0.016;
        // Indices
        if (str_contains($clean, 'SP500') || str_contains($clean, 'SPX') || str_contains($clean, 'NASDAQ') || str_contains($clean, 'NDX') || str_contains($clean, 'DOW') || str_contains($clean, 'DJI')) return 0.011;
        // Exotic forex (higher vol)
        foreach (['TRY', 'ZAR', 'BDT', 'NGN', 'EGP', 'PKR', 'IDR', 'BRL', 'MXN', 'PHP', 'INR'] as $ccy) {
            if (str_contains($clean, $ccy)) return 0.011;
        }
        return 0.0055; // major forex default
    }

    /**
     * Scale factor for the unit walk: the unit walk's sd over one
     * day is sqrt(86400 / SPAN), so this maps it onto the asset's
     * typical daily log-volatility, corrected for the average
     * sigma_local inflation (see ORGANIC_SCALE_CAL).
     */
    private static function organicScale(string $symbol): float
    {
        return self::ORGANIC_SCALE_CAL * self::organicDailyVol($symbol) / sqrt(86400.0 / self::ORGANIC_SPAN_SECONDS);
    }

    /**
     * Regime parameters for one 5-day regime, derived only from the
     * symbol and the regime index. Cached per request.
     *
     * calm (35%):     vol 0.50–0.65x, no drift bias
     * trending (37%): vol 0.90–1.20x, drift bias ±(0.9–1.6)
     * volatile (28%): vol 1.80–2.60x, drift bias ±(0.2–0.5)
     */
    private static function organicRegime(string $sym, int $index): array
    {
        $key = $sym . '|' . $index;
        if (isset(self::$organicRegimeCache[$key])) {
            return self::$organicRegimeCache[$key];
        }
        $uType = self::pxUniform01("px2:regime:{$sym}:{$index}:type");
        $uVol  = self::pxUniform01("px2:regime:{$sym}:{$index}:vol");
        $uDir  = self::pxUniform01("px2:regime:{$sym}:{$index}:dir");
        $uBias = self::pxUniform01("px2:regime:{$sym}:{$index}:bias");
        if ($uType < 0.35) {
            $params = ['type' => 'calm', 'vol' => 0.50 + 0.15 * $uVol, 'bias' => 0.0];
        } elseif ($uType < 0.72) {
            $dir = $uDir < 0.5 ? -1.0 : 1.0;
            $params = ['type' => 'trending', 'vol' => 0.90 + 0.30 * $uVol, 'bias' => $dir * (0.9 + 0.7 * $uBias)];
        } else {
            $dir = $uDir < 0.5 ? -1.0 : 1.0;
            $params = ['type' => 'volatile', 'vol' => 1.8 + 0.8 * $uVol, 'bias' => $dir * (0.2 + 0.3 * $uBias)];
        }
        return self::$organicRegimeCache[$key] = $params;
    }

    private static function organicRegimeAt(string $sym, float $t): array
    {
        $index = (int) floor(($t - self::ORGANIC_EPOCH) / self::ORGANIC_REGIME_SECONDS);
        if ($index < 0) $index = 0;
        return self::organicRegime($sym, $index);
    }

    /**
     * Contribution of level n's tent at normalised time $x (fraction
     * of the span). The tent's coefficient is fixed by its centre
     * time, so the path is continuous even though the volatility
     * factor varies: a tent never changes amplitude mid-flight.
     *
     * $withSigma=false gives the unmodulated "core" walk used for the
     * realised-volatility lookbacks (no recursion: the core never
     * calls organicSigmaLocal).
     */
    private static function organicTentValue(string $sym, float $x, int $n, bool $withSigma): float
    {
        $pow = 1 << $n;
        $pos = $x * $pow;
        $k = (int) floor($pos);
        if ($k >= $pow) $k = $pow - 1;
        $u = $pos - $k;
        $tri = $u < 0.5 ? 2.0 * $u : 2.0 * (1.0 - $u);
        if ($tri <= 0.0) return 0.0;

        $coef = self::pxNormal("px2:w:{$sym}:{$n}:{$k}");
        $center = self::ORGANIC_EPOCH + ($k + 0.5) * (self::ORGANIC_SPAN_SECONDS / $pow);
        $h = self::organicHeight($n);
        if ($n <= self::ORGANIC_LONG_DAMP_MAX_LEVEL) $h *= self::ORGANIC_LONG_DAMP_FACTOR;
        if ($n >= self::ORGANIC_BIAS_MIN_LEVEL && $n <= self::ORGANIC_BIAS_MAX_LEVEL) {
            $coef += self::organicRegimeAt($sym, $center)['bias'];
        }
        if ($withSigma) {
            if ($n >= self::ORGANIC_SIGMA_MIN_LEVEL) {
                $coef *= self::organicSigmaLocal($sym, (int) floor($center / 60));
            }
            if ($n >= self::ORGANIC_TICK_MIN_LEVEL) {
                $coef *= self::ORGANIC_TICK_BOOST;
            }
        }
        return $coef * $h * $tri;
    }

    /**
     * Unmodulated core walk (levels 8..30): the "market memory" the
     * volatility-clustering lookbacks are measured on. Cached per
     * request by integer timestamp.
     */
    private static function organicCoreWalk(string $sym, float $t): float
    {
        $it = (int) $t;
        $ck = $sym . '|' . $it;
        if (isset(self::$organicCoreCache[$ck])) {
            return self::$organicCoreCache[$ck];
        }
        $x = ($t - self::ORGANIC_EPOCH) / self::ORGANIC_SPAN_SECONDS;
        if ($x < 0.0) $x = 0.0;
        if ($x >= 1.0) $x = 1.0 - 1e-12;
        $sum = 0.0;
        for ($n = self::ORGANIC_CORE_MIN_LEVEL; $n <= self::ORGANIC_MAX_LEVEL; $n++) {
            $sum += self::organicTentValue($sym, $x, $n, false);
        }
        return self::$organicCoreCache[$ck] = $sum;
    }

    /**
     * Local volatility factor for one minute bucket: regime
     * multiplier x clustering multiplier. The clustering multiplier
     * is driven by realised movement of the core walk over 1 h /
     * 6 h / 1 d lookbacks (weighted 45/35/20), normalised by the
     * expected |move| for a unit walk over that lag. Cached per
     * request by (symbol, minute bucket); the value is a pure
     * function of (symbol, bucket), so the cache is exact.
     */
    private static function organicSigmaLocal(string $sym, int $minuteBucket): float
    {
        $key = $sym . '|' . $minuteBucket;
        if (isset(self::$organicSigmaCache[$key])) {
            return self::$organicSigmaCache[$key];
        }
        $tau = $minuteBucket * 60 + 30; // bucket centre
        $regimeVol = self::organicRegimeAt($sym, (float) $tau)['vol'];

        $lags = [3600, 21600, 86400];
        $weights = [0.45, 0.35, 0.20];
        $shock = 0.0;
        $coreNow = self::organicCoreWalk($sym, (float) $tau);
        foreach ($lags as $i => $lag) {
            $sd = sqrt($lag / self::ORGANIC_SPAN_SECONDS);
            $d = abs($coreNow - self::organicCoreWalk($sym, (float) ($tau - $lag)));
            $shock += $weights[$i] * ($d / (0.7978845608028654 * $sd));
        }
        $cluster = min(2.5, max(0.4, 0.6 + 0.4 * $shock));
        $value = $regimeVol * $cluster;
        return self::$organicSigmaCache[$key] = $value;
    }

    /**
     * Full unit walk: all tent levels 0..30, sigma_local on levels
     * >= 14, tick boost on levels >= 24, regime drift bias on
     * levels 10..12.
     */
    private static function organicWalk(string $sym, float $t): float
    {
        $x = ($t - self::ORGANIC_EPOCH) / self::ORGANIC_SPAN_SECONDS;
        if ($x < 0.0) $x = 0.0;
        if ($x >= 1.0) $x = 1.0 - 1e-12;
        $sum = 0.0;
        for ($n = 0; $n <= self::ORGANIC_MAX_LEVEL; $n++) {
            $sum += self::organicTentValue($sym, $x, $n, true);
        }
        return $sum;
    }

    /**
     * Rare hash-triggered news jumps, in log-price units: one jump
     * per day with probability 0.019 (~7/year). Amplitude is
     * 0.4–1.2x the asset's daily vol, direction is hashed, the move
     * ramps over 45 s (smoothstep, so the path stays continuous) and
     * then partially mean-reverts (35–80% over ~25 min), leaving a
     * permanent step of the remainder.
     */
    private static function organicJump(string $sym, float $t): float
    {
        $day0 = (int) floor(($t - 7200.0) / 86400.0);
        $day1 = (int) floor($t / 86400.0);
        $total = 0.0;
        for ($d = $day0; $d <= $day1; $d++) {
            if (self::pxUniform01("px2:jump:{$sym}:{$d}:p") >= self::ORGANIC_JUMP_PROB_PER_DAY) continue;
            $uT = self::pxUniform01("px2:jump:{$sym}:{$d}:t");
            $uA = self::pxUniform01("px2:jump:{$sym}:{$d}:a");
            $uD = self::pxUniform01("px2:jump:{$sym}:{$d}:d");
            $uR = self::pxUniform01("px2:jump:{$sym}:{$d}:r");
            $tau = $d * 86400.0 + 3600.0 + 77400.0 * $uT; // 01:00–22:30 UTC
            $dt = $t - $tau;
            if ($dt < 0.0) continue;
            $dir = $uD < 0.5 ? -1.0 : 1.0;
            $amp = (0.4 + 0.8 * $uA) * self::organicDailyVol($sym);
            $rho = 0.35 + 0.45 * $uR;
            if ($dt < self::ORGANIC_JUMP_RAMP_SECONDS) {
                $s = $dt / self::ORGANIC_JUMP_RAMP_SECONDS;
                $total += $dir * $amp * $s * $s * (3.0 - 2.0 * $s);
            } else {
                $total += $dir * $amp * ((1.0 - $rho) + $rho * exp(-($dt - self::ORGANIC_JUMP_RAMP_SECONDS) / self::ORGANIC_JUMP_REVERT_TAU));
            }
        }
        return $total;
    }

    /**
     * Calculate continuous deterministic price for any asset at any timestamp.
     * 100% synchronized across all clients, tabs, and server verification.
     *
     * Log-price model: base price anchored at the organic epoch, plus
     * the scaled multi-scale random walk and the jump term. The path
     * is continuous in time; the live tick and the forming candle
     * close both read getDeterministicPrice(now), so they agree.
     * The level is bounded by construction: the multi-year tents
     * are damped to 0.05 (mean reversion to base over ~year
     * horizons) and the total log-return is hard-capped by a smooth
     * tanh at 30x the asset's daily vol, so no asset can drift by
     * orders of magnitude however large its scale is.
     * Steering (calculateControlledPrice / applyLastSecondsSteering)
     * is layered on top of this and is untouched.
     */
    private static function calculateOrganicPrice(Asset $asset, float $timestamp, ?float $basePrice = null): float
    {
        $basePrice = $basePrice ?: self::getBasePrice($asset);
        $precision = self::getPrecision($basePrice, $asset);
        $pip = pow(10, -$precision);
        $sym = strtoupper($asset->symbol);

        $logReturn = self::organicScale($sym) * self::organicWalk($sym, $timestamp)
            + self::organicJump($sym, $timestamp);
        $cap = self::ORGANIC_LOGRET_CAP_MULT * self::organicDailyVol($sym);
        $logReturn = $cap * tanh($logReturn / $cap);
        $price = $basePrice * exp($logReturn);

        return round(max($pip, $price), $precision);
    }

    /**
     * True high/low of the deterministic price path over [start, end].
     * Team B hook for generateCandles(): sample the actual path
     * instead of the fabricated wick offsets. Samples $steps+1
     * instants including both endpoints, so the extremes are true
     * extrema of the sampled path (not invented wicks). For a
     * 1-minute candle 24 steps (~2.5 s spacing) is plenty; use
     * fewer steps for larger timeframes.
     */
    public static function samplePathExtrema(Asset $asset, float $start, float $end, int $steps = 24): array
    {
        $steps = max(1, $steps);
        $high = null;
        $low = null;
        $span = $end - $start;
        for ($i = 0; $i <= $steps; $i++) {
            $p = self::getDeterministicPrice($asset, $start + $span * $i / $steps);
            if ($high === null || $p > $high) $high = $p;
            if ($low === null || $p < $low) $low = $p;
        }
        return ['high' => $high, 'low' => $low];
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
     *
     * TEAM B — 3-year chart history (2026-11-14). Depth is achieved by
     * PAGING, not storage: there is still no candles table. Prices are a
     * pure deterministic function of (symbol, timestamp), so any window
     * ending up to 3+ years in the past can be generated on demand.
     *
     * PAGING CONTRACT (for Team C / the frontend scroll-back):
     *  - $timeframe accepts fixed seconds (int, e.g. 60) OR a key string:
     *    '3s','5s','10s','15s','30s','1m','2m','3m','5m','10m','15m','30m',
     *    '1h','4h','1d','1w' (Mon 00:00 UTC weeks) and '1M' (calendar
     *    months). Int 604800 also means '1w'. A month has no fixed
     *    second count, so it MUST be requested as the string '1M'.
     *  - $count is clamped to a bounded page size (see maxPageSizeFor()):
     *    1000 candles for sub-hourly timeframes, 2000 for 1h and above,
     *    300 weeks, 120 months. Defaults cover ~5y on daily/weekly/
     *    monthly; on small timeframes depth comes from paging.
     *  - $endTime is an EXCLUSIVE upper bound: every returned candle
     *    ends at or before $endTime, and the last candle is the one
     *    ending exactly at the bucket boundary <= $endTime. To fetch
     *    the next older page, pass endTime = first candle's `time` of
     *    the current page. Two adjacent pages concatenate with no gap
     *    and no overlap: older page's last close == newer page's
     *    first open, and times are consecutive buckets.
     *  - $endTime === null means "live": the last candle is the
     *    forming candle, its close is getDeterministicPrice(now)
     *    (the live tick), and its high/low only sample up to now.
     *    With $endTime given, every candle is completed
     *    (close = price at its bucket end) and fully reproducible.
     *
     * ALIGNMENT: fixed-second timeframes align on the absolute epoch
     * grid (floor(t / tf) * tf). Weekly buckets start Monday 00:00
     * UTC; monthly buckets start on the 1st at 00:00 UTC (month length
     * handled by calendar math, incl. February and leap years). All
     * buckets tile exactly: no gaps, no overlaps.
     *
     * CONTINUITY: open is always the deterministic price at the bucket
     * start, close the price at the bucket end, so open[N] == close[N-1]
     * exactly (same function, same timestamp). The forming candle's
     * open == previous close for the same reason.
     *
     * HIGH/LOW are true extrema of the sampled price path: max/min over
     * 13 evenly spaced samples of getDeterministicPrice() inside the
     * bucket (fractions k/12, k = 0..12, endpoints included); 3-second
     * candles use 5 samples (0.75 s spacing — still finer than the
     * fastest price structure). There are NO independently fabricated
     * wicks (the old deterministicNoise extraUpper/extraLower
     * extensions are gone).
     *
     * CROSS-TIMEFRAME CONSISTENCY (exact by construction):
     *  - 1m candles aggregate their 20 constituent 3s candles, so a 1m
     *    candle's OHLC == the aggregation of its 3s candles.
     *  - 1w / 1M candles aggregate their constituent daily candles, so
     *    a monthly candle's OHLC == the aggregation of its daily
     *    candles (same for weekly).
     *  - Other pairs share the same underlying price function and the
     *    same sampling rule, so they agree closely; they are not
     *    bit-exact aggregates (a parent samples its own 13 points
     *    while its children sample theirs more densely). See the
     *    Team B Node verification for measured discrepancies.
     */
    public static function generateCandles(Asset $asset, ?int $count = null, int|string $timeframeSec = 60, ?int $endTime = null): array
    {
        self::checkAndResetExpiredControl($asset);

        $tf = self::resolveTimeframe($timeframeSec);

        // A future endTime is just "live" (never return candles that
        // end after now, and never let the forming candle leak ahead).
        if ($endTime !== null && $endTime > time()) {
            $endTime = null;
        }
        $live = $endTime === null;
        $now = $live ? time() : (int) $endTime;

        if ($count === null || $count <= 0) {
            $count = self::defaultCandleCount($tf);
        }
        $count = min(max(1, $count), self::maxPageSizeFor($tf));

        $basePrice = self::getBasePrice($asset);
        $precision = self::getPrecision($basePrice, $asset);

        // Newest bucket start of the window, then walk back $count buckets.
        $anchorStart = $live
            ? self::candleStartForTimestamp($now, $tf)
            : self::prevCandleStart(self::candleStartForTimestamp($now, $tf), $tf);
        $starts = self::bucketStartsEndingAt($anchorStart, $count, $tf);
        $pageStart = $starts[0];
        $pageEnd = self::nextCandleStart($starts[count($starts) - 1], $tf);

        // Aggregation children: 1m <- 3s, 1w/1M <- 1d. Everything else
        // is sampled directly (13 points per candle).
        $childSeconds = self::aggregationChildSeconds($tf);

        if ($childSeconds !== null) {
            $children = [];
            // 3-second children are sampled at 5 points (0.75 s spacing):
            // that still resolves the fastest price structure (2 s tents,
            // 45 s jump ramps) and keeps a 1-minute candle at 100 price
            // evaluations instead of 260 — the initial 1m page load
            // (1000 candles) stays affordable. 1m-vs-3s aggregation
            // remains exact because the children use these same points.
            $childPoints = $childSeconds === 3 ? 5 : self::CANDLE_SAMPLE_POINTS;
            for ($t = $pageStart; $t < $pageEnd; $t += $childSeconds) {
                if ($live && $t > $now) {
                    break; // child buckets starting after now do not exist yet
                }
                $childEnd = $t + $childSeconds;
                $clamp = ($live && $childEnd > $now) ? $now : null;
                $children[] = self::buildDirectCandle($asset, $t, $childEnd, $precision, $clamp, $childPoints);
            }

            return self::aggregateChildrenIntoBuckets($children, $starts, $tf, $asset, $precision, $live ? $now : null);
        }

        $candles = [];
        $points = $tf['kind'] === 'fixed' && (int) $tf['seconds'] === 3 ? 5 : self::CANDLE_SAMPLE_POINTS;
        foreach ($starts as $idx => $start) {
            $end = self::nextCandleStart($start, $tf);
            $isForming = $live && $idx === count($starts) - 1;
            $clamp = $isForming ? $now : null;
            $candles[] = self::buildDirectCandle($asset, $start, $end, $precision, $clamp, $points);
        }

        return $candles;
    }

    /**
     * Minimum guaranteed chart-history depth. The engine has no lower
     * bound in code (the price function is defined for any timestamp);
     * 3 years is the depth Team B verifies (see candles:verify-depth).
     */
    public const HISTORY_YEARS = 3;

    /**
     * Bounded page sizes for generateCandles(): history is paged by
     * endTime, never materialised in one call.
     */
    public const MAX_CANDLES_SUBHOURLY = 1000;
    public const MAX_CANDLES_HOURLY_AND_ABOVE = 2000;
    public const MAX_CANDLES_WEEKLY = 300;
    public const MAX_CANDLES_MONTHLY = 120;

    /**
     * Interior samples per directly-built candle (fractions k/12,
     * k = 0..12, endpoints included). High/low are the max/min over
     * exactly these sampled prices — true path extrema, no wicks.
     */
    public const CANDLE_SAMPLE_POINTS = 13;

    /**
     * All timeframes the UI offers, keyed by their label. '1w' is a
     * calendar week (Monday 00:00 UTC); '1M' is a calendar month and
     * therefore has no fixed second count (null). The int values are
     * also accepted as $timeframeSec (604800 == '1w').
     */
    public static function supportedTimeframes(): array
    {
        return [
            '3s' => 3, '5s' => 5, '10s' => 10, '15s' => 15, '30s' => 30,
            '1m' => 60, '2m' => 120, '3m' => 180, '5m' => 300, '10m' => 600,
            '15m' => 900, '30m' => 1800, '1h' => 3600, '4h' => 14400,
            '1d' => 86400, '1w' => 604800, '1M' => null,
        ];
    }

    /**
     * Normalise a timeframe (int seconds or key string) to
     * ['key' => string, 'kind' => 'fixed'|'week'|'month', 'seconds' => ?int].
     * Unknown values fall back to 1m, matching the old clamping
     * behaviour of the price-data endpoint instead of throwing.
     */
    public static function resolveTimeframe(int|string $timeframe): array
    {
        if (is_string($timeframe)) {
            $raw = trim($timeframe);
            if ($raw === '1M') {
                return ['key' => '1M', 'kind' => 'month', 'seconds' => null];
            }
            $lower = strtolower($raw);
            $map = self::supportedTimeframes();
            if (isset($map[$lower])) {
                $seconds = $map[$lower];
                return $lower === '1w'
                    ? ['key' => '1w', 'kind' => 'week', 'seconds' => 604800]
                    : ['key' => $lower, 'kind' => 'fixed', 'seconds' => $seconds];
            }
            if (is_numeric($raw)) {
                return self::resolveTimeframe((int) $raw);
            }
            return ['key' => '1m', 'kind' => 'fixed', 'seconds' => 60];
        }

        $seconds = (int) $timeframe;
        if ($seconds === 604800) {
            return ['key' => '1w', 'kind' => 'week', 'seconds' => 604800];
        }
        if ($seconds >= 3 && $seconds <= 86400) {
            $key = (string) $seconds . 's';
            foreach (self::supportedTimeframes() as $label => $value) {
                if ($value === $seconds) {
                    $key = $label;
                    break;
                }
            }
            return ['key' => $key, 'kind' => 'fixed', 'seconds' => $seconds];
        }

        return ['key' => '1m', 'kind' => 'fixed', 'seconds' => 60];
    }

    /**
     * Start (epoch seconds, UTC) of the candle containing $timestamp.
     * Fixed timeframes use the absolute epoch grid; weeks start on
     * Monday 00:00 UTC; months on the 1st at 00:00 UTC.
     */
    public static function candleStartForTimestamp(int $timestamp, int|string|array $timeframe): int
    {
        $tf = is_array($timeframe) ? $timeframe : self::resolveTimeframe($timeframe);

        if ($tf['kind'] === 'month') {
            return (int) gmmktime(0, 0, 0, (int) gmdate('n', $timestamp), 1, (int) gmdate('Y', $timestamp));
        }

        if ($tf['kind'] === 'week') {
            $dayStart = (int) floor($timestamp / 86400) * 86400;
            $dow = (int) gmdate('N', $timestamp); // 1 = Monday .. 7 = Sunday
            return $dayStart - ($dow - 1) * 86400;
        }

        $seconds = (int) $tf['seconds'];
        return (int) floor($timestamp / $seconds) * $seconds;
    }

    /**
     * Start of the candle immediately after the one starting at $start.
     */
    public static function nextCandleStart(int $start, int|string|array $timeframe): int
    {
        $tf = is_array($timeframe) ? $timeframe : self::resolveTimeframe($timeframe);

        if ($tf['kind'] === 'month') {
            $year = (int) gmdate('Y', $start);
            $month = (int) gmdate('n', $start);
            return $month === 12
                ? (int) gmmktime(0, 0, 0, 1, 1, $year + 1)
                : (int) gmmktime(0, 0, 0, $month + 1, 1, $year);
        }

        if ($tf['kind'] === 'week') {
            return $start + 604800;
        }

        return $start + (int) $tf['seconds'];
    }

    /**
     * Start of the candle immediately before the one starting at $start.
     * Implemented via candleStartForTimestamp($start - 1) so calendar
     * buckets (short February, leap years) stay exact.
     */
    public static function prevCandleStart(int $start, int|string|array $timeframe): int
    {
        $tf = is_array($timeframe) ? $timeframe : self::resolveTimeframe($timeframe);
        return self::candleStartForTimestamp($start - 1, $tf);
    }

    /**
     * The $count bucket starts ending with $anchorStart (the newest
     * bucket), ascending, oldest first.
     */
    private static function bucketStartsEndingAt(int $anchorStart, int $count, array $tf): array
    {
        $starts = [$anchorStart];
        $cursor = $anchorStart;
        for ($i = 1; $i < $count; $i++) {
            $cursor = self::prevCandleStart($cursor, $tf);
            $starts[] = $cursor;
        }
        return array_reverse($starts);
    }

    /**
     * Default page size when the caller passes no count. Daily/weekly/
     * monthly defaults already cover ~5 years; small timeframes rely
     * on endTime paging for depth (see the generateCandles contract).
     */
    private static function defaultCandleCount(array $tf): int
    {
        if ($tf['kind'] === 'month') {
            return 60; // 5 years of monthly candles
        }
        if ($tf['kind'] === 'week') {
            return 260; // 5 years of weekly candles
        }

        $seconds = (int) $tf['seconds'];
        if ($seconds >= 86400) {
            return 1825; // 5 full years of daily candles
        }
        if ($seconds >= 14400) {
            return 2000; // ~11 months of 4-hour candles
        }
        if ($seconds >= 3600) {
            return 1500; // ~2 months of 1-hour candles
        }
        if ($seconds >= 60) {
            return 1000; // 1m–30m candles
        }
        return 800; // 800 micro candles (3s–30s)
    }

    /**
     * Hard per-call page cap for one timeframe (explicit counts are
     * clamped to this; see generateCandles()). Public so callers
     * (price-data endpoint, candles:verify-depth) can size pages
     * without duplicating the table.
     */
    public static function maxPageSizeFor(int|string|array $timeframe): int
    {
        $tf = is_array($timeframe) ? $timeframe : self::resolveTimeframe($timeframe);
        if ($tf['kind'] === 'month') {
            return self::MAX_CANDLES_MONTHLY;
        }
        if ($tf['kind'] === 'week') {
            return self::MAX_CANDLES_WEEKLY;
        }
        if ((int) $tf['seconds'] >= 3600) {
            return self::MAX_CANDLES_HOURLY_AND_ABOVE;
        }
        return self::MAX_CANDLES_SUBHOURLY;
    }

    /**
     * Child timeframe (in seconds) whose candles a parent aggregates,
     * or null when the parent is sampled directly. 1m aggregates 3s
     * candles; 1w and 1M aggregate daily candles. These are the pairs
     * whose OHLC aggregation is exact by construction.
     */
    private static function aggregationChildSeconds(array $tf): ?int
    {
        if ($tf['kind'] === 'fixed' && (int) $tf['seconds'] === 60) {
            return 3;
        }
        if ($tf['kind'] === 'week' || $tf['kind'] === 'month') {
            return 86400;
        }
        return null;
    }

    /**
     * One directly-sampled candle: open = price at bucket start,
     * close = price at bucket end (or at $clampBefore for the forming
     * candle — never sample the future), high/low = max/min over
     * $points evenly spaced price samples inside the bucket,
     * endpoints included. No fabricated wicks. $points defaults to
     * CANDLE_SAMPLE_POINTS (13); 3-second candles use 5 (see
     * generateCandles(): 0.75 s spacing is still finer than the
     * fastest price structure, and it keeps 1m aggregation cheap).
     */
    private static function buildDirectCandle(Asset $asset, int $start, int $end, int $precision, ?int $clampBefore = null, int $points = self::CANDLE_SAMPLE_POINTS): array
    {
        $open = self::getDeterministicPrice($asset, (float) $start);
        $closeTime = $clampBefore !== null ? min($end, $clampBefore) : $end;
        $close = self::getDeterministicPrice($asset, (float) $closeTime);

        $high = max($open, $close);
        $low = min($open, $close);

        $span = $end - $start;
        for ($k = 1; $k < $points - 1; $k++) {
            $t = $start + $span * $k / ($points - 1);
            if ($clampBefore !== null && $t > $clampBefore) {
                $t = (float) $clampBefore;
            }
            $price = self::getDeterministicPrice($asset, (float) $t);
            if ($price > $high) {
                $high = $price;
            }
            if ($price < $low) {
                $low = $price;
            }
        }

        return [
            'time' => $start,
            'open' => round($open, $precision),
            'high' => round($high, $precision),
            'low' => round($low, $precision),
            'close' => round($close, $precision),
        ];
    }

    /**
     * Aggregate pre-built child candles into their parent buckets.
     * OHLC of a parent == aggregation of its children by definition:
     * open = first child's open, close = last child's close,
     * high = max of children's highs, low = min of children's lows.
     * Children must be ascending by time; a single pointer walk keeps
     * this linear even for a 60-month page (1800 daily children).
     * A bucket with no children (only possible if the page window was
     * cut short) falls back to a directly-sampled candle.
     */
    private static function aggregateChildrenIntoBuckets(array $children, array $bucketStarts, array $tf, Asset $asset, int $precision, ?int $clampBefore = null): array
    {
        $out = [];
        $childCount = count($children);
        $childIdx = 0;
        $bucketCount = count($bucketStarts);

        foreach ($bucketStarts as $b => $bucketStart) {
            $bucketEnd = $b + 1 < $bucketCount
                ? $bucketStarts[$b + 1]
                : self::nextCandleStart($bucketStart, $tf);

            $group = [];
            while ($childIdx < $childCount && $children[$childIdx]['time'] < $bucketStart) {
                $childIdx++; // defensive: children before this bucket (never expected)
            }
            while ($childIdx < $childCount && $children[$childIdx]['time'] < $bucketEnd) {
                $group[] = $children[$childIdx];
                $childIdx++;
            }

            if ($group === []) {
                $isForming = $clampBefore !== null && $b === $bucketCount - 1;
                $out[] = self::buildDirectCandle($asset, $bucketStart, $bucketEnd, $precision, $isForming ? $clampBefore : null);
                continue;
            }

            $out[] = self::aggregateCandleGroup($group);
        }

        return $out;
    }

    /**
     * OHLC aggregation of a non-empty list of ascending child candles.
     */
    private static function aggregateCandleGroup(array $children): array
    {
        $first = $children[0];
        $last = $children[count($children) - 1];
        $high = $first['high'];
        $low = $first['low'];

        foreach ($children as $child) {
            if ($child['high'] > $high) {
                $high = $child['high'];
            }
            if ($child['low'] < $low) {
                $low = $child['low'];
            }
        }

        return [
            'time' => $first['time'],
            'open' => $first['open'],
            'high' => $high,
            'low' => $low,
            'close' => $last['close'],
        ];
    }

    /**
     * Earliest timestamp (epoch seconds) that still counts as "full
     * depth" for this engine: now minus HISTORY_YEARS years. Any
     * window ending at or after this is guaranteed generatable.
     */
    public static function historyDepthCutoff(?int $now = null, int $years = self::HISTORY_YEARS): int
    {
        $now = $now ?? time();
        $year = (int) gmdate('Y', $now) - $years;
        return (int) gmmktime(
            (int) gmdate('H', $now),
            (int) gmdate('i', $now),
            (int) gmdate('s', $now),
            (int) gmdate('n', $now),
            (int) gmdate('j', $now),
            $year
        );
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
     * Matches the assets migration default (90).
     */
    public const DEFAULT_PAYOUT_PERCENT = 90;

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
     * from the demo 8/2 cycle for demo trades (live 4/6 cycle for
     * live trades) with no other force. Cycle positions are peeked
     * in expiry order, counting settled trades plus earlier-pending
     * trades for the same user, so the peek matches what settle()
     * will decide later.
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

            $liveUserIds = $trades
                ->filter(fn (Trade $t) => $t->account_type === 'live' && self::forcedSideForTrade($t, $asset, $globalMode) === null)
                ->pluck('user_id')->unique()->values()->all();
            $settledLiveCounts = [];
            if ($liveUserIds !== []) {
                $settledLiveCounts = Trade::whereIn('user_id', $liveUserIds)
                    ->where('account_type', 'live')
                    ->whereIn('result', ['win', 'loss'])
                    ->selectRaw('user_id, COUNT(*) as cnt')
                    ->groupBy('user_id')
                    ->pluck('cnt', 'user_id')->all();
            }
            $liveOffsets = [];

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

                if ($trade->account_type === 'live') {
                    $userId = (int) $trade->user_id;
                    $effectiveCount = (int) ($settledLiveCounts[$userId] ?? 0) + (int) ($liveOffsets[$userId] ?? 0);
                    $liveOffsets[$userId] = (int) ($liveOffsets[$userId] ?? 0) + 1;

                    if ($forcedSide === null) {
                        $forcedSide = LiveCycleService::forcedSideForTrade($trade, $effectiveCount);
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
