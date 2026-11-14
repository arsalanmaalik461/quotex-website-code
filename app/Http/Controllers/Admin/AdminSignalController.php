<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Asset;
use App\Models\Signal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminSignalController extends Controller
{
    private function ensureTableExists(): void
    {
        if (!Schema::hasTable('signals')) {
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            } catch (\Throwable $e) {
            }
        }
    }

    public function index(Request $request)
    {
        $this->ensureTableExists();

        $query = Signal::with('asset')->latest();

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true)->where('expires_at', '>', now());
            } elseif ($request->status === 'expired') {
                $query->where(function ($q) {
                    $q->where('is_active', false)->orWhere('expires_at', '<=', now());
                });
            }
        }

        if ($request->filled('asset_id')) {
            $query->where('asset_id', $request->asset_id);
        }

        $signals = $query->paginate(20)->withQueryString();

        $totalCount = Signal::count();
        $activeCount = Signal::where('is_active', true)->where('expires_at', '>', now())->count();
        $expiredCount = Signal::where(function ($q) {
            $q->where('is_active', false)->orWhere('expires_at', '<=', now());
        })->count();

        $assets = Asset::where('is_active', true)->orderBy('symbol', 'asc')->get();

        return view('admin.signals.index', compact('signals', 'totalCount', 'activeCount', 'expiredCount', 'assets'));
    }

    public function create()
    {
        $this->ensureTableExists();
        $assets = Asset::where('is_active', true)->orderBy('symbol', 'asc')->get();
        return view('admin.signals.create', compact('assets'));
    }

    public function store(Request $request)
    {
        $this->ensureTableExists();

        $request->validate([
            'asset_id'         => 'required|exists:assets,id',
            'direction'        => 'required|in:call,put',
            'duration_minutes' => 'required|numeric|min:0.5',
            'start_time'       => 'nullable|date',
            'payout_percent'   => 'nullable|numeric|min:1|max:100',
            'notes'            => 'nullable|string|max:500',
        ]);

        $asset = Asset::findOrFail($request->asset_id);
        $durationMinutes = (float) $request->duration_minutes;
        $durationSeconds = (int) round($durationMinutes * 60);

        // Format duration display string e.g. 15:00 or 04:00:00
        if ($durationMinutes >= 60) {
            $hours = floor($durationMinutes / 60);
            $mins = $durationMinutes % 60;
            $durationFormatted = sprintf('%02d:%02d:00', $hours, $mins);
        } else {
            $durationFormatted = sprintf('%02d:00', $durationMinutes);
        }

        $startTime = $request->filled('start_time') ? \Carbon\Carbon::parse($request->start_time) : now();
        $expiresAt = (clone $startTime)->addSeconds($durationSeconds);

        $signal = Signal::create([
            'asset_id'         => $asset->id,
            'pair_name'        => $asset->symbol,
            'direction'        => $request->direction,
            'duration'         => $durationFormatted,
            'duration_seconds' => $durationSeconds,
            'start_time'       => $startTime,
            'expires_at'       => $expiresAt,
            'payout_percent'   => $request->payout_percent ?? $asset->payout_percent ?? 85.00,
            'is_active'        => true,
            'status'           => 'active',
            'notes'            => $request->notes,
        ]);

        AdminLog::record('signal_create', "Created trading signal for {$asset->symbol} ({$request->direction}, {$durationFormatted})");

        return redirect()->route('admin.signals.index')->with('success', "Trading signal for '{$asset->symbol}' created successfully!");
    }

    public function toggle(Signal $signal)
    {
        $signal->is_active = !$signal->is_active;
        if (!$signal->is_active) {
            $signal->status = 'expired';
            $signal->expires_at = now();
        } else {
            $signal->status = 'active';
            $signal->expires_at = now()->addSeconds($signal->duration_seconds ?: 900);
        }
        $signal->save();

        $status = $signal->is_active ? 'activated' : 'deactivated';
        AdminLog::record('signal_toggle', "Signal #{$signal->id} ({$signal->pair_name}) {$status}");

        return back()->with('success', "Signal #{$signal->id} has been {$status}.");
    }

    public function destroy(Signal $signal)
    {
        $pair = $signal->pair_name;
        $signal->delete();

        AdminLog::record('signal_delete', "Deleted signal #{$signal->id} ({$pair})");

        return back()->with('success', "Signal for '{$pair}' deleted successfully.");
    }
}
