<?php

namespace App\Services;

use App\Models\AppSetting;
use Throwable;

class AppDownloadService
{
    public const POPUP_STORAGE_KEY = 'app_download_popup_dismissed_at';
    public const POPUP_SUPPRESSION_MS = 86400000;

    public static function defaults(): array
    {
        return [
            'app_download.popup_enabled' => '1',
            'app_download.popup_title' => 'Download Our App',
            'app_download.popup_description' => 'Get a faster trading experience with our Android app.',
            'app_download.button_text' => 'Download App',
            'app_download.apk_path' => config('app_download.apk_path', 'app/qoutex.apk'),
            'app_download.popup_logo_url' => 'app/logo.png',
        ];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            return AppSetting::get($key, $default);
        } catch (Throwable) {
            return $default;
        }
    }

    public static function settings(): array
    {
        $settings = [];
        foreach (self::defaults() as $key => $default) {
            $settings[$key] = self::get($key, $default);
        }

        return $settings;
    }

    public static function popupEnabled(): bool
    {
        return in_array((string) self::get('app_download.popup_enabled', '1'), ['1', 'true', 'on', 'yes'], true);
    }

    public static function apkRelativePath(): string
    {
        $path = (string) self::get('app_download.apk_path', config('app_download.apk_path', 'app/qoutex.apk'));
        $path = ltrim(str_replace('\\', '/', trim($path)), '/');

        if ($path === '' || str_contains($path, '..') || !str_ends_with(strtolower($path), '.apk')) {
            return config('app_download.apk_path', 'app/qoutex.apk');
        }

        return $path;
    }

    public static function apkPublicPath(): string
    {
        return public_path(self::apkRelativePath());
    }

    public static function filename(): string
    {
        return config('app_download.filename', 'qoutex.apk');
    }
}
