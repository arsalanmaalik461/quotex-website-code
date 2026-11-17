<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Services\OtcPriceEngine;
use Illuminate\Console\Command;

/**
 * Team B — verify that every pair has at least 3 years of chart
 * history on every timeframe, with no candles table and no seeding.
 *
 * Depth is proven by PROBING the oldest window directly, not by
 * scanning the whole span: prices are a pure deterministic function
 * of (symbol, timestamp), so a correct page ending 3 years ago
 * proves every window in between is generatable the same way.
 * Scanning 3 years of 3-second candles (~10.5M rows) is never
 * needed — and is exactly what the paging contract exists to avoid.
 *
 * Usage:
 *   php artisan candles:verify-depth                 # first active asset
 *   php artisan candles:verify-depth "EUR/USD (OTC)"
 *   php artisan candles:verify-depth --years=3 --page=200
 *   php artisan candles:verify-depth --fresh         # + brand-new symbol
 *
 * Checks per timeframe (3s, 5s, 10s, 15s, 30s, 1m, 2m, 3m, 5m, 10m,
 * 15m, 30m, 1h, 4h, 1d, 1w, 1M):
 *  - live page: ascending times, correct bucket alignment,
 *    open[N] == close[N-1], high/low bracket the body, forming
 *    candle close == live tick price.
 *  - oldest page (window ending just after the 3-year cutoff):
 *    same checks on completed candles, and the window really
 *    reaches the cutoff.
 *  - concatenation: the page ending at the oldest page's first
 *    candle time joins it with no gap and no overlap
 *    (last close == first open, buckets adjacent).
 * Plus one-off aggregation checks: 1m vs its 3s children and
 * monthly/weekly vs their daily children, over a window 3 years ago.
 */
class VerifyCandleDepth extends Command
{
    protected $signature = 'candles:verify-depth {symbol? : Asset symbol to check (default: first active asset)} {--years=3 : Years of history required} {--page=200 : Candles per probe page} {--fresh : Also verify a brand-new, never-seen symbol (no DB row is created)}';

    protected $description = 'Verify 3-year chart-history depth, paging and cross-timeframe aggregation for a pair (no candles are stored).';

    private int $failures = 0;

    public function handle(): int
    {
        $years = max(1, (int) $this->option('years'));
        $page = max(10, min(1000, (int) $this->option('page')));
        $now = time();
        $cutoff = OtcPriceEngine::historyDepthCutoff($now, $years);

        $asset = $this->resolveAsset($this->argument('symbol'));
        if (!$asset) {
            $this->error('No asset found (and none could be constructed).');
            return self::FAILURE;
        }

        $this->info("Asset: {$asset->symbol} (persisted: " . ($asset->exists ? 'yes' : 'no — transient, proof needs no seeding') . ')');
        $this->info('Cutoff (now - ' . $years . 'y): ' . gmdate('Y-m-d H:i:s', $cutoff) . ' UTC — oldest window is probed directly; the full span is never scanned.');
        $this->newLine();

        foreach (OtcPriceEngine::supportedTimeframes() as $key => $seconds) {
            $this->checkTimeframe($asset, $key, $page, $now, $cutoff);
        }

        $this->checkAggregation($asset, $cutoff);

        if ($this->option('fresh')) {
            $this->checkFreshAsset($cutoff);
        }

        $this->newLine();
        if ($this->failures === 0) {
            $this->info("ALL CHECKS PASSED for {$asset->symbol}: {$years}-year depth on every timeframe.");
            return self::SUCCESS;
        }

        $this->error("FAILED: {$this->failures} check(s) failed for {$asset->symbol}.");
        return self::FAILURE;
    }

    private function resolveAsset(?string $symbol): ?Asset
    {
        if ($symbol) {
            $asset = Asset::where('symbol', $symbol)->first()
                ?? Asset::where('code', $symbol)->first()
                ?? Asset::where('symbol', 'like', "%{$symbol}%")->first();
            if ($asset) {
                return $asset;
            }

            // Fresh symbol, never seen: transient (not persisted) is
            // enough — the price seed comes from the symbol string.
            $asset = new Asset();
            $asset->symbol = $symbol;
            $asset->type = 'forex';
            $asset->control_mode = 'auto';
            $asset->payout_percent = 90;
            return $asset;
        }

        return Asset::where('symbol', 'EUR/USD (OTC)')->first()
            ?? Asset::where('is_active', true)->first()
            ?? Asset::whereNotNull('symbol')->first();
    }

