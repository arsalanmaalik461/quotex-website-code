<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetMarketState extends Model
{
    protected $fillable = [
        'asset_id',
        'mode',
        'anchor_price',
        'activated_at',
        'ended_at',
        'version',
    ];

    protected $casts = [
        'anchor_price' => 'decimal:8',
        'activated_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
