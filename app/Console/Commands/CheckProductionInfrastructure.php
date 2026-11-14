<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CheckProductionInfrastructure extends Command
{
    protected $signature = 'app:check-production-infrastructure';

    protected $description = 'Check PHP extensions and database-backed session, cache, queue, and admin log infrastructure.';

    public function handle(): int
    {
        $this->components->info('PHP extensions');
        foreach (['PDO', 'pdo_mysql', 'mbstring', 'openssl', 'curl'] as $extension) {
            $this->line(sprintf(
                '%-12s %s',
                $extension . ':',
                extension_loaded($extension) ? '<fg=green>loaded</>' : '<fg=red>missing</>'
            ));
        }

        $this->newLine();
        $this->components->info('Database connection');
        try {
            DB::connection()->getPdo();
            $this->line('<fg=green>database: connected</>');
        } catch (\Throwable $e) {
            $this->line('<fg=red>database: failed</> ' . $this->cleanMessage($e->getMessage()));
            return self::FAILURE;
        }

        $this->newLine();
        $this->components->info('Required tables');
        $requiredTables = [
            'sessions',
            'cache',
            'cache_locks',
            'admin_logs',
            'jobs',
            'failed_jobs',
        ];

        $missing = false;
        foreach ($requiredTables as $table) {
            try {
                $exists = Schema::hasTable($table);
                $missing = $missing || !$exists;
                $this->line(sprintf(
                    '%-12s %s',
                    $table . ':',
                    $exists ? '<fg=green>exists</>' : '<fg=red>missing</>'
                ));
            } catch (\Throwable $e) {
                $missing = true;
                $this->line(sprintf('%-12s <fg=red>check failed</> %s', $table . ':', $this->cleanMessage($e->getMessage())));
            }
        }

        $this->newLine();
        $this->components->info('Database cache write/read');
        try {
            $key = 'infra_check_' . Str::random(12);
            Cache::put($key, 'ok', 60);
            $ok = Cache::get($key) === 'ok';
            Cache::forget($key);
            $this->line($ok ? '<fg=green>cache: write/read ok</>' : '<fg=red>cache: write/read failed</>');
            $missing = $missing || !$ok;
        } catch (\Throwable $e) {
            $missing = true;
            $this->line('<fg=red>cache: failed</> ' . $this->cleanMessage($e->getMessage()));
        }

        $this->newLine();
        $this->line('session_driver: ' . config('session.driver'));
        $this->line('cache_store: ' . config('cache.default'));
        $this->line('queue_connection: ' . config('queue.default'));

        return $missing ? self::FAILURE : self::SUCCESS;
    }

    private function cleanMessage(string $message): string
    {
        return preg_replace('/password=([^;\\s]+)/i', 'password=***', $message) ?? $message;
    }
}
