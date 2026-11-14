<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralSetting extends Model
{
    protected $table = 'referral_settings';

    protected $fillable = [
        'is_enabled',
        'join_commission_amount',
        'deposit_commission_percent',
        'trade_win_commission_percent',
        'trade_loss_commission_percent',
        'min_trade_amount',
        'auto_credit_to_balance',
        'terms_and_conditions',
    ];

    protected $casts = [
        'is_enabled'                     => 'boolean',
        'join_commission_amount'         => 'decimal:2',
        'deposit_commission_percent'     => 'decimal:2',
        'trade_win_commission_percent'   => 'decimal:2',
        'trade_loss_commission_percent'  => 'decimal:2',
        'min_trade_amount'               => 'decimal:2',
        'auto_credit_to_balance'         => 'boolean',
    ];

    /**
     * Get or create the singleton referral settings instance.
     */
    public static function getSettings(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'is_enabled'                    => true,
                'join_commission_amount'        => 0.00,
                'deposit_commission_percent'    => 5.00,
                'trade_win_commission_percent'  => 3.00,
                'trade_loss_commission_percent' => 10.00,
                'min_trade_amount'              => 1.00,
                'auto_credit_to_balance'        => true,
                'terms_and_conditions'          => 'Affiliate commissions are credited on active trades and verified deposits.',
            ]
        );
    }
}
