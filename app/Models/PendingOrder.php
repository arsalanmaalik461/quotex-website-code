<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingOrder extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_EXECUTED = 'executed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED = 'expired';

    public const TYPE_PRICE = 'price';
    public const TYPE_TIME = 'time';

    protected $fillable = [
        'user_id', 'asset_id', 'direction', 'investment', 'duration_seconds',
        'order_type', 'trigger_price', 'trigger_time', 'account_type',
        'status', 'trade_id',
    ];

    protected $casts = [
        'investment' => 'decimal:2',
        'trigger_price' => 'decimal:8',
        'trigger_time' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function trade()
    {
        return $this->belongsTo(Trade::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * By Price trigger, in the trade-direction sense:
     * an UP (Buy) order fires when the price reaches or rises above the
     * trigger; a DOWN (Sell) order fires when it reaches or falls below it.
     */
    public function isPriceTriggered(float $currentPrice): bool
    {
        if ($this->order_type !== self::TYPE_PRICE || $this->trigger_price === null) {
            return false;
        }

        $trigger = (float) $this->trigger_price;

        return $this->direction === 'up'
            ? $currentPrice >= $trigger
            : $currentPrice <= $trigger;
    }

    /**
     * By Time trigger: the moment has been reached.
     * Used together with the executor's grace window — an order whose
     * trigger passed long ago (e.g. the server was down) is expired,
     * never executed late.
     */
    public function isTimeReached(?\DateTimeInterface $now = null): bool
    {
        if ($this->order_type !== self::TYPE_TIME || $this->trigger_time === null) {
            return false;
        }

        $now = $now ? \Carbon\Carbon::instance($now) : now();

        return $now->greaterThanOrEqualTo($this->trigger_time);
    }
}
