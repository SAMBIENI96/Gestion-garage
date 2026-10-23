<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Identifiants incorrects.',
            ]);
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            if ($user->isPending()) {
                return back()->withInput($request->only('email'))
                    ->with('status', 'Votre demande est en cours de validation par le patron.');
            }
            return back()->withErrors(['email' => 'Compte désactivé. Contactez le patron.']);
        }

        $request->session()->regenerate();

        // Le mécanicien travaille dans son application mobile
        if ($user->isMecanicien()) {
            return redirect()->route('mecanicien.handoff');
        }

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
