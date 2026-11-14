<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class TradeSimulationControl extends Model
{
    public const MODE_NORMAL = 'normal';
    public const MODE_SIMULATION_WIN = 'simulation_win';
    public const MODE_SIMULATION_LOSS = 'simulation_loss';

    protected $fillable = [
        'mode',
        'version',
        'created_by',
        'updated_by',
        'reason',
    ];

    protected $casts = [
        'version' => 'integer',
    ];

    public static function currentMode(): string
    {
        if (!self::tableExists()) {
            return self::MODE_NORMAL;
        }

        return static::latest('id')->value('mode') ?: self::MODE_NORMAL;
    }

    public static function tableExists(): bool
    {
        try {
            return Schema::hasTable((new self())->getTable());
        } catch (\Throwable) {
            return false;
        }
    }

    public static function isSimulationMode(?string $mode): bool
    {
        return in_array($mode, [self::MODE_SIMULATION_WIN, self::MODE_SIMULATION_LOSS], true);
    }

    public static function overrideForMode(?string $mode): string
    {
        return match ($mode) {
            self::MODE_SIMULATION_WIN => 'force_win',
            self::MODE_SIMULATION_LOSS => 'force_loss',
            default => 'none',
        };
    }
}
