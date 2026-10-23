<?php

namespace App\Http\Middleware;

use App\Support\Demo;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

// Ouvrir le lien du site (lien partagé, favori, adresse tapée) referme une
// session de démo : le visiteur revoit la page de connexion et choisit
// lui-même de relancer la démo. Naviguer dans la démo ou recharger la page
// ne la ferme pas.
class EndDemoOnNewVisit
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isNewVisit($request) && Demo::enabled() && Demo::isDemoUser($request->user())) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        return $next($request);
    }

    private function isNewVisit(Request $request): bool
    {
        if (! $request->isMethod('GET') || $request->header('Sec-Fetch-Mode') !== 'navigate') {
            return false;
        }

        // « none » : adresse tapée, favori, nouvel onglet ; « cross-site » : lien venant d'un autre site
        if (! in_array($request->header('Sec-Fetch-Site'), ['none', 'cross-site'], true)) {
            return false;
        }

        // Un rechargement garde la page d'origine comme référent : la démo continue
        $referer = parse_url((string) $request->headers->get('referer'), PHP_URL_HOST);

        return $referer === null || $referer !== $request->getHost();
    }
}
