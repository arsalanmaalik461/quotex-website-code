<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketPurchase extends Model
{
    protected $table = 'market_purchases';

    protected $fillable = [
        'user_id',
        'promo_code_id',
        'price_paid',
        'idempotency_key',
        'status',
        'granted_summary',
    ];

    protected $casts = [
        'price_paid' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }
}
