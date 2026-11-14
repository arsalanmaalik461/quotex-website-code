<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'provider',
        'provider_code',
        'name',
        'icon',
        'logo_path',
        'trade_type',
        'currency',
        'conversion_rate',
        'wallet_address',
        'description',
        'min_amount',
        'max_amount',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'conversion_rate' => 'decimal:4',
            'min_amount'      => 'decimal:2',
            'max_amount'      => 'decimal:2',
            'is_active'       => 'boolean',
            'sort_order'      => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order', 'asc');
    }

    public function scopeDeposits($query)
    {
        return $query->whereIn('type', ['deposit', 'both']);
    }

    public function scopeWithdrawals($query)
    {
        return $query->whereIn('type', ['withdrawal', 'both']);
    }

    /**
     * Per-method minimums (real Quotex rules, also stated in the
     * withdrawal-page FAQ): $10 for most methods, $50 for major
     * crypto methods. The enforced minimum is the highest of the
     * admin-configured min_amount column and that floor — so an
     * admin-set $100 minimum still stands, while a crypto row
     * seeded with min_amount $10 (e.g. USDT TRC-20) still enforces
     * the $50 crypto floor instead of silently accepting $30.
     */
    public const DEFAULT_MIN_AMOUNT = 10.0;
    public const CRYPTO_MIN_AMOUNT = 50.0;

    /** Crypto-ish method? Matched on name / provider code / icon / currency. */
    public function isCrypto(): bool
    {
        $haystack = strtolower(implode(' ', array_filter([
            (string) $this->name,
            (string) $this->provider_code,
            (string) $this->icon,
            (string) $this->currency,
        ])));

        foreach (['crypto', 'btc', 'bitcoin', 'eth', 'ethereum', 'usdt', 'usdc', 'usdt_', 'trc', 'erc', 'bep', 'ltc', 'litecoin', 'xrp', 'ripple', 'doge', 'sol', 'solana', 'trx', 'tron', 'bnb', 'binance', 'dai', 'ton', 'matic', 'polygon', 'bch', 'dash', 'shib'] as $keyword) {
            if (str_contains($haystack, $keyword)) {
                return true;
            }
        }

        return in_array(strtoupper((string) $this->currency), ['BTC', 'ETH', 'USDT', 'USDC', 'LTC', 'XRP', 'DOGE', 'SOL', 'TRX', 'BNB', 'DAI', 'TON'], true);
    }

    /** The minimum deposit/withdrawal for this method, in USD. */
    public function effectiveMinAmount(): float
    {
        $floor = $this->isCrypto() ? self::CRYPTO_MIN_AMOUNT : self::DEFAULT_MIN_AMOUNT;

        return max((float) ($this->min_amount ?? 0), $floor);
    }

    public function getEffectiveMinAmountAttribute(): float
    {
        return $this->effectiveMinAmount();
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function categories()
    {
        return $this->belongsToMany(PaymentCategory::class, 'payment_category_method')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo_path) {
            return null;
        }

        if (str_starts_with($this->logo_path, 'http://') || str_starts_with($this->logo_path, 'https://')) {
            return $this->logo_path;
        }

        return asset($this->logo_path);
    }
}
