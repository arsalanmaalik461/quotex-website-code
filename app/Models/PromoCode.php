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
        'min_deposit',
        'expires_at',
        'is_unlimited',
        'is_active',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'reward_value' => 'float',
        'min_deposit'  => 'float',
        'expires_at'   => 'datetime',
        'is_unlimited' => 'boolean',
        'is_active'    => 'boolean',
        'sort_order'   => 'integer',
    ];

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
