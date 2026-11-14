<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Signal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SignalController extends Controller
{
    public function getSignals()
    {
        if (!Schema::hasTable('signals')) {
            return response()->json([
                'active' => [],
                'past'   => [],
            ]);
        }

        $now = now();

        $activeSignals = Signal::with('asset')
            ->where('is_active', true)
            ->where('expires_at', '>', $now)
            ->orderBy('created_at', 'desc')
            ->get();

        $pastSignals = Signal::with('asset')
            ->where(function ($q) use ($now) {
                $q->where('is_active', false)
                  ->orWhere('expires_at', '<=', $now);
            })
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $formatSignal = function ($sig) {
            $created = $sig->created_at ?: now();
            return [
                'id'                   => $sig->id,
                'asset_id'             => $sig->asset_id,
                'pair_name'            => $sig->pair_name,
                'direction'            => strtolower($sig->direction),
                'duration'             => $sig->duration,
                'duration_seconds'     => $sig->duration_seconds,
                'time_formatted'       => $created->format('d.m H:i'),
                'expires_at_timestamp' => $sig->expires_at ? $sig->expires_at->timestamp * 1000 : null,
                'is_expired'           => $sig->isExpired(),
                'payout_percent'       => (float) $sig->payout_percent,
                'notes'                => $sig->notes,
            ];
        };

        return response()->json([
            'active' => $activeSignals->map($formatSignal),
            'past'   => $pastSignals->map($formatSignal),
            'server_time' => now()->timestamp * 1000,
        ]);
    }
}
