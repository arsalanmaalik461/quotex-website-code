<?php

namespace App\Services;

use App\Http\Controllers\TradeController;
use App\Models\PendingOrder;
use App\Models\Trade;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PendingOrderExecutor
{
    /**
     * Grace window for By Time orders. The cron runs once a minute and the
     * in-page check runs every few seconds, so a healthy trigger fires
     * within seconds. If the trigger time passed longer ago than this
     * (e.g. the server was down), the order is marked expired and is
     * NEVER executed late — a time order must fire on time or not at all.
     */
    public const TIME_TRIGGER_GRACE_SECONDS = 120;

    /**
     * Execute every due pending order for one user.
     * Returns ['executed' => [trade summaries], 'expired' => [order summaries], 'failed' => int].
     */
    public static function executeDueForUser(User $user): array
    {
        $orders = PendingOrder::pending()
            ->where('user_id', $user->id)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        return self::processOrders($orders);
    }

    /**
     * Execute every due pending order across all users, bounded per run.
     */
    public static function executeDueAll(int $limit = 100): array
    {
        $limit = max(1, min(500, $limit));

        $orders = PendingOrder::pending()
            ->orderBy('created_at')
            ->orderBy('id')
            ->limit($limit)
            ->get();

        return self::processOrders($orders);
    }

    private static function processOrders($orders): array
    {
        $result = ['executed' => [], 'expired' => [], 'failed' => 0];
        $priceCache = [];
        $now = now();

        foreach ($orders as $order) {
            try {
                $outcome = self::processOne($order, $priceCache, $now);

                if ($outcome['outcome'] === 'executed' && $outcome['trade']) {
                    $summary = self::tradeSummary($outcome['trade']);
                    $summary['order_id'] = $order->id;
                    $result['executed'][] = $summary;
                } elseif ($outcome['outcome'] === 'expired') {
                    $result['expired'][] = self::orderSummary($order);
                }
            } catch (\Throwable $e) {
                // One bad order must never kill the whole run; the order
                // stays pending and is retried on the next run.
                $result['failed']++;
                Log::error('Pending order execution failed.', [
                    'pending_order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $result;
    }

    /**
     * Evaluate one order (trigger hit?) and, if due, execute it.
     * Returns ['outcome' => 'executed'|'expired'|null, 'trade' => ?Trade].
     */
    private static function processOne(PendingOrder $order, array &$priceCache, Carbon $now): array
    {
        $outcome = null;
        $trade = null;

        DB::transaction(function () use ($order, &$priceCache, $now, &$outcome, &$trade) {
            // Claim the row: a concurrent runner (cron + in-page check)
            // blocks here, then re-reads the status and skips the order.
            $locked = PendingOrder::whereKey($order->id)->lockForUpdate()->first();
            if (!$locked || !$locked->isPending()) {
                return;
            }

            if ($locked->order_type === PendingOrder::TYPE_PRICE) {
                $asset = $locked->asset;
                if (!$asset) {
                    // Asset is gone; the order can never trigger.
                    $locked->update(['status' => PendingOrder::STATUS_EXPIRED]);
                    $outcome = 'expired';
                    return;
                }

                if (!array_key_exists($asset->id, $priceCache)) {
                    // Same "current market price" the trade form uses.
                    $priceCache[$asset->id] = OtcPriceEngine::simulatePrice($asset);
                }

                if (!$locked->isPriceTriggered($priceCache[$asset->id])) {
                    return; // Trigger not hit yet — stays pending.
                }
            } else {
                // By Time.
                if (!$locked->isTimeReached($now)) {
                    return; // Not yet — stays pending.
                }

                if ($now->greaterThan($locked->trigger_time->copy()->addSeconds(self::TIME_TRIGGER_GRACE_SECONDS))) {
                    // Trigger passed long ago (server was down): expire,
                    // never execute late.
                    $locked->update(['status' => PendingOrder::STATUS_EXPIRED]);
                    $outcome = 'expired';
                    return;
                }
            }

            $user = $locked->user;
            if (!$user) {
                return;
            }

            // Pending orders belong to the account they were created on.
            // If the user has since switched demo/live, the order waits
            // until they switch back — it must never debit the other
            // account's balance.
            if ($user->account_type !== $locked->account_type) {
                return;
            }

            // Execute through the SAME placement path a normal trade uses
            // (TradeController::placeTrade): same validation, policy check,
            // balance debit, payout and admin-override handling.
            $placed = self::placeTradeForOrder($locked, $user);

            if ($placed['ok']) {
                $locked->update([
                    'status' => PendingOrder::STATUS_EXECUTED,
                    'trade_id' => $placed['trade_id'],
                ]);
                $trade = Trade::find($placed['trade_id']);
                $outcome = 'executed';
            } elseif ($placed['insufficient']) {
                // The money is gone (user spent it meanwhile) — the order
                // can never execute, so retire it instead of retrying
                // forever.
                $locked->update(['status' => PendingOrder::STATUS_EXPIRED]);
                $outcome = 'expired';
                Log::warning('Pending order expired: insufficient balance at execution.', [
                    'pending_order_id' => $locked->id,
                    'user_id' => $user->id,
                ]);
            } else {
                // Transient failure (trading paused, policy, …): throw so
                // this transaction rolls back and the order stays pending
                // for the next run.
                throw new \RuntimeException($placed['error'] ?? 'Trade placement failed.');
            }
        });

        return ['outcome' => $outcome, 'trade' => $trade];
    }

    /**
     * Place the trade for an order via TradeController::placeTrade.
     * Returns ['ok' => bool, 'trade_id' => ?int, 'error' => ?string,
     *          'insufficient' => bool].
     */
    private static function placeTradeForOrder(PendingOrder $order, User $user): array
    {
        try {
            // placeTrade reads Auth::id() for the balance debit.
            Auth::setUser($user);

            $request = Request::create('/trade/place', 'POST', [
                'asset_id' => $order->asset_id,
                'direction' => $order->direction,
                'investment' => (float) $order->investment,
                'duration_seconds' => (int) $order->duration_seconds,
            ]);
            $request->setUserResolver(fn () => $user);

            /** @var \Illuminate\Http\JsonResponse $response */
            $response = app(TradeController::class)->placeTrade($request);
            $data = $response->getData(true) ?: [];
            $status = $response->getStatusCode();

            if ($status >= 200 && $status < 300 && !empty($data['trade_id'])) {
                return ['ok' => true, 'trade_id' => (int) $data['trade_id']];
            }

            $error = $data['error'] ?? $data['message'] ?? "Trade placement failed (HTTP {$status}).";

            return [
                'ok' => false,
                'error' => $error,
                'insufficient' => $status === 422 && str_contains((string) $error, 'Insufficient balance'),
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage(), 'insufficient' => false];
        }
    }

    private static function tradeSummary(Trade $trade): array
    {
        $trade->loadMissing('asset');
        $openedTs = $trade->opened_at ? $trade->opened_at->timestamp : $trade->created_at->timestamp;
        $duration = (int) ($trade->duration_seconds ?: 60);

        return [
            'trade_id' => $trade->id,
            'asset_id' => $trade->asset_id,
            'symbol' => $trade->asset->symbol ?? '',
            'direction' => $trade->direction,
            'investment' => (float) $trade->investment,
            'payout' => (float) $trade->payout,
            'open_price' => (float) $trade->open_price,
            'duration_seconds' => $duration,
            'expires_at' => ($openedTs + $duration) * 1000,
            'account_type' => $trade->account_type,
            // Fresh post-debit balance (tradeSummary runs after the
            // placement transaction committed), so the page can update
            // the header balance without a reload.
            'balance' => (float) $trade->user->getBalance(),
        ];
    }

    private static function orderSummary(PendingOrder $order): array
    {
        $order->loadMissing('asset');

        return [
            'order_id' => $order->id,
            'order_type' => $order->order_type,
            'symbol' => $order->asset->symbol ?? '',
        ];
    }
}
