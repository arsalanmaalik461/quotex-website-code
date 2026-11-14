<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Team E (tournaments) P1/P2 parity, 2026-11-14.
 *
 * 1. "Grand 100K Championship" was an invented tournament (not a real
 *    Quotex tournament). The seeder in AdminTournamentController no
 *    longer creates it; this migration deletes the row from databases
 *    that were already seeded with it. Its registrations are deleted
 *    first (explicitly, before the tournament row — the FK also has
 *    cascadeOnDelete, so this is belt-and-braces and cannot error on
 *    orphaned registrations).
 * 2. Crazy Wednesday's stored prize_distribution still holds the old
 *    split ($2,300/$1,800/$1,500/$1,200/$700). The code default was
 *    fixed to the real Quotex split, but the model accessor returns
 *    the stored JSON when present, so existing rows are corrected
 *    here to $3,000/$2,000/$1,000/$900/$600 (sums to the $7,500 pool).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tournaments')) {
            return;
        }

        $grandIds = DB::table('tournaments')
            ->where('title', 'Grand 100K Championship')
            ->pluck('id');

        if ($grandIds->isNotEmpty() && Schema::hasTable('tournament_registrations')) {
            DB::table('tournament_registrations')
                ->whereIn('tournament_id', $grandIds)
                ->delete();
        }

        DB::table('tournaments')
            ->where('title', 'Grand 100K Championship')
            ->delete();

        DB::table('tournaments')
            ->where('title', 'Crazy Wednesday')
            ->update([
                'prize_distribution' => json_encode([
                    ['place' => '1 Place', 'prize' => '3000$'],
                    ['place' => '2 Place', 'prize' => '2000$'],
                    ['place' => '3 Place', 'prize' => '1000$'],
                    ['place' => '4 Place', 'prize' => '900$'],
                    ['place' => '5 Place', 'prize' => '600$'],
                ]),
            ]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('tournaments')) {
            return;
        }

        // Best-effort reversal: re-insert the removed tournament row
        // (its registrations cannot be restored — they were deleted)
        // and restore the previous Crazy Wednesday split.
        if (DB::table('tournaments')->where('title', 'Grand 100K Championship')->doesntExist()) {
            DB::table('tournaments')->insert([
                'title'              => 'Grand 100K Championship',
                'prize_pool'         => 100000.00,
                'entry_fee'          => 50.00,
                'rebuy_cost'         => 50.00,
                'number_of_rebuys'   => 200,
                'duration'           => '30 days',
                'start_date'         => now()->subDays(30)->setTime(5, 0),
                'end_date'           => now()->subDay()->setTime(5, 0),
                'timezone_label'     => 'UTC+05:00',
                'badge_text'         => 'Completed',
                'status'             => 'completed',
                'is_active'          => false,
                'sort_order'         => 4,
                'description'        => 'Annual grand championship for professional binary traders with massive $100,000 prize pool distribution.',
                'prize_distribution' => json_encode([
                    ['place' => '1 Place', 'prize' => '30000$'],
                    ['place' => '2 Place', 'prize' => '25000$'],
                    ['place' => '3 Place', 'prize' => '20000$'],
                    ['place' => '4 Place', 'prize' => '15000$'],
                    ['place' => '5 Place', 'prize' => '10000$'],
                ]),
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }

        DB::table('tournaments')
            ->where('title', 'Crazy Wednesday')
            ->update([
                'prize_distribution' => json_encode([
                    ['place' => '1 Place', 'prize' => '2300$'],
                    ['place' => '2 Place', 'prize' => '1800$'],
                    ['place' => '3 Place', 'prize' => '1500$'],
                    ['place' => '4 Place', 'prize' => '1200$'],
                    ['place' => '5 Place', 'prize' => '700$'],
                ]),
            ]);
    }
};
