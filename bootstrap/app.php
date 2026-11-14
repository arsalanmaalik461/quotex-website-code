<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'api/*',
            'deposit/lgpay/callback',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->context(function () {
            $request = request();
            $sessionUserId = null;

            try {
                foreach ($request->hasSession() ? $request->session()->all() : [] as $key => $value) {
                    if (is_string($key) && str_starts_with($key, 'login_')) {
                        $sessionUserId = $value;
                        break;
                    }
                }
            } catch (\Throwable) {
                $sessionUserId = null;
            }

            return [
                'request_id' => $request->headers->get('X-Request-Id') ?: $request->headers->get('X-Litespeed-Request-Id'),
                'method' => $request->method(),
                'path' => $request->path(),
                'auth_user_id' => $sessionUserId,
                'admin_user_id' => session('admin_impersonating') ? session('admin_original_id') : null,
            ];
        });
    })->create();
