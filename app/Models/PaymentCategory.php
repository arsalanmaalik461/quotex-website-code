<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'image_path',
        'short_label',
        'description',
        'sort_order',
        'is_active',
        'show_on_deposit',
        'country',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'show_on_deposit' => 'boolean',
        ];
    }

    public function paymentMethods()
    {
        return $this->belongsToMany(PaymentMethod::class, 'payment_category_method')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderBy('payment_category_method.sort_order')
            ->orderBy('payment_methods.sort_order')
            ->orderBy('payment_methods.name');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeShownOnDeposit($query)
    {
        return $query->where('show_on_deposit', true);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        return asset($this->image_path);
    }
}
