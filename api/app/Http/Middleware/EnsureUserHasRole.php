<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->actif) {
            abort(403, 'Compte inactif ou non authentifié.');
        }

        if (! empty($roles) && ! $user->hasRole(...$roles)) {
            abort(403, 'Rôle insuffisant pour cette action.');
        }

        return $next($request);
    }
}
