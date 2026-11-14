<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Signal extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'pair_name',
        'direction',
        'duration',
        'duration_seconds',
        'start_time',
        'expires_at',
        'payout_percent',
        'is_active',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active'        => 'boolean',
            'duration_seconds' => 'integer',
            'start_time'       => 'datetime',
            'expires_at'       => 'datetime',
            'payout_percent'   => 'decimal:2',
        ];
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function isExpired(): bool
    {
        if ($this->expires_at) {
            return $this->expires_at->isPast();
        }
        return false;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    public function scopePast($query)
    {
        return $query->where(function ($q) {
            $q->where('is_active', false)
              ->orWhere('expires_at', '<=', now());
        });
    }
}
