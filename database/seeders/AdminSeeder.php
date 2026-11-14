<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = env('ADMIN_EMAIL', 'admin@admin.com');
        $adminPassword = env('ADMIN_PASSWORD') ?: Str::password(32);

        // ── Create Admin User ─────────────────────────────────────
        User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name'         => 'Super Admin',
                'password'     => Hash::make($adminPassword),
                'is_admin'     => true,
                'is_blocked'   => false,
                'demo_balance' => 0,
                'live_balance' => 0,
                'account_type' => 'live',
                'email_verified' => true,
                'referral_code'  => 'ADMIN001',
            ]
        );

        $this->command->info('Admin user created or updated. Configure ADMIN_EMAIL and ADMIN_PASSWORD for deterministic credentials.');

        // ── Seed default Trading Assets ───────────────────────────
        $assets = [
            // Forex
            ['symbol' => 'EUR/USD', 'name' => 'Euro / US Dollar',       'type' => 'forex',     'payout_percent' => 85, 'flag' => '🇪🇺🇺🇸', 'category' => 'Majors'],
            ['symbol' => 'GBP/USD', 'name' => 'British Pound / USD',     'type' => 'forex',     'payout_percent' => 85, 'flag' => '🇬🇧🇺🇸', 'category' => 'Majors'],
            ['symbol' => 'USD/JPY', 'name' => 'US Dollar / Japanese Yen','type' => 'forex',     'payout_percent' => 85, 'flag' => '🇺🇸🇯🇵', 'category' => 'Majors'],
            ['symbol' => 'AUD/CHF', 'name' => 'Australian Dollar / CHF', 'type' => 'forex',     'payout_percent' => 82, 'flag' => '🇦🇺🇨🇭', 'category' => 'Minors'],
            ['symbol' => 'USD/NGN', 'name' => 'US Dollar / Nigerian Naira','type' => 'forex',   'payout_percent' => 80, 'flag' => '🇺🇸🇳🇬', 'category' => 'Exotic'],
            // Crypto
            ['symbol' => 'BTC/USD', 'name' => 'Bitcoin / US Dollar',    'type' => 'crypto',    'payout_percent' => 90, 'flag' => '₿🇺🇸',   'category' => 'Crypto'],
            ['symbol' => 'ETH/USD', 'name' => 'Ethereum / US Dollar',   'type' => 'crypto',    'payout_percent' => 88, 'flag' => '⟠🇺🇸',   'category' => 'Crypto'],
            // Commodity
            ['symbol' => 'XAU/USD', 'name' => 'Gold / US Dollar',       'type' => 'commodity', 'payout_percent' => 87, 'flag' => '🥇🇺🇸',   'category' => 'Metals'],
            // OTC Custom
            ['symbol' => 'AZA/USDT','name' => 'Aza Coin / USDT',        'type' => 'otc',       'payout_percent' => 85, 'flag' => '🪙',      'category' => 'OTC', 'is_otc' => true],
        ];

        foreach ($assets as $data) {
            Asset::updateOrCreate(
                ['symbol' => $data['symbol']],
                array_merge([
                    'is_active'             => true,
                    'is_otc'                => $data['is_otc'] ?? false,
                    'market_volume'         => 1000000,
                    'trading_capital'       => 500000,
                    'max_pump_percent'      => 5.00,
                    'max_crash_percent'     => 5.00,
                    'pump_duration_minutes' => 15,
                    'control_mode'          => 'auto',
                    'win_side'              => 'none',
                ], $data)
            );
        }

        $this->command->info('✓ ' . count($assets) . ' default assets seeded.');
    }
}
