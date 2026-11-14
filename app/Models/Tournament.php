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

    public function registrations()
    {
        return $this->hasMany(TournamentRegistration::class);
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
        // No stored start date: show nothing fake rather than an
        // invented date.
        return '—';
    }

    public function getFormattedEndAttribute(): string
    {
        if ($this->end_date) {
            return $this->end_date->format('d M H:i');
        }
        return '—';
    }

    /**
     * Badge text recomputed from the real start/end dates and status
     * on every read — the stored badge_text column is no longer
     * trusted (it held stale strings like "Until start: 1 day(s)"
     * that were never recomputed as the dates passed).
     */
    public function getBadgeTextAttribute(): ?string
    {
        if (in_array($this->status, ['completed', 'finished', 'cancelled'], true)) {
            return 'Finished';
        }
        $now = now();
        if ($this->start_date && $now->lt($this->start_date)) {
            $days = (int) $now->diffInDays($this->start_date);
            $days = max($days, 1);
            return 'Until start: ' . $days . ' day(s)';
        }
        if ($this->end_date && $now->gt($this->end_date)) {
            return 'Finished';
        }
        if ($this->end_date) {
            $days = (int) $now->diffInDays($this->end_date);
            return $days > 0 ? 'Ends in: ' . $days . ' day(s)' : 'Ends today';
        }
        if ($this->start_date) {
            return 'Live now';
        }
        return $this->getRawOriginal('badge_text') ?: 'Active';
    }

    public function getResolvedPrizeDistributionAttribute(): array
    {
        if (!empty($this->prize_distribution) && is_array($this->prize_distribution)) {
            return $this->prize_distribution;
        }

        $total = (float) $this->prize_pool;
        if ($total <= 0) $total = 7500;

        // Default 5-place distribution percentages — real Quotex
        // Crazy Wednesday split of the $7,500 pool:
        // $3,000 / $2,000 / $1,000 / $900 / $600.
        $rates = [
            '1 Place' => 0.4000, // $3000 of $7500
            '2 Place' => 0.2667, // $2000 of $7500 (7500*0.2667=2000.25 -> 2000)
            '3 Place' => 0.1333, // $1000 of $7500 (7500*0.1333=999.75 -> 1000)
            '4 Place' => 0.1200, // $900 of $7500
            '5 Place' => 0.0800, // $600 of $7500
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
