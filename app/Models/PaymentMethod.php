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
