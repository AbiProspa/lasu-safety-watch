<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Usage: ->middleware('role:staff') or ->middleware('role:admin') */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        $allowed = match ($role) {
            'admin' => $user?->isAdmin(),
            'staff' => $user?->isStaff(),
            default => $user?->role === $role,
        };

        abort_unless($allowed && $user->is_active, 403, 'You do not have access to this page.');

        return $next($request);
    }
}
