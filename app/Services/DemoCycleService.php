<?php

namespace App\Services;

use App\Models\Trade;

/**
 * Demo 8/2 Cycle
 *
 * Demo-balance trades only: in every block of 10 settled demo
 * trades, exactly 8 are wins and 2 are losses, in a random order
 * that is different for every user and every block.
 *
 * There is no date, no daily reset and no stored counter to expire:
 * the position is derived from the number of demo trades the user
 * has already settled, which lives in the trades table forever.
 * A user who took 3 trades today is on position 4 of the same
 * block when they take their next trade, even 2 years later.
 *
 * The 2 loss positions inside a block are derived from a hash of
 * (user_id, block), so they are fixed for that block, different
 * for every user/block, and never the same pattern twice in a row:
 * the losses can be consecutive, at the start, at the end, or
 * spread out, and the 8 wins can come in one straight run.
 */
class DemoCycleService
{
    public const BLOCK_SIZE = 10;
    public const WINS_PER_BLOCK = 8;
    public const LOSSES_PER_BLOCK = 2;

    /**
     * How many demo trades this user has already settled (win/loss).
     * Ties do not consume a win/loss slot; pending trades do not count.
     */
    public static function settledDemoCount(int $userId): int
    {
        return Trade::where('user_id', $userId)
            ->where('account_type', 'demo')
            ->whereIn('result', ['win', 'loss'])
            ->count();
    }

    /**
     * The 2 loss positions (0-9) inside the given block for this user.
     */
    public static function lossPositions(int $userId, int $block): array
    {
        $hash = hash('sha256', "demo-cycle:{$userId}:{$block}");
        $first = hexdec(substr($hash, 0, 8)) % self::BLOCK_SIZE;
        $second = hexdec(substr($hash, 8, 8)) % self::BLOCK_SIZE;

        if ($second === $first) {
            $second = ($second + 4) % self::BLOCK_SIZE;
        }

        $positions = [$first, $second];
        sort($positions);

        return $positions;
    }

    /**
     * Is the demo trade at this settled-count a loss?
     * $settledCount is how many demo trades are already settled
     * before this one, so position 0 is the user's first demo trade.
     */
    public static function isLoss(int $userId, int $settledCount): bool
    {
        $block = intdiv($settledCount, self::BLOCK_SIZE);
        $position = $settledCount % self::BLOCK_SIZE;

        return in_array($position, self::lossPositions($userId, $block), true);
    }

    /**
     * Which way must the price move for this demo trade?
     * Returns 'up' / 'down' (the forced price side), or null when
     * this is not a demo trade.
     */
    public static function forcedSideForTrade(Trade $trade, int $settledCount): ?string
    {
        if ($trade->account_type !== 'demo') {
            return null;
        }

        $isLoss = self::isLoss((int) $trade->user_id, $settledCount);

        return $isLoss
            ? ($trade->direction === 'up' ? 'down' : 'up')
            : $trade->direction;
    }

    /**
     * Human label for where this trade sits in its block, e.g. "4/10".
     */
    public static function positionLabel(int $settledCount): string
    {
        return (($settledCount % self::BLOCK_SIZE) + 1) . '/' . self::BLOCK_SIZE;
    }
}
