<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalControl extends Model
{
    public const MODE_NORMAL = 'normal';
    public const MODE_TRADING_PAUSED = 'trading_paused';

    public const SCOPE_ALL = 'all';
    public const SCOPE_ENTRY = 'entry';
    public const SCOPE_MARKET = 'market';
    public const SCOPE_SETTLEMENT = 'settlement';

    protected $fillable = [
        'enabled',
        'mode',
        'scope',
        'asset_ids',
        'starts_at',
        'expires_at',
        'version',
        'created_by',
        'updated_by',
        'reason',
        'correlation_id',
        'disabled_at',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'asset_ids' => 'array',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'disabled_at' => 'datetime',
        'version' => 'integer',
    ];
}
