<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'prize_pool',
        'entry_fee',
        'rebuy_cost',
        'number_of_rebuys',
        'duration',
        'start_date',
        'end_date',
        'timezone_label',
        'badge_text',
        'status',
        'is_active',
        'description',
        'rules',
        'prize_distribution',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'prize_pool'         => 'decimal:2',
            'entry_fee'          => 'decimal:2',
            'rebuy_cost'         => 'decimal:2',
            'number_of_rebuys'   => 'integer',
            'start_date'         => 'datetime',
            'end_date'           => 'datetime',
            'prize_distribution' => 'array',
            'is_active'          => 'boolean',
            'sort_order'         => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('status', 'active')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc');
    }

    public function getFormattedStartAttribute(): string
    {
        if ($this->start_date) {
            return $this->start_date->format('d M H:i');
        }
        return '16 Sep 05:00';
    }

    public function getFormattedEndAttribute(): string
    {
        if ($this->end_date) {
            return $this->end_date->format('d M H:i');
        }
        return '17 Sep 05:00';
    }

    public function getResolvedPrizeDistributionAttribute(): array
    {
        if (!empty($this->prize_distribution) && is_array($this->prize_distribution)) {
            return $this->prize_distribution;
        }

        $total = (float) $this->prize_pool;
        if ($total <= 0) $total = 7500;

        // Default 5-place distribution percentages
        $rates = [
            '1 Place' => 0.3067, // ~$2300 of $7500
            '2 Place' => 0.2400, // ~$1800 of $7500
            '3 Place' => 0.2000, // ~$1500 of $7500
            '4 Place' => 0.1600, // ~$1200 of $7500
            '5 Place' => 0.0933, // ~$700 of $7500
        ];

        $items = [];
        foreach ($rates as $place => $pct) {
            $amt = round($total * $pct);
            $items[] = [
                'place' => $place,
                'prize' => number_format($amt, 0, '.', '') . '$',
            ];
        }

        return $items;
    }
}
