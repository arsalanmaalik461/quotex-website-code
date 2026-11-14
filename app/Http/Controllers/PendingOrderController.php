<?php

namespace App\Http\Controllers;

use App\Models\PendingOrder;
use App\Services\PendingOrderExecutor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PendingOrderController extends Controller
{
    /**
     * List the authenticated user's still-pending orders (newest first).
     * Used by the trade page to render the "Pending orders" list.
     */
    public function index(Request $request)
    {
        $orders = PendingOrder::with('asset')
            ->where('user_id', Auth::id())
            ->pending()
            ->latest()
            ->get()
            ->map(fn (PendingOrder $order) => $this->orderPayload($order));

        return response()->json(['orders' => $orders]);
    }

    /**
     * Create a real pending order. This only records the intent — it does
     * NOT place a trade and does NOT touch the balance. The balance is
     * debited when (and if) the trigger fires, via PendingOrderExecutor,
     * through the same TradeController::placeTrade path a normal trade uses.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'direction' => 'required|in:up,down',
            'investment' => 'required|numeric|min:1|max:1000',
            'duration_seconds' => 'required|integer|min:5|max:86400',
            'order_type' => 'required|in:price,time',
            'trigger_price' => 'required_if:order_type,price|nullable|numeric|gt:0',
            'trigger_time' => 'required_if:order_type,time|nullable|date|after:now',
        ]);

        $user = Auth::user();

        $order = PendingOrder::create([
            'user_id' => $user->id,
            'asset_id' => $validated['asset_id'],
            'direction' => $validated['direction'],
            'investment' => round((float) $validated['investment'], 2),
            'duration_seconds' => (int) $validated['duration_seconds'],
            'order_type' => $validated['order_type'],
            'trigger_price' => $validated['order_type'] === 'price'
                ? (float) $validated['trigger_price']
                : null,
            'trigger_time' => $validated['order_type'] === 'time'
                ? $validated['trigger_time']
                : null,
            // The order belongs to the account it was created on; never
            // trust a client-sent account_type.
            'account_type' => $user->account_type,
            'status' => PendingOrder::STATUS_PENDING,
        ]);

        $order->load('asset');

        return response()->json([
            'success' => true,
            'order' => $this->orderPayload($order),
        ], 201);
    }

    /**
     * Cancel one of the user's own pending orders.
     */
    public function cancel(PendingOrder $pendingOrder)
    {
        if ($pendingOrder->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        if (!$pendingOrder->isPending()) {
            return response()->json(['error' => 'Only a pending order can be cancelled.'], 409);
        }

        $pendingOrder->update(['status' => PendingOrder::STATUS_CANCELLED]);

        return response()->json(['success' => true]);
    }

    /**
     * Opportunistic execution hook: execute this user's due pending orders
     * right now, without waiting for the `pending:execute` cron.
     * The trade page calls this every few seconds while pending orders
     * exist (and the cron covers the user being away from the page).
     * Returns the executed trades so the page can show them immediately.
     */
    public function checkDue(Request $request)
    {
        $result = PendingOrderExecutor::executeDueForUser(Auth::user());

        return response()->json([
            'executed' => $result['executed'],
            'expired' => $result['expired'],
            'failed' => $result['failed'],
        ]);
    }

    private function orderPayload(PendingOrder $order): array
    {
        return [
            'id' => $order->id,
            'asset_id' => $order->asset_id,
            'symbol' => $order->asset->symbol ?? '',
            'direction' => $order->direction,
            'investment' => (float) $order->investment,
            'duration_seconds' => (int) $order->duration_seconds,
            'order_type' => $order->order_type,
            'trigger_price' => $order->trigger_price !== null ? (float) $order->trigger_price : null,
            'trigger_time' => $order->trigger_time?->toIso8601String(),
            'account_type' => $order->account_type,
            'status' => $order->status,
            'created_at' => $order->created_at?->toIso8601String(),
        ];
    }
}
