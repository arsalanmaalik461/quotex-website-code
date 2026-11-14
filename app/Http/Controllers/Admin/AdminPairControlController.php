<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Asset;
use App\Models\Trade;
use App\Services\GlobalPolicyEvaluator;
use App\Services\OtcPriceEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPairControlController extends Controller
{
    public function index()
    {
        $pairsData = $this->getPairsSummary();
        $marketPairs = Asset::where('is_active', true)
            ->orderBy('symbol')
            ->get(['id', 'symbol', 'name', 'payout_percent', 'digits', 'type']);
        $marketPairOptions = $marketPairs->map(fn (Asset $pair) => [
            'id' => $pair->id,
            'symbol' => $pair->symbol,
            'name' => $pair->name,
            'payout' => $pair->payout_percent,
            'digits' => $pair->digits,
            'type' => $pair->type,
        ])->values();

        return view('admin.pair-control.index', compact('pairsData', 'marketPairs', 'marketPairOptions'));
    }

    /**
     * JSON endpoint for real-time polling
     */
    public function data()
    {
        return response()->json($this->getPairsSummary());
    }

    public function monitor(Request $request, GlobalPolicyEvaluator $policy)
    {
        $timeframe = 60;
        $now = time();
        $currentStart = (int) floor($now / $timeframe) * $timeframe;
        $nextStart = $currentStart + $timeframe;

        $selectedAsset = null;
        if ($request->filled('pair_id')) {
            $selectedAsset = Asset::where('is_active', true)->find($request->integer('pair_id'));
        }
        if (!$selectedAsset && $request->filled('symbol')) {
            $symbol = $request->query('symbol');
            $selectedAsset = Asset::where('is_active', true)
                ->where(function ($query) use ($symbol) {
                    $query->where('symbol', $symbol)->orWhere('code', $symbol);
                })
                ->first();
        }

        if ($selectedAsset) {
            return response()->json([
                'global_status' => $policy->frontendStatus($selectedAsset),
                'pair' => $this->buildMonitorPair($selectedAsset, $policy, $now, $currentStart, $nextStart, $timeframe, true),
            ]);
        }

        $pairs = Asset::where('is_active', true)
            ->orderBy('symbol')
            ->get()
            ->map(fn (Asset $asset) => $this->buildMonitorPair($asset, $policy, $now, $currentStart, $nextStart, $timeframe, false))
            ->values();

        return response()->json([
            'global_status' => $policy->frontendStatus(),
            'pairs' => $pairs,
        ]);
    }

    private function buildMonitorPair(
        Asset $asset,
        GlobalPolicyEvaluator $policy,
        int $now,
        int $currentStart,
        int $nextStart,
        int $timeframe,
        bool $includeCandles = false
    ): array {
        $currentPrice = OtcPriceEngine::getDeterministicPrice($asset, (float) $now);
        $currentOpen = OtcPriceEngine::getDeterministicPrice($asset, (float) $currentStart);
        $nextOpen = OtcPriceEngine::getDeterministicPrice($asset, (float) $nextStart);
        $nextClose = OtcPriceEngine::getDeterministicPrice($asset, (float) ($nextStart + $timeframe));
        $controlMode = $asset->control_mode ?? 'auto';
        $winSide = $asset->win_side ?? 'none';
        $hasPairOverride = $controlMode !== 'auto' || $winSide !== 'none';

        $payload = [
            'id' => $asset->id,
            'symbol' => $asset->symbol,
            'digits' => $asset->digits,
            'current_price' => $currentPrice,
            'current_candle' => $currentPrice >= $currentOpen ? 'up' : 'down',
            'next_calculated_candle' => $nextClose >= $nextOpen ? 'up' : 'down',
            'time_remaining' => max(0, $nextStart - $now),
            'pair_control' => [
                'control_mode' => $controlMode,
                'win_side' => $winSide,
                'control_expires_at' => $asset->control_expires_at?->toIso8601String(),
            ],
            'effective_control' => [
                'source' => $hasPairOverride ? 'pair' : 'global',
                'label' => $hasPairOverride
                    ? ('PAIR ' . strtoupper($controlMode !== 'auto' ? $controlMode : str_replace('_', ' ', $winSide)))
                    : 'GLOBAL AUTO',
            ],
            'global_status' => $policy->frontendStatus($asset),
        ];

        if ($includeCandles) {
            $payload['candles'] = OtcPriceEngine::generateCandles($asset, 80, $timeframe);
        }

        return $payload;
    }

    /**
     * Set pair control mode (Pump / Crash / Win Side rule)
     */
    public function setMode(Request $request, Asset $asset)
    {
        $request->validate([
            'control_mode' => 'required|in:auto,pump,crash,force_win,force_loss',
            'win_side'     => 'nullable|in:none,majority_investment,minority_investment,majority_users,minority_users',
            'duration'     => 'nullable|integer|min:1|max:1440',
        ]);

        $mode = $request->control_mode;
        $winSide = $request->win_side ?? 'none';
        $duration = (int)($request->duration ?? 15);

        $expiresAt = null;
        if (!in_array($mode, ['auto', 'force_win', 'force_loss'])) {
            $expiresAt = now()->addMinutes($duration);
        }

        $asset = DB::transaction(function () use ($asset, $mode, $winSide, $duration, $expiresAt) {
            $lockedAsset = Asset::whereKey($asset->id)->lockForUpdate()->firstOrFail();
            $updates = OtcPriceEngine::prepareControlTransition($lockedAsset, $mode, $expiresAt);
            $lockedAsset->update(array_merge($updates, [
                'control_mode'          => $mode,
                'control_expires_at'    => $expiresAt,
                'pump_duration_minutes' => $duration,
                'win_side'              => $winSide,
            ]));

            $modeName = strtoupper($mode);
            $actionDesc = "Set pair {$lockedAsset->symbol} mode to {$modeName} (win_side={$winSide}, duration={$duration}m)";
            if (!AdminLog::record('pair_control', $actionDesc, 'asset', $lockedAsset->id)) {
                throw new \RuntimeException('Pair control audit log write failed.');
            }

            return $lockedAsset->fresh();
        });

        $modeName = strtoupper($mode);

        return response()->json([
            'success' => true,
            'message' => "Pair {$asset->symbol} updated to {$modeName}.",
            'asset'   => [
                'id'           => $asset->id,
                'symbol'       => $asset->symbol,
                'control_mode' => $asset->control_mode,
                'win_side'     => $asset->win_side,
                'expires_at'   => $asset->control_expires_at?->format('H:i:s'),
            ]
        ]);
    }

    /**
     * Reset pair to Auto (Free Market)
     */
    public function resetMode(Asset $asset)
    {
        $asset = DB::transaction(function () use ($asset) {
            $lockedAsset = Asset::whereKey($asset->id)->lockForUpdate()->firstOrFail();
            $updates = OtcPriceEngine::prepareControlTransition($lockedAsset, 'auto', null);
            $lockedAsset->update(array_merge($updates, [
                'control_mode'       => 'auto',
                'control_expires_at' => null,
                'win_side'           => 'none',
            ]));

            if (!AdminLog::record('pair_reset', "Reset pair {$lockedAsset->symbol} to AUTO (Free Market)", 'asset', $lockedAsset->id)) {
                throw new \RuntimeException('Pair control audit log write failed.');
            }

            return $lockedAsset->fresh();
        });

        return response()->json([
            'success' => true,
            'message' => "Pair {$asset->symbol} reset to Auto (Free Market).",
        ]);
    }

    /**
     * Helper to assemble real-time summary for all active pairs
     */
    private function getPairsSummary(): array
    {
        $assets = Asset::where('is_active', true)->get();

        // Get all pending trades grouped by asset and direction
        $pendingTrades = Trade::with('user')
            ->where('result', 'pending')
            ->get();

        $tradesByAsset = $pendingTrades->groupBy('asset_id');

        $result = [];

        foreach ($assets as $asset) {
            $tradesForAsset = $tradesByAsset->get($asset->id, collect());

            $upTrades   = $tradesForAsset->where('direction', 'up');
            $downTrades = $tradesForAsset->where('direction', 'down');

            $upUsersCount   = $upTrades->pluck('user_id')->unique()->count();
            $downUsersCount = $downTrades->pluck('user_id')->unique()->count();

            $upInvestment   = (float) $upTrades->sum('investment');
            $downInvestment = (float) $downTrades->sum('investment');
            $totalTrades    = $tradesForAsset->count();
            $totalInvestment= $upInvestment + $downInvestment;

            // Auto-check expired control
            OtcPriceEngine::checkAndResetExpiredControl($asset);

            // Current price
            $currentPrice = (float) ($asset->base_price_override ?? 1.0);

            $result[] = [
                'id'               => $asset->id,
                'symbol'           => $asset->symbol,
                'name'             => $asset->name,
                'flag'             => $asset->flag ?? '🌐',
                'type'             => $asset->type,
                'current_price'    => $currentPrice,
                'payout_percent'   => $asset->payout_percent,
                'control_mode'     => $asset->control_mode ?? 'auto',
                'win_side'         => $asset->win_side ?? 'none',
                'control_expires'  => $asset->control_expires_at?->diffForHumans(),
                'total_trades'     => $totalTrades,
                'total_investment' => $totalInvestment,
                'up_count'         => $upTrades->count(),
                'up_users'         => $upUsersCount,
                'up_investment'    => $upInvestment,
                'down_count'       => $downTrades->count(),
                'down_users'       => $downUsersCount,
                'down_investment'  => $downInvestment,
                'recent_traders'   => $tradesForAsset->map(fn($t) => [
                    'id'         => $t->id,
                    'user'       => $t->user?->name ?? 'User',
                    'direction'  => $t->direction,
                    'investment' => (float)$t->investment,
                    'override'   => $t->admin_override,
                ])->take(5)->values(),
            ];
        }

        // Sort assets: pairs with active trades first, then alphabetically
        usort($result, function ($a, $b) {
            if ($a['total_trades'] !== $b['total_trades']) {
                return $b['total_trades'] <=> $a['total_trades'];
            }
            return strcmp($a['symbol'], $b['symbol']);
        });

        return $result;
    }
}
