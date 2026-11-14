<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Trade;
use App\Models\TradeSimulationControl;
use App\Models\User;
use App\Services\GlobalPolicyEvaluator;
use App\Services\OtcPriceEngine;
use App\Services\TradeResultService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TradeController extends Controller
{
    // Real Quotex caps a single trade at $1,000 — same cap as the JS client
    // (MAX_TRADE_INVESTMENT in public/js/trade.js). Enforced in placeTrade
    // validation below; do not raise without changing both sides.
    public const MAX_TRADE_INVESTMENT = 1000;

    public static function settleExpiredTradesForUser($user)
    {
        if (!$user) return;

        $pendingTrades = Trade::with('asset')
            ->where('user_id', $user->id)
            ->where('result', 'pending')
            ->get();

        foreach ($pendingTrades as $trade) {
            $openedTs = $trade->opened_at ? $trade->opened_at->timestamp : $trade->created_at->timestamp;
            $duration = (int) ($trade->duration_seconds ?: 60);
            if (time() >= ($openedTs + $duration)) {
                TradeResultService::settle($trade, null);
            }
        }
    }

    public function index(Request $request)
    {
        try {
            $btc = \App\Models\Asset::where('symbol', 'Bitcoin (OTC)')->first();
            if (!$btc || (float)$btc->base_price_override < 70000 || \App\Models\Asset::count() < 90) {
                (new \Database\Seeders\AssetSeeder())->run();
            }
        } catch (\Throwable $e) {}

        $user = Auth::user();
        self::settleExpiredTradesForUser($user);

        // Fetch still-active running trades
        $activeTrades = Trade::with('asset')
            ->where('user_id', $user->id)
            ->where('result', 'pending')
            ->get()
            ->map(function ($t) {
                $openedTs = $t->opened_at ? $t->opened_at->timestamp : $t->created_at->timestamp;
                $duration = (int) ($t->duration_seconds ?: 60);
                $elapsed = time() - $openedTs;
                $remaining = max(1, $duration - $elapsed);
                return [
                    'trade_id'   => $t->id,
                    'asset'      => [
                        'id' => $t->asset->id ?? 1,
                        'symbol' => $t->asset->symbol ?? 'AUD/CHF',
                        'flag' => $t->asset->flag_icon ?? '🇦🇺🇨🇭',
                        'payout' => $t->asset->payout_percent ?? 85,
                    ],
                    'direction'  => $t->direction,
                    'investment' => (float)$t->investment,
                    'payout'     => (float)$t->payout,
                    'open_price' => (float)$t->open_price,
                    'duration'   => $duration,
                    'remaining'  => $remaining,
                    'expires_at' => (time() + $remaining) * 1000,
                ];
            })->values();

        $btcAsset = Asset::where('symbol', 'like', '%BTC%')->orWhere('symbol', 'like', '%Bitcoin%')->first();
        $defaultId = $btcAsset ? $btcAsset->id : 1;
        $assetId = $request->query('asset', $defaultId);
        $assets = Asset::where('is_active', true)->get();
        // Real per-asset values (Team 4 P0 fix): 5-min / 24h change from
        // actual candle closes, payout from the stored admin-configured
        // value, sentiment from the platform's own recent trades (or the
        // last-20-candle trend). Attached here so the view can render
        // them instead of the old crc32-derived values.
        $statsById = collect(OtcPriceEngine::assetStatsBulk($assets))->keyBy('asset_id');
        $assets = $assets->map(function ($a) use ($statsById) {
            $st = $statsById->get((int) $a->id);
            $a->current_price = OtcPriceEngine::getBasePrice($a);
            $a->payout_1m_pct = $st['payout_1m_pct'] ?? OtcPriceEngine::payoutPercent($a);
            $a->payout_5m_pct = $st['payout_5m_pct'] ?? OtcPriceEngine::payoutPercent($a);
            $a->change_5m_pct = $st['change_5m_pct'] ?? null;
            $a->change_24h_pct = $st['change_24h_pct'] ?? null;
            $a->sentiment_buy_pct = $st['sentiment_buy_pct'] ?? null;
            $a->sentiment_sell_pct = $st['sentiment_sell_pct'] ?? null;
            $a->sentiment_source = $st['sentiment_source'] ?? null;
            return $a;
        });
        $selectedAsset = $assets->firstWhere('id', (int)$assetId) ?? $btcAsset ?? $assets->first();
        if ($selectedAsset && empty($selectedAsset->current_price)) {
            $selectedAsset->current_price = OtcPriceEngine::getBasePrice($selectedAsset);
        }

        $recentTrades = Trade::with('asset')
            ->where('user_id', $user->id)
            ->where('result', '!=', 'pending')
            ->latest('closed_at')
            ->limit(20)
            ->get();

        $todayTrades = Trade::where('user_id', $user->id)
            ->whereIn('result', ['win', 'loss'])
            ->where(function ($q) {
                $q->whereDate('closed_at', now()->toDateString())
                  ->orWhereDate('created_at', now()->toDateString());
            })
            ->get();

        // Signed P/L for the day: a win adds payout - investment, a
        // loss subtracts the investment (less any early-sell buyout),
        // a draw adds 0. Not clamped at zero — a losing day must show
        // as negative, or losses are invisible.
        $todayProfit = 0.0;
        foreach ($todayTrades as $t) {
            $todayProfit += TradeResultService::settledProfit($t);
        }

        return view('trade.index', compact('assets', 'selectedAsset', 'user', 'recentTrades', 'activeTrades', 'todayProfit'));
    }

    public function placeTrade(Request $request)
    {
        $request->validate([
            'asset_id'         => 'required|exists:assets,id',
            'direction'        => 'required|in:up,down',
            'investment'       => 'required|numeric|min:1|max:' . self::MAX_TRADE_INVESTMENT,
            'duration_seconds' => 'required|integer|min:5',
        ], [
            'investment.max' => 'Maximum trade amount is $1,000.',
        ]);

        $asset = Asset::findOrFail($request->asset_id);
        $policy = app(GlobalPolicyEvaluator::class)->entryAllowed($asset);
        if (!$policy['allowed']) {
            return response()->json(['error' => $policy['message']], 423);
        }

        $tradeData = DB::transaction(function () use ($request, $asset) {
            $user = User::whereKey(Auth::id())->lockForUpdate()->firstOrFail();
            $investment = (float) $request->investment;
            $accountType = $user->account_type;
            $balance = $accountType === 'live' ? (float) $user->live_balance : (float) $user->demo_balance;

            if ($investment > $balance) {
                return ['error' => 'Insufficient balance.', 'status' => 422];
            }

            $openPrice = OtcPriceEngine::simulatePrice($asset);
            $payout = round($investment * (1 + $asset->payout_percent / 100), 2);
            $simulationMode = TradeSimulationControl::currentMode();
            $adminOverride = TradeSimulationControl::overrideForMode($simulationMode);

            if ($accountType === 'live') {
                $user->live_balance = round($balance - $investment, 2);
            } else {
                $user->demo_balance = round($balance - $investment, 2);
            }
            $user->save();

            $tradeAttributes = [
                'user_id' => $user->id,
                'asset_id' => $asset->id,
                'direction' => $request->direction,
                'investment' => $investment,
                'payout' => $payout,
                'open_price' => $openPrice,
                'duration_seconds' => $request->duration_seconds,
                'result' => 'pending',
                'account_type' => $accountType,
                'admin_override' => $adminOverride,
                'opened_at' => now(),
            ];
            // Only write override_set_at once the migration has run.
            if ($adminOverride !== 'none' && Schema::hasColumn('trades', 'override_set_at')) {
                $tradeAttributes['override_set_at'] = now();
            }
            $trade = Trade::create($tradeAttributes);

            return [
                'trade' => $trade,
                'open_price' => $openPrice,
                'payout' => $payout,
                'balance' => $user->getBalance(),
            ];
        });

        if (isset($tradeData['error'])) {
            return response()->json(['error' => $tradeData['error']], $tradeData['status']);
        }

        $trade = $tradeData['trade'];

        return response()->json([
            'success'    => true,
            'trade_id'   => $trade->id,
            'open_price' => $tradeData['open_price'],
            'payout'     => $tradeData['payout'],
            'duration'   => $request->duration_seconds,
            'balance'    => $tradeData['balance'],
        ]);
    }

    public function settleTrade(Request $request, Trade $trade)
    {
        if ($trade->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        if ($trade->result !== 'pending') {
            $settled = TradeResultService::settle($trade, null);
            return response()->json($this->settlementResponse($settled));
        }

        // Early sell path
        if ($request->boolean('early_sell')) {
            $clientClosePrice = $request->filled('close_price') && (float) $request->close_price > 0
                ? (float) $request->close_price
                : null;
            $settled = TradeResultService::earlySell($trade, $clientClosePrice);

            if ($settled['expired'] ?? false) {
                $settled = TradeResultService::settle($trade, null);
            }

            return response()->json($this->settlementResponse($settled + ['success' => true]));
        }

        // Get client close price if available
        $clientClosePrice = $request->filled('close_price') && (float) $request->close_price > 0
            ? round((float) $request->close_price, 5)
            : null;

        // Use TradeResultService (handles admin override + OTC spike)
        $settled = TradeResultService::settle($trade, $clientClosePrice);

        return response()->json($this->settlementResponse($settled));
    }

    public function switchAccount(Request $request)
    {
        $request->validate([
            'account_type' => 'required|in:demo,live',
            'new_balance'  => 'nullable|numeric|min:0',
        ]);
        $user = Auth::user();
        $user->account_type = $request->account_type;

        // Demo balance only: live_balance is NEVER set from a user request.
        if ($request->filled('new_balance') && (float) $request->new_balance >= 0) {
            if ($request->account_type === 'demo') {
                $user->demo_balance = round((float) $request->new_balance, 2);
            }
        }

        $user->save();
        return response()->json([
            'success'      => true,
            'balance'      => $user->getBalance(),
            'account_type' => $user->account_type,
            'demo_balance' => $user->demo_balance,
            'live_balance' => $user->live_balance,
        ]);
    }

    public function refillDemo(Request $request)
    {
        $user = Auth::user();
        $amount = $request->filled('amount') ? (float) $request->amount : 10000.00;
        $user->demo_balance = round($amount, 2);
        $user->save();
        return response()->json([
            'success'      => true,
            'balance'      => $user->getBalance(),
            'demo_balance' => $user->demo_balance,
        ]);
    }

    public function priceData(Request $request)
    {
        $symbol = $request->query('symbol');
        $asset = null;
        if ($request->filled('asset_id')) {
            $asset = Asset::find($request->asset_id);
        }
        if (!$asset && $symbol) {
            $asset = Asset::where('symbol', $symbol)->first()
                ?? Asset::where('code', $symbol)->first()
                ?? Asset::where('symbol', 'like', "%{$symbol}%")->first();
        }
        if (!$asset) {
            $asset = Asset::where('is_active', true)->first();
        }
        if (!$asset) {
            $asset = new Asset();
            $asset->symbol = $symbol ?: 'BTC/USD';
            $asset->control_mode = 'auto';
            $asset->payout_percent = 85;
            $asset->type = (str_contains($symbol ?? '', 'BTC') || str_contains($symbol ?? '', 'ETH')) ? 'crypto' : 'forex';
        }

        // Real per-asset stats (Team 4 P0 fix). No new route: the
        // existing price-data endpoint answers stats requests.
        // ?stats=1   -> stats object for this one asset (sentiment strip).
        // ?stats=all -> {assets: [...]} for every active asset (asset
        //              modal 24h-change / 5-min payout columns).
        // Without ?stats the endpoint still returns the candle array.
        if ($request->query('stats') === 'all') {
            $all = Asset::where('is_active', true)->get();
            return response()->json(['assets' => OtcPriceEngine::assetStatsBulk($all)]);
        }
        if ($request->boolean('stats')) {
            return response()->json(OtcPriceEngine::assetStats($asset));
        }

        $count = $request->filled('count') ? (int) $request->query('count') : null;
        $timeframeSec = max(5, (int) $request->query('timeframe', 60));
        $endTime = $request->filled('to_time') 
            ? (int) $request->query('to_time') 
            : ($request->filled('end_time') ? (int) $request->query('end_time') : null);

        $candles = OtcPriceEngine::generateCandles($asset, $count, $timeframeSec, $endTime);

        return response()->json($candles);
    }

    private function settlementResponse(array $settled): array
    {
        return [
            'success' => $settled['success'] ?? true,
            'result' => $settled['result'],
            'close_price' => $settled['close_price'],
            // 'payout' is the gross amount credited back; 'profit' is
            // the signed P/L (payout - investment, negative on a loss,
            // 0 on a draw) and is what the UI must display as profit.
            'payout' => $settled['payout'],
            'profit' => $settled['profit'] ?? null,
            'balance' => $settled['balance'],
        ];
    }

    private function simulatePrice(string $symbol): float
    {
        $asset = Asset::where('symbol', $symbol)->first();
        if ($asset) {
            return OtcPriceEngine::simulatePrice($asset);
        }
        $base = $this->getBasePrice($symbol);
        $delta = ($base < 5) ? (mt_rand(-20, 20) / 100000) : (mt_rand(-50, 50) / 100);
        return round($base + $delta, 5);
    }

    private function getBasePrice(string $symbol): float
    {
        $prices = [
            'USD/NGN' => 1335.50,
            'EUR/USD' => 1.0854,
            'GBP/USD' => 1.2715,
            'USD/JPY' => 149.32,
            'AUD/CHF' => 0.58364,
            'BTC/USD' => 63250.00,
            'ETH/USD' => 2450.00,
        ];
        return $prices[$symbol] ?? 1.0;
    }
}
