<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class AdminLog extends Model
{
    protected $fillable = [
        'admin_id', 'action', 'target_type', 'target_id', 'description', 'ip_address'
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public static function record(string $action, string $description, string $targetType = null, int $targetId = null): bool
    {
        $adminId = session('admin_impersonating')
            ? session('admin_original_id')
            : auth()->id();

        if (!$adminId) {
            return false;
        }

        try {
            static::create([
                'admin_id'    => $adminId,
                'action'      => $action,
                'target_type' => $targetType,
                'target_id'   => $targetId,
                'description' => $description,
                'ip_address'  => request()->ip(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Admin audit log write failed.', [
                'action' => $action,
                'target_type' => $targetType,
                'target_id' => $targetId,
                'admin_id' => $adminId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }

        return true;
    }
}
