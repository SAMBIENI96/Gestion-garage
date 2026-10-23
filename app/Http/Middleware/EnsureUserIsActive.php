<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Un compte désactivé par le patron perd immédiatement l'accès à l'API
// (ses jetons sont révoqués), même si l'application mobile est déjà connectée.
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && (! $user->is_active || $user->isPending())) {
            $user->tokens()->delete();

            return response()->json(['message' => 'Compte désactivé. Contactez le patron.'], 401);
        }

        return $next($request);
    }
}
