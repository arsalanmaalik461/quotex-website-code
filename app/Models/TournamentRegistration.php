<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TournamentRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id',
        'user_id',
        'entry_fee_paid',
        'tournament_balance',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'entry_fee_paid'     => 'decimal:2',
            'tournament_balance' => 'decimal:2',
        ];
    }

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
