<?php

namespace App\Console\Commands;

use App\Services\PendingOrderExecutor;
use Illuminate\Console\Command;

class ExecutePendingOrders extends Command
{
    protected $signature = 'pending:execute {--limit=100 : Maximum due pending orders to process per run}';

    protected $description = 'Execute due pending orders (By Price / By Time) as real trades.';

    public function handle(): int
    {
        $limit = max(1, min(500, (int) $this->option('limit')));

        // NOTE: a host cron must run this every minute — execution otherwise
        // only happens while a user has the trade page open (the page calls
        // POST /pending-orders/check-due every few seconds while pending
        // orders exist). Cron line, run from the project root:
        //   * * * * * cd /path/to/quotex-website-code && php artisan pending:execute --limit=100
        // (The Laravel scheduler entry in routes/console.php covers it too
        // wherever `artisan schedule:run` already runs every minute.)
        $result = PendingOrderExecutor::executeDueAll($limit);

        $this->info(sprintf(
            'Pending order execution complete. executed=%d, expired=%d, failed=%d',
            count($result['executed']),
            count($result['expired']),
            $result['failed']
        ));

        return $result['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
