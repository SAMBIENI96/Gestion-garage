<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Usage: ->middleware('role:patron')
     *        ->middleware('role:patron,accueil')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        if (!$request->user()->is_active) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Votre compte est désactivé.');
        }

        if (!in_array($request->user()->role, $roles)) {
            abort(403, 'Accès refusé : permissions insuffisantes.');
        }

        return $next($request);
    }
}
