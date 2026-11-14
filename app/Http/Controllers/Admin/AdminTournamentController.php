<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Tournament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class AdminTournamentController extends Controller
{
    private function ensureTableExists(): void
    {
        if (!Schema::hasTable('tournaments')) {
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            } catch (\Throwable $e) {
            }
        }

        if (Schema::hasTable('tournaments')) {
            // Check missing columns
            Schema::table('tournaments', function (Blueprint $table) {
                if (!Schema::hasColumn('tournaments', 'rebuy_cost')) {
                    $table->decimal('rebuy_cost', 10, 2)->nullable()->default(10.00);
                }
                if (!Schema::hasColumn('tournaments', 'number_of_rebuys')) {
                    $table->integer('number_of_rebuys')->nullable()->default(100);
                }
                if (!Schema::hasColumn('tournaments', 'timezone_label')) {
                    $table->string('timezone_label')->nullable()->default('UTC+05:00');
                }
                if (!Schema::hasColumn('tournaments', 'prize_distribution')) {
                    $table->json('prize_distribution')->nullable();
                }
            });
        }

        if (Schema::hasTable('tournaments') && Tournament::count() === 0) {
            // Seed initial Quotex tournaments with rich details
            Tournament::create([
                'title'              => 'Crazy Wednesday',
                'prize_pool'         => 7500.00,
                'entry_fee'          => 10.00,
                'rebuy_cost'         => 10.00,
                'number_of_rebuys'   => 100,
                'duration'           => '1 day',
                'start_date'         => now()->addDay()->setTime(5, 0),
                'end_date'           => now()->addDays(2)->setTime(5, 0),
                'timezone_label'     => 'UTC+05:00',
                'badge_text'         => 'Until start: 1 day(s)',
                'status'             => 'active',
                'is_active'          => true,
                'sort_order'         => 1,
                'description'        => 'Total prize pool is $7500. To participate you need to register and pay the entrance fee, after that $100 will be deposited to a special tournament account. Initial conditions in the tournament are the same for all participants. During one day, participants need to make trades on the tournament account. And 5 traders with the biggest tournament balance will become winners.',
                'prize_distribution' => [
                    ['place' => '1 Place', 'prize' => '2300$'],
                    ['place' => '2 Place', 'prize' => '1800$'],
                    ['place' => '3 Place', 'prize' => '1500$'],
                    ['place' => '4 Place', 'prize' => '1200$'],
                    ['place' => '5 Place', 'prize' => '700$'],
                ],
            ]);

            Tournament::create([
                'title'              => 'Free Friday',
                'prize_pool'         => 1000.00,
                'entry_fee'          => 0.00,
                'rebuy_cost'         => 1.00,
                'number_of_rebuys'   => 50,
                'duration'           => '1 day',
                'start_date'         => now()->addDays(3)->setTime(5, 0),
                'end_date'           => now()->addDays(4)->setTime(5, 0),
                'timezone_label'     => 'UTC+05:00',
                'badge_text'         => 'Until start: 3 day(s)',
                'status'             => 'active',
                'is_active'          => true,
                'sort_order'         => 2,
                'description'        => 'Free entry tournament with $1,000 guaranteed prize pool for all traders. Test your trading strategies without any risk and win real cash rewards.',
                'prize_distribution' => [
                    ['place' => '1 Place', 'prize' => '350$'],
                    ['place' => '2 Place', 'prize' => '250$'],
                    ['place' => '3 Place', 'prize' => '200$'],
                    ['place' => '4 Place', 'prize' => '120$'],
                    ['place' => '5 Place', 'prize' => '80$'],
                ],
            ]);

            Tournament::create([
                'title'              => 'Weekend Battle',
                'prize_pool'         => 5000.00,
                'entry_fee'          => 1.00,
                'rebuy_cost'         => 1.00,
                'number_of_rebuys'   => 100,
                'duration'           => '2 days',
                'start_date'         => now()->addDays(4)->setTime(5, 0),
                'end_date'           => now()->addDays(6)->setTime(5, 0),
                'timezone_label'     => 'UTC+05:00',
                'badge_text'         => 'Until start: 4 day(s)',
                'status'             => 'active',
                'is_active'          => true,
                'sort_order'         => 3,
                'description'        => 'Weekend 48-hour trading tournament with huge rewards for top participants. Compete against top traders and claim your share of the $5,000 prize pool.',
                'prize_distribution' => [
                    ['place' => '1 Place', 'prize' => '1500$'],
                    ['place' => '2 Place', 'prize' => '1200$'],
                    ['place' => '3 Place', 'prize' => '1000$'],
                    ['place' => '4 Place', 'prize' => '800$'],
                    ['place' => '5 Place', 'prize' => '500$'],
                ],
            ]);

            Tournament::create([
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
                'prize_distribution' => [
                    ['place' => '1 Place', 'prize' => '30000$'],
                    ['place' => '2 Place', 'prize' => '25000$'],
                    ['place' => '3 Place', 'prize' => '20000$'],
                    ['place' => '4 Place', 'prize' => '15000$'],
                    ['place' => '5 Place', 'prize' => '10000$'],
                ],
            ]);
        }
    }

    public function index(Request $request)
    {
        $this->ensureTableExists();

        $query = Tournament::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tournaments = $query->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $totalCount = Tournament::count();
        $activeCount = Tournament::where('status', 'active')->where('is_active', true)->count();
        $completedCount = Tournament::where('status', 'completed')->count();
        $totalPrizePool = Tournament::sum('prize_pool');

        return view('admin.tournaments.index', compact('tournaments', 'totalCount', 'activeCount', 'completedCount', 'totalPrizePool'));
    }

    public function create()
    {
        $this->ensureTableExists();
        return view('admin.tournaments.create');
    }

    public function store(Request $request)
    {
        $this->ensureTableExists();

        $request->validate([
            'title'            => 'required|string|max:255',
            'prize_pool'       => 'required|numeric|min:0',
            'entry_fee'        => 'required|numeric|min:0',
            'rebuy_cost'       => 'nullable|numeric|min:0',
            'number_of_rebuys' => 'nullable|integer|min:0',
            'duration'         => 'required|string|max:100',
            'timezone_label'   => 'nullable|string|max:50',
            'badge_text'       => 'nullable|string|max:100',
            'status'           => 'required|in:active,completed',
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date',
            'description'      => 'nullable|string',
            'rules'            => 'nullable|string',
            'sort_order'       => 'nullable|integer',
        ]);

        $badge = $request->badge_text;
        if (empty($badge) && $request->status === 'active') {
            $badge = 'Until start: 1 day(s)';
        } elseif (empty($badge) && $request->status === 'completed') {
            $badge = 'Completed';
        }

        // Process prize distribution rows
        $prizeDistribution = [];
        if ($request->has('prize_places') && is_array($request->prize_places)) {
            foreach ($request->prize_places as $idx => $place) {
                $prize = $request->prize_amounts[$idx] ?? '';
                if (!empty($place) && !empty($prize)) {
                    $prizeDistribution[] = [
                        'place' => trim($place),
                        'prize' => str_ends_with(trim($prize), '$') ? trim($prize) : trim($prize) . '$',
                    ];
                }
            }
        }

        $tournament = Tournament::create([
            'title'              => $request->title,
            'prize_pool'         => $request->prize_pool,
            'entry_fee'          => $request->entry_fee,
            'rebuy_cost'         => $request->rebuy_cost ?? $request->entry_fee,
            'number_of_rebuys'   => $request->number_of_rebuys ?? 100,
            'duration'           => $request->duration,
            'timezone_label'     => $request->timezone_label ?? 'UTC+05:00',
            'badge_text'         => $badge,
            'status'             => $request->status,
            'start_date'         => $request->start_date,
            'end_date'           => $request->end_date,
            'description'        => $request->description,
            'rules'              => $request->rules,
            'prize_distribution' => count($prizeDistribution) ? $prizeDistribution : null,
            'is_active'          => $request->has('is_active') || $request->status === 'active',
            'sort_order'         => $request->sort_order ?? 0,
        ]);

        if (class_exists(AdminLog::class)) {
            AdminLog::record('tournament_create', "Created tournament: {$tournament->title} (Prize: $" . $tournament->prize_pool . ')');
        }

        return redirect()->route('admin.tournaments.index')->with('success', "Tournament '{$tournament->title}' created successfully!");
    }

    public function edit(Tournament $tournament)
    {
        $this->ensureTableExists();
        return view('admin.tournaments.edit', compact('tournament'));
    }

    public function update(Request $request, Tournament $tournament)
    {
        $this->ensureTableExists();

        $request->validate([
            'title'            => 'required|string|max:255',
            'prize_pool'       => 'required|numeric|min:0',
            'entry_fee'        => 'required|numeric|min:0',
            'rebuy_cost'       => 'nullable|numeric|min:0',
            'number_of_rebuys' => 'nullable|integer|min:0',
            'duration'         => 'required|string|max:100',
            'timezone_label'   => 'nullable|string|max:50',
            'badge_text'       => 'nullable|string|max:100',
            'status'           => 'required|in:active,completed',
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date',
            'description'      => 'nullable|string',
            'rules'            => 'nullable|string',
            'sort_order'       => 'nullable|integer',
        ]);

        $badge = $request->badge_text;
        if (empty($badge) && $request->status === 'active') {
            $badge = 'Until start: 1 day(s)';
        } elseif (empty($badge) && $request->status === 'completed') {
            $badge = 'Completed';
        }

        // Process prize distribution rows
        $prizeDistribution = [];
        if ($request->has('prize_places') && is_array($request->prize_places)) {
            foreach ($request->prize_places as $idx => $place) {
                $prize = $request->prize_amounts[$idx] ?? '';
                if (!empty($place) && !empty($prize)) {
                    $prizeDistribution[] = [
                        'place' => trim($place),
                        'prize' => str_ends_with(trim($prize), '$') ? trim($prize) : trim($prize) . '$',
                    ];
                }
            }
        }

        $tournament->update([
            'title'              => $request->title,
            'prize_pool'         => $request->prize_pool,
            'entry_fee'          => $request->entry_fee,
            'rebuy_cost'         => $request->rebuy_cost ?? $tournament->rebuy_cost,
            'number_of_rebuys'   => $request->number_of_rebuys ?? $tournament->number_of_rebuys,
            'duration'           => $request->duration,
            'timezone_label'     => $request->timezone_label ?? 'UTC+05:00',
            'badge_text'         => $badge,
            'status'             => $request->status,
            'start_date'         => $request->start_date,
            'end_date'           => $request->end_date,
            'description'        => $request->description,
            'rules'              => $request->rules,
            'prize_distribution' => count($prizeDistribution) ? $prizeDistribution : null,
            'is_active'          => $request->has('is_active'),
            'sort_order'         => $request->sort_order ?? 0,
        ]);

        if (class_exists(AdminLog::class)) {
            AdminLog::record('tournament_update', "Updated tournament: {$tournament->title}");
        }

        return redirect()->route('admin.tournaments.index')->with('success', "Tournament '{$tournament->title}' updated successfully!");
    }

    public function toggle(Tournament $tournament)
    {
        $this->ensureTableExists();
        $tournament->update(['is_active' => !$tournament->is_active]);

        $status = $tournament->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Tournament '{$tournament->title}' {$status}!");
    }

    public function destroy(Tournament $tournament)
    {
        $this->ensureTableExists();
        $title = $tournament->title;
        $tournament->delete();

        if (class_exists(AdminLog::class)) {
            AdminLog::record('tournament_delete', "Deleted tournament: {$title}");
        }

        return redirect()->route('admin.tournaments.index')->with('success', "Tournament '{$title}' deleted successfully!");
    }
}