    private function checkTimeframe(Asset $asset, string $key, int $page, int $now, int $cutoff): void
    {
        $label = str_pad($key, 4);
        $expected = min($page, OtcPriceEngine::maxPageSizeFor($key));

        // Live page (includes the forming candle).
        $live = OtcPriceEngine::generateCandles($asset, $page, $key, null);
        $problems = $this->checkPage($live, $key, $expected, true, $now);
        $formingClose = end($live)['close'] ?? null;
        if ($formingClose !== null && !$this->isLiveTick($asset, $formingClose, $now)) {
            $problems[] = 'forming close is not the live tick price';
        }
        $formingStart = end($live)['time'] ?? null;
        if ($formingStart !== OtcPriceEngine::candleStartForTimestamp($now, $key)) {
            $problems[] = 'forming candle does not start at the current bucket';
        }

        // Oldest window: ends just after the bucket containing the cutoff,
        // so the last candle IS the cutoff bucket (completed).
        $oldEnd = OtcPriceEngine::nextCandleStart(
            OtcPriceEngine::candleStartForTimestamp($cutoff, $key), $key
        );
        $old = OtcPriceEngine::generateCandles($asset, $page, $key, $oldEnd);
        $problems = array_merge($problems, $this->checkPage($old, $key, $expected, false, $oldEnd));
        if (($old[0]['time'] ?? PHP_INT_MAX) > $cutoff) {
            $problems[] = 'oldest page does not reach the cutoff';
        }

        // Concatenation: page ending exactly at the oldest page's first time.
        if ($old !== []) {
            $older = OtcPriceEngine::generateCandles($asset, $page, $key, $old[0]['time']);
            $problems = array_merge($problems, $this->checkPage($older, $key, $expected, false, $old[0]['time']));
            $lastOlder = end($older);
            $firstOld = $old[0];
            if ($lastOlder['close'] !== $firstOld['open']) {
                $problems[] = 'page boundary: older last close != newer first open';
            }
            if (OtcPriceEngine::nextCandleStart($lastOlder['time'], $key) !== $firstOld['time']) {
                $problems[] = 'page boundary: buckets not adjacent';
            }
        }

        $oldest = $old[0]['time'] ?? null;
        $detail = $oldest !== null
            ? 'oldest=' . gmdate('Y-m-d', $oldest) . ' depth=' . round(($now - $oldest) / 31557600, 2) . 'y'
            : 'no candles';

        if ($problems === []) {
            $this->info("PASS {$label} {$detail}");
        } else {
            $this->failures++;
            $this->error("FAIL {$label} {$detail} :: " . implode('; ', array_unique($problems)));
        }
    }

    /**
     * Structural checks shared by every probe page.
     *
     * @return string[] problems found (empty = pass)
     */
    private function checkPage(array $candles, string $key, int $expected, bool $live, int $windowEnd): array
    {
        $problems = [];
        if (count($candles) !== $expected) {
            $problems[] = 'expected ' . $expected . ' candles, got ' . count($candles);
        }
        if ($candles === []) {
            return array_merge($problems, ['empty page']);
        }

        $prev = null;
        foreach ($candles as $candle) {
            if (!$this->isAligned($candle['time'], $key)) {
                $problems[] = 'candle time ' . $candle['time'] . ' not aligned to ' . $key;
                break;
            }
            if ($candle['high'] < max($candle['open'], $candle['close']) || $candle['low'] > min($candle['open'], $candle['close'])) {
                $problems[] = 'high/low do not bracket open/close at ' . $candle['time'];
                break;
            }
            if ($prev !== null) {
                if ($candle['open'] !== $prev['close']) {
                    $problems[] = 'continuity break at ' . $candle['time'] . ': open != prev close';
                    break;
                }
                if (OtcPriceEngine::nextCandleStart($prev['time'], $key) !== $candle['time']) {
                    $problems[] = 'gap/overlap at ' . $candle['time'];
                    break;
                }
            }
            $prev = $candle;
        }

        // Window bound (completed pages only): the last candle ends
        // exactly at the exclusive window end. A live page's forming
        // candle instead ends at the next bucket boundary after now.
        $last = end($candles);
        $lastEnd = OtcPriceEngine::nextCandleStart($last['time'], $key);
        if (!$live && $lastEnd !== $windowEnd) {
            $problems[] = 'last candle ends at ' . $lastEnd . ', expected window end ' . $windowEnd;
        }

        return $problems;
    }

    private function isAligned(int $time, string $key): bool
    {
        if ($key === '1M') {
            return (int) gmdate('j', $time) === 1 && $time % 86400 === 0;
        }
        if ($key === '1w') {
            return (int) gmdate('N', $time) === 1 && $time % 86400 === 0;
        }
        $seconds = OtcPriceEngine::supportedTimeframes()[$key] ?? 60;
        return $time % $seconds === 0;
    }

    /**
     * The forming candle's close is the live tick; allow for the
     * one-second race between generateCandles()' internal time()
     * call and this check.
     */
    private function isLiveTick(Asset $asset, float $close, int $now): bool
    {
        for ($t = $now - 2; $t <= $now + 1; $t++) {
            if (OtcPriceEngine::getDeterministicPrice($asset, (float) $t) === $close) {
                return true;
            }
        }
        return false;
    }

