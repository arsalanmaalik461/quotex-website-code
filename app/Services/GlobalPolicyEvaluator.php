<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\GlobalControl;
use App\Models\Trade;
use Illuminate\Support\Facades\Schema;

class GlobalPolicyEvaluator
{
    public function activeControl(?Asset $asset = null, ?string $scope = null): ?GlobalControl
    {
        if (!Schema::hasTable('global_controls')) {
            return null;
        }

        $now = now();

        return GlobalControl::query()
            ->where('enabled', true)
            ->where(function ($query) use ($now) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', $now);
            })
            ->latest('id')
            ->get()
            ->first(function (GlobalControl $control) use ($asset, $scope) {
                return $this->scopeApplies($control, $scope) && $this->assetApplies($control, $asset);
            });
    }

    public function frontendStatus(?Asset $asset = null): string
    {
        $control = $this->activeControl($asset);

        if (!$control || $control->mode === GlobalControl::MODE_NORMAL) {
            return 'NORMAL';
        }

        if ($control->mode === GlobalControl::MODE_TRADING_PAUSED) {
            return 'TRADING_PAUSED';
        }

        return strtoupper($control->mode);
    }

    public function entryAllowed(Asset $asset): array
    {
        $control = $this->activeControl($asset, GlobalControl::SCOPE_ENTRY);

        if (!$control || $control->mode === GlobalControl::MODE_NORMAL) {
            return ['allowed' => true, 'control' => $control, 'message' => null];
        }

        if ($control->mode === GlobalControl::MODE_TRADING_PAUSED) {
            return [
                'allowed' => false,
                'control' => $control,
                'message' => 'Trading is temporarily paused.',
            ];
        }

        return ['allowed' => true, 'control' => $control, 'message' => null];
    }

    public function settlementPolicy(Trade $trade): array
    {
        $control = $this->activeControl($trade->asset, GlobalControl::SCOPE_SETTLEMENT);

        return [
            'control' => $control,
            'mode' => $control?->mode ?? GlobalControl::MODE_NORMAL,
            'affects_settlement' => false,
        ];
    }

    private function scopeApplies(GlobalControl $control, ?string $scope): bool
    {
        if ($scope === null || $control->scope === GlobalControl::SCOPE_ALL) {
            return true;
        }

        return $control->scope === $scope;
    }

    private function assetApplies(GlobalControl $control, ?Asset $asset): bool
    {
        $assetIds = $control->asset_ids;

        if (!$assetIds || $asset === null) {
            return true;
        }

        return in_array((int) $asset->id, array_map('intval', $assetIds), true);
    }
}
