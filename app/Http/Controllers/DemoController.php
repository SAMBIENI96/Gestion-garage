<?php

namespace App\Http\Controllers;

use App\Support\Demo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemoController extends Controller
{
    // POST /demo/{role} : connexion sans identifiants (mode démo uniquement)
    public function login(Request $request, string $role)
    {
        abort_unless(Demo::enabled() && in_array($role, ['patron', 'accueil', 'mecanicien'], true), 404);

        $user = Demo::account($role);

        // Le mécanicien découvre l'application mobile, conçue pour lui (session de démo)
        if ($role === 'mecanicien') {
            return MecanicienAppController::redirectToApp($user, demo: true);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', "Bienvenue dans la démo ! Vous êtes connecté en tant que {$user->role_label}.");
    }
}
