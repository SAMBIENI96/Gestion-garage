<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function show()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|max:150|unique:users,email',
            'phone'    => 'nullable|string|max:20',
            'role'     => 'required|in:accueil,mecanicien',
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'email.unique' => 'Un compte existe déjà avec cette adresse. Connectez-vous ou demandez au patron.',
        ]);

        // Le rôle choisi n'est qu'une demande : le patron le confirme en validant le compte
        User::create([
            'name'      => $data['name'],
            'email'     => mb_strtolower($data['email']),
            'phone'     => $data['phone'] ?? null,
            'role'      => $data['role'],
            'password'  => Hash::make($data['password']),
            'is_active' => false,
        ]);

        return redirect()->route('login')->with('status', 'Demande envoyée ! Le patron doit valider votre compte avant votre première connexion.');
    }
}
