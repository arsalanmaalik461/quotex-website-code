<?php

namespace App\Services;

use App\Models\Trade;

/**
 * Live 4/6 Cycle
 *
 * Live-balance trades only: in every block of 10 settled live
 * trades, exactly 4 are wins and 6 are losses, in a random order
 * that is different for every user and every block.
 *
 * This mirrors DemoCycleService exactly in structure (same
 * no-counter, no-reset design), with the win/loss split flipped
 * to 4 wins / 6 losses per block:
 *
 * There is no date, no daily reset and no stored counter to expire:
 * the position is derived from the number of live trades the user
 * has already settled, which lives in the trades table forever.
 * A user who took 3 trades today is on position 4 of the same
 * block when they take their next trade, even 2 years later.
 *
 * The 6 loss positions inside a block are derived from a hash of
 * (user_id, block), so they are fixed for that block, different
 * for every user/block, and never the same pattern twice in a row:
 * the losses can be consecutive, at the start, at the end, or
 * spread out, and the 4 wins can come in one straight run.
 *
 * Priority (unchanged): per-trade admin control > pair control >
 * global mode > auto cycle rule (live 4/6 here, demo 8/2 for demo
 * accounts) > market. An admin press still overrides this rule
 * for that trade: the cycle is only consulted when no admin /
 * pair / global force is set for the trade.
 */
class LiveCycleService
{
    public const BLOCK_SIZE = 10;
    public const WINS_PER_BLOCK = 4;
    public const LOSSES_PER_BLOCK = 6;

    /**
     * How many live trades this user has already settled (win/loss).
     * Ties do not consume a win/loss slot; pending trades do not count.
     */
    public static function settledLiveCount(int $userId): int
    {
        return Trade::where('user_id', $userId)
            ->where('account_type', 'live')
            ->whereIn('result', ['win', 'loss'])
            ->count();
    }

    /**
     * The 6 loss positions (0-9) inside the given block for this user.
     *
     * Positions are drawn one at a time from successive hashes of
     * "live-cycle:{user_id}:{block}:{attempt}", skipping duplicates,
     * until 6 distinct positions are collected. Six distinct draws
     * need more than the two fixed slices DemoCycleService uses, so
     * the attempt counter re-hashes until the block is full; the
     * result is still a pure hash function of (user_id, block).
     */
    public static function lossPositions(int $userId, int $block): array
    {
        $positions = [];
        $attempt = 0;

        while (count($positions) < self::LOSSES_PER_BLOCK) {
            $hash = hash('sha256', "live-cycle:{$userId}:{$block}:{$attempt}");
            $position = hexdec(substr($hash, 0, 8)) % self::BLOCK_SIZE;

            if (!in_array($position, $positions, true)) {
                $positions[] = $position;
            }

            $attempt++;
        }

        sort($positions);

        return $positions;
    }

    /**
     * Is the live trade at this settled-count a loss?
     * $settledCount is how many live trades are already settled
     * before this one, so position 0 is the user's first live trade.
     */
    public static function isLoss(int $userId, int $settledCount): bool
    {
        $block = intdiv($settledCount, self::BLOCK_SIZE);
        $position = $settledCount % self::BLOCK_SIZE;

        return in_array($position, self::lossPositions($userId, $block), true);
    }

    /**
     * Which way must the price move for this live trade?
     * Returns 'up' / 'down' (the forced price side), or null when
     * this is not a live trade.
     */
    public static function forcedSideForTrade(Trade $trade, int $settledCount): ?string
    {
        if ($trade->account_type !== 'live') {
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
