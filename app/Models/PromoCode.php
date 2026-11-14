<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    use HasFactory;

    protected $table = 'promo_codes';

    protected $fillable = [
        'code',
        'category',
        'reward_type',
        'reward_value',
        'price',
        'min_deposit',
        'expires_at',
        'is_unlimited',
        'is_active',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'reward_value' => 'float',
        'price'        => 'float',
        'min_deposit'  => 'float',
        'expires_at'   => 'datetime',
        'is_unlimited' => 'boolean',
        'is_active'    => 'boolean',
        'sort_order'   => 'integer',
    ];

    /**
     * Purchase price in dollars. Codes created before the price column
     * existed (NULL) are free.
     */
    public function priceAmount(): float
    {
        return (float) ($this->price ?? 0);
    }

    /**
     * The Market catalogue: one or two codes per category, so every card
     * has something on it. The three deposit_bonus codes are the original
     * seed; the other ten cover the five categories the old seed left
     * empty. Seeding is idempotent (firstOrCreate by code) — see
     * AccountController::seedMarketPromoCodes().
     *
     * Prices are seeded defaults the site owner can change later (the
     * admin promo-code form has no price field yet). Balance-bonus codes
     * are priced below their bonus value on purpose (a promo discount);
     * repeat buying of the same code is blocked by the unique
     * (user_id, promo_code_id) on market_purchases, so they cannot be
     * farmed.
     */
    public static function seedDefinitions(): array
    {
        return [
            [
                'code' => 'DEPOSIT50', 'category' => 'deposit_bonus',
                'reward_type' => 'percentage', 'reward_value' => 50.00,
                'price' => 5.00, 'min_deposit' => 50.00,
                'expires_at' => '2030-10-29 23:59:59', 'is_unlimited' => false,
                'is_active' => true, 'sort_order' => 1,
                'description' => 'Receive a 50% extra bonus on your deposit.',
            ],
            [
                'code' => 'WELCOME50', 'category' => 'deposit_bonus',
                'reward_type' => 'percentage', 'reward_value' => 50.00,
                'price' => 0.00, 'min_deposit' => 10.00,
                'expires_at' => null, 'is_unlimited' => true,
                'is_active' => true, 'sort_order' => 2,
                'description' => 'Welcome 50% deposit bonus for all traders.',
            ],
            [
                'code' => 'DEPOSIT40', 'category' => 'deposit_bonus',
                'reward_type' => 'percentage', 'reward_value' => 40.00,
                'price' => 4.00, 'min_deposit' => 30.00,
                'expires_at' => '2030-10-29 23:59:59', 'is_unlimited' => false,
                'is_active' => true, 'sort_order' => 3,
                'description' => 'Receive a 40% extra bonus on your deposit.',
            ],
            [
                'code' => 'RISK10', 'category' => 'risk_free',
                'reward_type' => 'fixed_amount', 'reward_value' => 10.00,
                'price' => 2.00, 'min_deposit' => null,
                'expires_at' => null, 'is_unlimited' => true,
                'is_active' => true, 'sort_order' => 4,
                'description' => 'Risk-free cover for one losing trade of up to $10.',
            ],
            [
                'code' => 'RISK25', 'category' => 'risk_free',
                'reward_type' => 'fixed_amount', 'reward_value' => 25.00,
                'price' => 5.00, 'min_deposit' => null,
                'expires_at' => null, 'is_unlimited' => true,
                'is_active' => true, 'sort_order' => 5,
                'description' => 'Risk-free cover for one losing trade of up to $25.',
            ],
            [
                'code' => 'CASHBACK10', 'category' => 'cashback',
                'reward_type' => 'percentage', 'reward_value' => 10.00,
                'price' => 3.00, 'min_deposit' => null,
                'expires_at' => null, 'is_unlimited' => true,
                'is_active' => true, 'sort_order' => 6,
                'description' => 'Get 10% of your losses back at the end of the bonus period.',
            ],
            [
                'code' => 'CASHBACK20', 'category' => 'cashback',
                'reward_type' => 'percentage', 'reward_value' => 20.00,
                'price' => 6.00, 'min_deposit' => null,
                'expires_at' => null, 'is_unlimited' => true,
                'is_active' => true, 'sort_order' => 7,
                'description' => 'Get 20% of your losses back at the end of the bonus period.',
            ],
            [
                'code' => 'TURNOVER5', 'category' => 'turnover_percent',
                'reward_type' => 'percentage', 'reward_value' => 5.00,
                'price' => 2.00, 'min_deposit' => null,
                'expires_at' => null, 'is_unlimited' => true,
                'is_active' => true, 'sort_order' => 8,
                'description' => 'Get 5% of your total trade turnover credited after the bonus expires.',
            ],
            [
                'code' => 'TURNOVER10', 'category' => 'turnover_percent',
                'reward_type' => 'percentage', 'reward_value' => 10.00,
                'price' => 4.00, 'min_deposit' => null,
                'expires_at' => null, 'is_unlimited' => true,
                'is_active' => true, 'sort_order' => 9,
                'description' => 'Get 10% of your total trade turnover credited after the bonus expires.',
            ],
            [
                'code' => 'BALANCE10', 'category' => 'balance_bonus',
                'reward_type' => 'fixed_amount', 'reward_value' => 10.00,
                'price' => 8.00, 'min_deposit' => null,
                'expires_at' => null, 'is_unlimited' => true,
                'is_active' => true, 'sort_order' => 10,
                'description' => 'Instantly get $10 bonus funds to your real balance.',
            ],
            [
                'code' => 'BALANCE25', 'category' => 'balance_bonus',
                'reward_type' => 'fixed_amount', 'reward_value' => 25.00,
                'price' => 20.00, 'min_deposit' => null,
                'expires_at' => null, 'is_unlimited' => true,
                'is_active' => true, 'sort_order' => 11,
                'description' => 'Instantly get $25 bonus funds to your real balance.',
            ],
            [
                'code' => 'CANCEL5', 'category' => 'cancel_points',
                'reward_type' => 'points', 'reward_value' => 5.00,
                'price' => 2.00, 'min_deposit' => null,
                'expires_at' => null, 'is_unlimited' => true,
                'is_active' => true, 'sort_order' => 12,
                'description' => 'Losing trades within 5 points are canceled automatically.',
            ],
            [
                'code' => 'CANCEL10', 'category' => 'cancel_points',
                'reward_type' => 'points', 'reward_value' => 10.00,
                'price' => 4.00, 'min_deposit' => null,
                'expires_at' => null, 'is_unlimited' => true,
                'is_active' => true, 'sort_order' => 13,
                'description' => 'Losing trades within 10 points are canceled automatically.',
            ],
        ];
    }

    /**
     * What buying this code actually grants, from the item's own data.
     * Shown in the purchase confirm modal BEFORE the user pays, and
     * stored on the MarketPurchase row afterwards.
     *
     * Only the balance_bonus / fixed_amount effect can be granted from
     * the data alone (its tooltip promises instant bonus funds). A
     * deposit bonus is credited by the existing deposit flow when the
     * user deposits with the code. The other four categories promise
     * trade/bonus-period effects the trading engine does not implement
     * (no risk-free settlement, no cashback settlement, no turnover
     * accounting, no cancel-points logic anywhere in the codebase), so
     * for those the purchase is recorded on the account and the modal
     * says plainly that nothing is applied automatically yet.
     */
    public function grantDescription(): string
    {
        $val = rtrim(rtrim(number_format((float) $this->reward_value, 2, '.', ''), '0'), '.');

        if ($this->category === 'balance_bonus' && $this->reward_type === 'fixed_amount') {
            return 'A bonus of $' . number_format((float) $this->reward_value, 2)
                . ' is credited to your live balance immediately — no deposit needed.';
        }

        if ($this->category === 'balance_bonus') {
            return 'Percentage balance bonuses have no defined base amount, so no bonus is credited at purchase. The code is recorded on your account.';
        }

        if ($this->category === 'deposit_bonus') {
            $text = 'This code is added to your account. The ' . $val . '% bonus is credited when you deposit with this code';
            if ($this->min_deposit !== null) {
                $text .= ' (minimum deposit $' . number_format((float) $this->min_deposit, 2) . ')';
            }
            return $text . '.';
        }

        if ($this->category === 'risk_free') {
            return 'This risk-free bonus is recorded on your account. Automatic cover for a losing trade is not built into the trading engine yet, so nothing is applied to your trades automatically.';
        }

        if ($this->category === 'cashback') {
            return 'This cashback bonus is recorded on your account. Automatic cashback settlement is not built into the platform yet, so nothing is credited automatically.';
        }

        if ($this->category === 'turnover_percent') {
            return 'This turnover bonus is recorded on your account. Automatic turnover settlement is not built into the platform yet, so nothing is credited automatically.';
        }

        if ($this->category === 'cancel_points') {
            return 'This cancel-points bonus is recorded on your account. Automatic cancellation of losing trades is not built into the trading engine yet, so nothing is applied to your trades automatically.';
        }

        return 'This bonus is recorded on your account.';
    }

    public static function categories(): array
    {
        return [
            'risk_free'        => 'Risk Free',
            'cashback'         => 'Cashback',
            'deposit_bonus'    => 'Deposit Bonus',
            'turnover_percent' => 'Percentage of turnover',
            'balance_bonus'    => 'Balance Bonus',
            'cancel_points'    => 'Cancel X points',
        ];
    }

    public static function categoryBadges(): array
    {
        return [
            'risk_free'        => 'blue',
            'cashback'         => 'purple',
            'deposit_bonus'    => 'orange',
            'turnover_percent' => 'pink',
            'balance_bonus'    => 'sky',
            'cancel_points'    => 'teal',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->where('is_unlimited', true)
                  ->orWhereNull('expires_at')
                  ->orWhere('expires_at', '>=', now());
            })
            ->orderBy('sort_order')
            ->orderBy('id', 'desc');
    }

    public function scopeForCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function getFormattedStatusAttribute(): string
    {
        if ($this->is_unlimited || !$this->expires_at) {
            return 'Unlimited';
        }
        return $this->expires_at->format('d/m/Y');
    }

    public function getFormattedNameAttribute(): string
    {
        $val = rtrim(rtrim(number_format($this->reward_value, 2, '.', ''), '0'), '.');
        if ($this->reward_type === 'percentage') {
            return "{$this->code} ({$val}%)";
        } elseif ($this->reward_type === 'fixed_amount') {
            return "{$this->code} ($" . $val . ')';
        } elseif ($this->reward_type === 'points') {
            return "{$this->code} ({$val} pts)";
        }
        return "{$this->code} ({$val})";
    }
}
