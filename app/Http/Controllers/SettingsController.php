<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function edit(Request $request)
    {
        return view('settings.index', ['user' => $request->user()]);
    }

    public function updateTheme(Request $request)
    {
        $data = $request->validate([
            'theme' => 'required|in:system,light,dark',
        ]);

        $request->user()->update($data);

        if ($request->expectsJson()) {
            return response()->json(['theme' => $data['theme']]);
        }

        return back()->with('success', 'Apparence enregistrée.');
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name'  => 'required|string|max:100',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($data);

        return back()->with('success', 'Profil mis à jour.');
    }

    public function updatePassword(Request $request)
    {
        // Compte créé avec Google : pas encore de mot de passe à confirmer
        $data = $request->validate([
            'current_password' => $request->user()->password ? 'required|current_password' : 'nullable',
            'password'         => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $request->user()->update(['password' => Hash::make($data['password'])]);

        return back()->with('success', 'Mot de passe modifié.');
    }
}