    private function checkAggregation(Asset $asset, int $cutoff): void
    {
        // 1m vs 3s over a 30-minute span ending 3 years ago.
        $spanEnd = OtcPriceEngine::candleStartForTimestamp($cutoff, '1m');
        $minutes = OtcPriceEngine::generateCandles($asset, 30, '1m', $spanEnd);
        $seconds = OtcPriceEngine::generateCandles($asset, 600, '3s', $spanEnd);
        $bad = 0;
        foreach ($minutes as $i => $minute) {
            $group = array_slice($seconds, $i * 20, 20);
            if (count($group) !== 20) {
                $bad++;
                continue;
            }
            $agg = [
                'time' => $group[0]['time'],
                'open' => $group[0]['open'],
                'high' => max(array_column($group, 'high')),
                'low' => min(array_column($group, 'low')),
                'close' => $group[19]['close'],
            ];
            if ($agg !== $minute) {
                $bad++;
            }
        }
        if ($bad === 0) {
            $this->info('PASS agg  1m == aggregation of its 3s candles (30 min @ ' . gmdate('Y-m-d H:i', $spanEnd) . ' UTC)');
        } else {
            $this->failures++;
            $this->error("FAIL agg  1m vs 3s: {$bad}/30 minutes mismatch");
        }

        // Monthly (and weekly) vs daily, for the month containing the cutoff.
        $monthStart = OtcPriceEngine::candleStartForTimestamp($cutoff, '1M');
        $monthEnd = OtcPriceEngine::nextCandleStart($monthStart, '1M');
        $daysInMonth = (int) round(($monthEnd - $monthStart) / 86400);
        $month = OtcPriceEngine::generateCandles($asset, 1, '1M', $monthEnd);
        $days = OtcPriceEngine::generateCandles($asset, $daysInMonth, '1d', $monthEnd);

        $aggMonth = count($days) === $daysInMonth ? [
            'time' => $days[0]['time'],
            'open' => $days[0]['open'],
            'high' => max(array_column($days, 'high')),
            'low' => min(array_column($days, 'low')),
            'close' => $days[$daysInMonth - 1]['close'],
        ] : null;

        if ($aggMonth !== null && $aggMonth === ($month[0] ?? null)) {
            $this->info("PASS agg  1M == aggregation of its {$daysInMonth} daily candles (" . gmdate('Y-m', $monthStart) . ')');
        } else {
            $this->failures++;
            $this->error('FAIL agg  1M vs daily: mismatch for ' . gmdate('Y-m', $monthStart));
        }

        $weekStart = OtcPriceEngine::candleStartForTimestamp($cutoff, '1w');
        $weekEnd = OtcPriceEngine::nextCandleStart($weekStart, '1w');
        $week = OtcPriceEngine::generateCandles($asset, 1, '1w', $weekEnd);
        $weekDays = OtcPriceEngine::generateCandles($asset, 7, '1d', $weekEnd);
        $aggWeek = count($weekDays) === 7 ? [
            'time' => $weekDays[0]['time'],
            'open' => $weekDays[0]['open'],
            'high' => max(array_column($weekDays, 'high')),
            'low' => min(array_column($weekDays, 'low')),
            'close' => $weekDays[6]['close'],
        ] : null;
        if ($aggWeek !== null && $aggWeek === ($week[0] ?? null)) {
            $this->info('PASS agg  1w == aggregation of its 7 daily candles (week of ' . gmdate('Y-m-d', $weekStart) . ')');
        } else {
            $this->failures++;
            $this->error('FAIL agg  1w vs daily: mismatch for week of ' . gmdate('Y-m-d', $weekStart));
        }
    }

    private function checkFreshAsset(int $cutoff): void
    {
        $symbol = 'ZZZ/QQQ (OTC)';
        if (Asset::where('symbol', $symbol)->exists()) {
            $symbol = 'ZZZ/QQQ2 (OTC)';
        }

        $asset = new Asset();
        $asset->symbol = $symbol;
        $asset->type = 'forex';
        $asset->control_mode = 'auto';
        $asset->payout_percent = 90;

        $existedBefore = Asset::where('symbol', $symbol)->exists();
        $problems = [];
        foreach (['1m', '1d', '1M'] as $key) {
            $oldEnd = OtcPriceEngine::nextCandleStart(
                OtcPriceEngine::candleStartForTimestamp($cutoff, $key), $key
            );
            $page = OtcPriceEngine::generateCandles($asset, 25, $key, $oldEnd);
            $problems = array_merge($problems, $this->checkPage($page, $key, 25, false, $oldEnd));
        }

        $existedAfter = Asset::where('symbol', $symbol)->exists();
        if ($existedBefore || $existedAfter) {
            $problems[] = 'fresh-asset probe touched the database (a row exists for the test symbol)';
        }

        if ($problems === []) {
            $this->info("PASS fresh brand-new symbol '{$symbol}' shows 3-year history with NO seeding step and NO database row.");
        } else {
            $this->failures++;
            $this->error("FAIL fresh '{$symbol}' :: " . implode('; ', array_unique($problems)));
        }
    }
}
