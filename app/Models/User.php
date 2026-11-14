<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'demo_balance', 'live_balance',
        'country', 'account_type', 'email_verified', 'kyc_status',
        'is_admin', 'is_blocked', 'referral_code', 'referred_by', 'phone', 'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'demo_balance' => 'decimal:2',
            'live_balance' => 'decimal:2',
            'is_admin' => 'boolean',
            'is_blocked' => 'boolean',
        ];
    }

    public function trades()
    {
        return $this->hasMany(Trade::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function commissionsEarned()
    {
        return $this->hasMany(ReferralCommission::class, 'referrer_id');
    }

    public function commissionsGenerated()
    {
        return $this->hasMany(ReferralCommission::class, 'referred_user_id');
    }

    public function ensureReferralCode(): string
    {
        if (empty($this->referral_code)) {
            do {
                $code = 'QX' . strtoupper(\Illuminate\Support\Str::random(6));
            } while (User::where('referral_code', $code)->exists());

            $this->referral_code = $code;
            $this->save();
        }
        return $this->referral_code;
    }

    public function getReferralLinkAttribute(): string
    {
        $code = $this->referral_code ?: $this->ensureReferralCode();
        return url('/sign-up?ref=' . $code);
    }

    public function getTotalCommissionEarned(): float
    {
        return round((float) $this->commissionsEarned()->where('status', 'credited')->sum('commission_amount'), 2);
    }

    public function getWinCommissionEarned(): float
    {
        return round((float) $this->commissionsEarned()->where('type', 'trade_win')->where('status', 'credited')->sum('commission_amount'), 2);
    }

    public function getLossCommissionEarned(): float
    {
        return round((float) $this->commissionsEarned()->where('type', 'trade_loss')->where('status', 'credited')->sum('commission_amount'), 2);
    }

    public function getDepositCommissionEarned(): float
    {
        return round((float) $this->commissionsEarned()->where('type', 'deposit')->where('status', 'credited')->sum('commission_amount'), 2);
    }

    public function adminLogs()
    {
        return $this->hasMany(\App\Models\AdminLog::class, 'admin_id');
    }

    public function kycVerifications()
    {
        return $this->hasMany(\App\Models\KycVerification::class);
    }

    public function kyc()
    {
        return $this->hasOne(\App\Models\KycVerification::class)->latestOfMany();
    }

    public function isKycVerified(): bool
    {
        return $this->kyc_status === 'verified' || ($this->kyc && $this->kyc->status === 'approved');
    }

    public function getBalance(): float
    {
        return $this->account_type === 'live'
            ? (float) $this->live_balance
            : (float) $this->demo_balance;
    }

    public function getTotalTrades(): int
    {
        return $this->trades()->count();
    }

    public function getAccountAgeDays(): int
    {
        return (int) $this->created_at->diffInDays(now());
    }

    public function getTotalProfit(): float
    {
        $wins = $this->trades()->where('result', 'win')->sum('payout');
        $invested = $this->trades()->whereIn('result', ['win', 'loss'])->sum('investment');
        return round((float)$wins - (float)$invested, 2);
    }
}
