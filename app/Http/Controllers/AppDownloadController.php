<?php

namespace App\Http\Controllers;

use App\Services\AppDownloadService;

class AppDownloadController extends Controller
{
    public function __invoke()
    {
        $path = AppDownloadService::apkPublicPath();

        if (!is_file($path)) {
            abort(404);
        }

        return response()->download($path, AppDownloadService::filename(), [
            'Content-Type' => 'application/vnd.android.package-archive',
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
