<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\GlobalControl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class AdminGlobalControlController extends Controller
{
    public function show()
    {
        $control = GlobalControl::latest('id')->first();

        return response()->json([
            'control' => $control,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'enabled' => 'required|boolean',
            'mode' => ['required', Rule::in([GlobalControl::MODE_NORMAL, GlobalControl::MODE_TRADING_PAUSED])],
            'scope' => ['required', Rule::in([GlobalControl::SCOPE_ALL, GlobalControl::SCOPE_ENTRY])],
            'asset_ids' => 'nullable|array',
            'asset_ids.*' => 'integer|exists:assets,id',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'version' => 'nullable|integer|min:1',
            'reason' => 'nullable|string|max:1000',
        ]);

        $control = DB::transaction(function () use ($data) {
            $current = GlobalControl::latest('id')->lockForUpdate()->first();

            if ($current && isset($data['version']) && (int) $data['version'] !== (int) $current->version) {
                throw new HttpResponseException(response()->json([
                    'error' => 'Global control was changed by another admin. Refresh and try again.',
                    'current_version' => $current->version,
                ], 409));
            }

            $payload = [
                'enabled' => (bool) $data['enabled'],
                'mode' => $data['mode'],
                'scope' => $data['scope'],
                'asset_ids' => $data['asset_ids'] ?? null,
                'starts_at' => $data['starts_at'] ?? null,
                'expires_at' => $data['expires_at'] ?? null,
                'updated_by' => Auth::id(),
                'reason' => $data['reason'] ?? null,
                'correlation_id' => (string) Str::uuid(),
                'disabled_at' => $data['enabled'] ? null : now(),
            ];

            if ($current) {
                $current->update($payload + ['version' => $current->version + 1]);
                $control = $current->fresh();
            } else {
                $control = GlobalControl::create($payload + [
                    'version' => 1,
                    'created_by' => Auth::id(),
                ]);
            }

            $logged = AdminLog::record(
                'global_control_update',
                "Global control updated: enabled={$control->enabled}, mode={$control->mode}, scope={$control->scope}, version={$control->version}, reason=" . ($control->reason ?: 'none'),
                'global_control',
                $control->id
            );

            if (!$logged) {
                throw new \RuntimeException('Global control audit log write failed.');
            }

            return $control;
        });

        return response()->json([
            'success' => true,
            'control' => $control,
        ]);
    }
}
