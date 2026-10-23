<?php

namespace App\Http\Middleware;

use App\Support\Demo;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// En mode démo, les comptes partagés ne peuvent ni changer leurs identifiants
// ni gérer les utilisateurs : la démo reste accessible à tous les visiteurs.
class BlockInDemo
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethodSafe() || ! Demo::enabled() || ! Demo::isDemoUser($request->user())) {
            return $next($request);
        }

        $message = 'Action désactivée dans la démo.';

        return $request->expectsJson()
            ? response()->json(['message' => $message], 403)
            : back()->with('error', $message);
    }
}
