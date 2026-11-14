<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralCommission extends Model
{
    protected $table = 'referral_commissions';

    protected $fillable = [
        'referrer_id',
        'referred_user_id',
        'type',
        'trade_id',
        'transaction_id',
        'base_amount',
        'commission_percent',
        'commission_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'base_amount'        => 'decimal:2',
        'commission_percent' => 'decimal:2',
        'commission_amount'  => 'decimal:2',
    ];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function trade()
    {
        return $this->belongsTo(Trade::class, 'trade_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function getFormattedTypeAttribute(): string
    {
        return match ($this->type) {
            'trade_win'  => 'Trade Win Profit Share',
            'trade_loss' => 'Trade Loss RevShare',
            'deposit'    => 'Deposit Commission',
            'join'       => 'Registration Bonus',
            default      => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }
}
