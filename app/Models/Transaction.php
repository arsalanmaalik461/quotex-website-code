<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'account_type',
        'type',
        'amount',
        'method',
        'status',
        'notes',
        'proof_image',
        'transaction_id',
        'wallet_address',
        'converted_amount',
        'currency',
        'payment_method_id',
        // P0-3: pending promo bonus for a deposit, credited only on
        // admin approval (see AdminTransactionController).
        'promo_code',
        'promo_bonus_amount',
        'provider',
        'provider_method',
        'merchant_order_id',
        'provider_reference',
        'provider_status',
        'provider_payload',
        'processed_at',
        'gateway_amount',
        'gateway_currency',
        'gateway_conversion_rate',
        // Withdrawal fee (3% from the 3rd same-day withdrawal) and the
        // amount actually sent after the fee. For deposits both are 0.
        'fee_amount',
        'net_amount',
    ];

    protected function casts(): array
    {
        return [
            'provider_payload' => 'array',
            'processed_at' => 'datetime',
            'fee_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'promo_bonus_amount' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function getProofImageUrlAttribute(): ?string
    {
        if (!$this->proof_image) {
            return null;
        }
        if (str_starts_with($this->proof_image, 'http://') || str_starts_with($this->proof_image, 'https://')) {
            return $this->proof_image;
        }
        return asset($this->proof_image);
    }
}
