<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('admin.login')->with('error', 'Admin login required.');
        }

        $user = Auth::user();

        // Check if impersonating - allow if original user is admin
        if (session('admin_impersonating') && session('admin_original_id')) {
            $adminUser = \App\Models\User::find(session('admin_original_id'));
            if ($adminUser && $adminUser->is_admin) {
                return $next($request);
            }
        }

        if (!$user->is_admin) {
            abort(403, 'Access denied. Admin only.');
        }

        if ($user->is_blocked) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Your account has been blocked.');
        }

        return $next($request);
    }
}
