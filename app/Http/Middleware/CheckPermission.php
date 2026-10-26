<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authorises an admin request against a named permission. Authorisation is
 * always enforced server-side here, never by merely hiding UI (README §15).
 *
 * Usage: ->middleware('permission:transactions.create')
 */
class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = Auth::guard('web')->user();

        if (! $user || ! $user->isActive()) {
            Auth::guard('web')->logout();
            abort(403, 'Akun admin tidak aktif atau sesi tidak valid.');
        }

        if (! $user->hasPermission($permission)) {
            abort(403, 'Anda tidak memiliki izin untuk tindakan ini.');
        }

        return $next($request);
    }
}
