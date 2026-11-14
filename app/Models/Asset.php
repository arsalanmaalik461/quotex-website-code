<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = [
        'symbol', 'code', 'name', 'payout_percent', 'digits', 'type', 'is_otc', 'is_active', 'flag',
        'market_volume', 'trading_capital', 'max_pump_percent', 'max_crash_percent',
        'pump_duration_minutes', 'control_mode', 'control_expires_at',
        'control_anchor_price', 'control_activated_at', 'control_version',
        'win_side', 'base_price_override', 'category',
    ];

    protected $casts = [
        'is_otc'              => 'boolean',
        'is_active'           => 'boolean',
        'market_volume'       => 'decimal:2',
        'trading_capital'     => 'decimal:2',
        'max_pump_percent'    => 'decimal:2',
        'max_crash_percent'   => 'decimal:2',
        'base_price_override' => 'decimal:8',
        'control_expires_at'  => 'datetime',
        'control_anchor_price' => 'decimal:8',
        'control_activated_at' => 'datetime',
        'control_version'      => 'integer',
    ];

    public function trades()
    {
        return $this->hasMany(Trade::class);
    }

    public function marketStates()
    {
        return $this->hasMany(AssetMarketState::class);
    }
}
