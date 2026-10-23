<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Demo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Passage de relais vers l'application mobile : un mécanicien connecté sur la page
// d'accueil arrive directement dans son application, déjà authentifiée.
class MecanicienAppController extends Controller
{
    // GET /connexion/atelier (hors du dossier public/mecanicien, servi en statique)
    public function handoff(Request $request)
    {
        $user = $request->user();

        // La session web n'est plus utile : l'application utilise son propre jeton
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return self::redirectToApp($user);
    }

    /**
     * Le jeton passe dans le fragment d'URL (#…) : il n'est jamais envoyé au serveur
     * ni enregistré dans les journaux, et l'application l'efface aussitôt lu.
     */
    public static function redirectToApp(User $user, bool $demo = false)
    {
        // Un seul appareil connecté par mécanicien, sauf le compte de démo partagé
        // (plusieurs visiteurs peuvent l'utiliser en même temps)
        if (! (Demo::enabled() && Demo::isDemoUser($user))) {
            $user->tokens()->delete();
        }
        $token = $user->createToken($demo ? 'pwa-demo' : 'pwa-mecanicien')->plainTextToken;

        return redirect('/mecanicien/#'.($demo ? 'demo' : 'connexion').'='.rawurlencode($token));
    }
}
