<?php

namespace App\Console\Commands;

use App\Models\Trade;
use App\Services\TradeResultService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SettleExpiredTrades extends Command
{
    protected $signature = 'trades:settle-expired {--limit=100 : Maximum expired pending trades to settle per run}';

    protected $description = 'Settle expired pending trades in bounded batches.';

    public function handle(): int
    {
        $limit = max(1, min(500, (int) $this->option('limit')));
        $processed = 0;
        $failed = 0;

        Trade::with('asset')
            ->where('result', 'pending')
            ->orderBy('opened_at')
            ->orderBy('id')
            ->limit($limit * 3)
            ->get()
            ->each(function (Trade $trade) use (&$processed, &$failed, $limit) {
                if ($processed >= $limit || !$this->isExpired($trade)) {
                    return;
                }

                try {
                    TradeResultService::settle($trade, null);
                    $processed++;
                } catch (\Throwable $e) {
                    $failed++;
                    Log::error('Expired trade recovery failed.', [
                        'trade_id' => $trade->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            });

        $this->info("Expired trade recovery complete. processed={$processed}, failed={$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function isExpired(Trade $trade): bool
    {
        $openedAt = $trade->opened_at ?: $trade->created_at;
        $duration = (int) ($trade->duration_seconds ?: 60);

        return $openedAt && now()->greaterThanOrEqualTo($openedAt->copy()->addSeconds($duration));
    }
}
