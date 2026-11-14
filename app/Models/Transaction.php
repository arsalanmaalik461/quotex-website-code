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
    ];

    protected function casts(): array
    {
        return [
            'provider_payload' => 'array',
            'processed_at' => 'datetime',
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
