<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trade extends Model
{
    protected $fillable = [
        'user_id', 'asset_id', 'direction', 'investment', 'payout',
        'open_price', 'close_price', 'duration_seconds', 'result',
        'account_type', 'opened_at', 'closed_at',
        'admin_override', 'spike_injected',
    ];

    protected $casts = [
        'opened_at'      => 'datetime',
        'closed_at'      => 'datetime',
        'spike_injected' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
