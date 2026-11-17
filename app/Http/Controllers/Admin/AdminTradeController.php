<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Trade;
use App\Models\TradeSimulationControl;
use App\Models\User;
use App\Services\DemoCycleService;
use App\Services\LiveCycleService;
use App\Services\TradeResultService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class AdminTradeController extends Controller
{
    public function index()
    {
        $this->settleExpiredPendingTrades(300);

        return view('admin.live-trades.index', [
            'tradeMode' => TradeSimulationControl::currentMode(),
        ]);
    }

    public function setMode(Request $request)
    {
        if (!TradeSimulationControl::tableExists()) {
            return response()->json([
                'success' => false,
                'error' => 'Trade mode table is missing. Please run database migrations first.',
                'mode' => TradeSimulationControl::MODE_NORMAL,
            ], 503);
        }

        $data = $request->validate([
            'mode' => ['required', Rule::in([
                TradeSimulationControl::MODE_NORMAL,
                TradeSimulationControl::MODE_SIMULATION_WIN,
                TradeSimulationControl::MODE_SIMULATION_LOSS,
            ])],
        ]);

        $control = DB::transaction(function () use ($data) {
            $current = TradeSimulationControl::latest('id')->lockForUpdate()->first();

            if ($current) {
                $current->update([
                    'mode' => $data['mode'],
                    'version' => $current->version + 1,
                    'updated_by' => Auth::id(),
                ]);
                $control = $current->fresh();
            } else {
                $control = TradeSimulationControl::create([
                    'mode' => $data['mode'],
                    'version' => 1,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            }

            if (!AdminLog::record(
                'trade_simulation_mode_update',
                "Live trade global outcome mode changed to {$control->mode}",
                'trade_simulation_control',
                $control->id
            )) {
                throw new \RuntimeException('Trade simulation mode audit log write failed.');
            }

            return $control;
        });

        return response()->json([
            'success' => true,
            'mode' => $control->mode,
            'version' => $control->version,
        ]);
    }

    /**
     * Real-time JSON data for live trades monitor (polled every 3s)
     */
    public function data()
    {
        $this->settleExpiredPendingTrades(300);

        $pending = Trade::with(['user', 'asset'])
            ->where('result', 'pending')
            ->get()
            ->filter(fn ($trade) => !$this->isExpired($trade))
            ->sortBy(fn ($trade) => ($trade->opened_at ?: $trade->created_at)?->timestamp ?? 0)
            ->values();

        // Settled demo counts, one grouped query for the whole list,
        // so the admin can see each demo user's 8/2 position.
        $demoUserIds = $pending->where('account_type', 'demo')->pluck('user_id')->unique()->values()->all();
        $settledDemoCounts = $demoUserIds !== []
            ? Trade::whereIn('user_id', $demoUserIds)
                ->where('account_type', 'demo')
                ->whereIn('result', ['win', 'loss'])
                ->selectRaw('user_id, COUNT(*) as cnt')
                ->groupBy('user_id')
                ->pluck('cnt', 'user_id')->all()
            : [];
        $demoOffsets = [];

        // Settled live counts, one grouped query for the whole list,
        // so the admin can see each live user's 4/6 position.
        $liveUserIds = $pending->where('account_type', 'live')->pluck('user_id')->unique()->values()->all();
        $settledLiveCounts = $liveUserIds !== []
            ? Trade::whereIn('user_id', $liveUserIds)
                ->where('account_type', 'live')
                ->whereIn('result', ['win', 'loss'])
                ->selectRaw('user_id, COUNT(*) as cnt')
                ->groupBy('user_id')
                ->pluck('cnt', 'user_id')->all()
            : [];
        $liveOffsets = [];

        $trades = $pending->map(function ($trade) use ($settledDemoCounts, &$demoOffsets, $settledLiveCounts, &$liveOffsets) {
                $user       = $trade->user;
                $asset      = $trade->asset;
                $openedTs   = $trade->opened_at ? $trade->opened_at->timestamp : $trade->created_at->timestamp;
                $duration   = (int) ($trade->duration_seconds ?: 60);
                $elapsed    = time() - $openedTs;
                $remaining  = max(0, $duration - $elapsed);
                $isExpired  = $elapsed >= $duration;

                // Current simulated profit/loss %
                $profitPct = $trade->direction === 'up'
                    ? round(($asset->payout_percent ?? 90), 2)
                    : round(-100, 2);

                // Balance of the account this trade was placed on:
                // demo trades used demo_balance, not live_balance.
                $isDemo = $trade->account_type === 'demo';
                $balance = $isDemo ? (float) ($user?->demo_balance ?? 0) : (float) ($user?->live_balance ?? 0);

                // Demo 8/2 position for this trade, e.g. "4/10",
                // counting settled demo trades plus earlier pending
                // demo trades for the same user.
                $demoCycle = null;
                $demoCycleResult = null;
                if ($isDemo && $user) {
                    $userId = (int) $user->id;
                    $effectiveCount = (int) ($settledDemoCounts[$userId] ?? 0) + (int) ($demoOffsets[$userId] ?? 0);
                    $demoOffsets[$userId] = (int) ($demoOffsets[$userId] ?? 0) + 1;
                    $demoCycle = DemoCycleService::positionLabel($effectiveCount);
                    $demoCycleResult = DemoCycleService::isLoss($userId, $effectiveCount) ? 'loss' : 'win';
                }

                // Live 4/6 position for this trade, e.g. "4/10",
                // counting settled live trades plus earlier pending
                // live trades for the same user.
                $liveCycle = null;
                $liveCycleResult = null;
                if (!$isDemo && $user) {
                    $userId = (int) $user->id;
                    $effectiveCount = (int) ($settledLiveCounts[$userId] ?? 0) + (int) ($liveOffsets[$userId] ?? 0);
                    $liveOffsets[$userId] = (int) ($liveOffsets[$userId] ?? 0) + 1;
                    $liveCycle = LiveCycleService::positionLabel($effectiveCount);
                    $liveCycleResult = LiveCycleService::isLoss($userId, $effectiveCount) ? 'loss' : 'win';
                }

                return [
                    'trade_id'        => $trade->id,
                    'user_id'         => $user?->id,
                    'user_name'       => $user?->name ?? 'Unknown',
                    'user_balance'    => number_format($balance, 2),
                    'account_age'     => $user ? $user->getAccountAgeDays() : 0,
                    'referral_count'  => $user ? $user->referrals()->count() : 0,
                    'total_trades'    => $user ? $user->trades()->count() : 0,
                    'asset_symbol'    => $asset?->symbol ?? 'Unknown',
                    'direction'       => $trade->direction,
                    'investment'      => (float) $trade->investment,
                    'duration_min'    => round($duration / 60, 1),
                    'remaining_sec'   => $remaining,
                    'is_expired'       => $isExpired,
                    'lifecycle_state'  => $isExpired ? 'expired_pending' : 'active_pending',
                    'payout'          => (float) $trade->payout,
                    'admin_override'  => $trade->admin_override,
                    'account_type'    => $trade->account_type,
                    'demo_cycle'      => $demoCycle,
                    'demo_cycle_result' => $demoCycleResult,
                    'live_cycle'      => $liveCycle,
                    'live_cycle_result' => $liveCycleResult,
                ];
            })
            ->values();

        return response()->json($trades);
    }

    private function settleExpiredPendingTrades(int $limit = 300): void
    {
        $processed = 0;

        Trade::with('asset')
            ->where('result', 'pending')
            ->orderBy('opened_at')
            ->orderBy('id')
            ->limit($limit * 2)
            ->get()
            ->each(function (Trade $trade) use (&$processed, $limit) {
                if ($processed >= $limit || !$this->isExpired($trade)) {
                    return;
                }

                try {
                    TradeResultService::settle($trade, null);
                    $processed++;
                } catch (\Throwable $e) {
                    Log::error('Admin live trade expired settlement failed.', [
                        'trade_id' => $trade->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            });
    }

    private function isExpired(Trade $trade): bool
    {
        $openedAt = $trade->opened_at ?: $trade->created_at;
        $duration = (int) ($trade->duration_seconds ?: 60);

        return $openedAt && now()->greaterThanOrEqualTo($openedAt->copy()->addSeconds($duration));
    }

    public function forceWin(Trade $trade)
    {
        $updated = $this->updateOverride($trade, 'force_win', 'trade_force_win', 'Forced WIN');
        if (!$updated) return response()->json(['error' => 'Trade already settled.'], 422);

        return response()->json(['success' => true, 'message' => 'Trade marked for forced WIN.']);
    }

    public function forceLoss(Trade $trade)
    {
        $updated = $this->updateOverride($trade, 'force_loss', 'trade_force_loss', 'Forced LOSS');
        if (!$updated) return response()->json(['error' => 'Trade already settled.'], 422);

        return response()->json(['success' => true, 'message' => 'Trade marked for forced LOSS.']);
    }

    public function resetOverride(Trade $trade)
    {
        $updated = $this->updateOverride($trade, 'none', 'trade_reset_override', 'Reset override');
        if (!$updated) return response()->json(['error' => 'Trade already settled.'], 422);

        return response()->json(['success' => true, 'message' => 'Override reset. Trade will follow market.']);
    }

    private function updateOverride(Trade $trade, string $override, string $action, string $label): bool
    {
        return DB::transaction(function () use ($trade, $override, $action, $label) {
            $lockedTrade = Trade::whereKey($trade->id)->lockForUpdate()->firstOrFail();

            if ($lockedTrade->result !== 'pending') {
                return false;
            }

            // Record when Win/Loss was pressed. The column is only written
            // when the migration has run, so the button still works on a
            // database that has not been migrated yet.
            $updates = ['admin_override' => $override];
            if (Schema::hasColumn('trades', 'override_set_at')) {
                $updates['override_set_at'] = $override === 'none' ? null : now();
            }
            $lockedTrade->update($updates);

            if (!AdminLog::record(
                $action,
                "{$label} on trade #{$lockedTrade->id} for user_id={$lockedTrade->user_id}",
                'trade',
                $lockedTrade->id
            )) {
                throw new \RuntimeException('Trade override audit log write failed.');
            }

            return true;
        });
    }
}
