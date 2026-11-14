<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetAdminPassword extends Command
{
    protected $signature = 'admin:reset-password {email} {password}';

    protected $description = 'Create or repair an admin account password safely.';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));
        $password = (string) $this->argument('password');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Please provide a valid email address.');
            return self::FAILURE;
        }

        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.');
            return self::FAILURE;
        }

        $user = User::firstOrNew(['email' => $email]);
        $user->name = $user->name ?: 'Super Admin';
        $user->password = Hash::make($password);
        $user->is_admin = true;
        $user->is_blocked = false;
        $user->email_verified = true;
        $user->account_type = $user->account_type ?: 'live';
        $user->demo_balance = $user->demo_balance ?? 0;
        $user->live_balance = $user->live_balance ?? 0;
        $user->save();

        $this->info("Admin credentials repaired for {$email}.");
        return self::SUCCESS;
    }
}
