<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('role')->orderBy('name')->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'nullable|string|max:20',
            'role'     => 'required|in:accueil,mecanicien',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $data['password'] = Hash::make($data['password']);
        User::create($data);

        return redirect()->route('users.index')->with('success', 'Utilisateur créé.');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => "required|email|unique:users,email,{$user->id}",
            'phone'    => 'nullable|string|max:20',
            'role'     => 'required|in:accueil,mecanicien',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        return redirect()->route('users.index')->with('success', 'Utilisateur mis à jour.');
    }

    public function toggleActive(User $user)
    {
        if ($user->isPatron()) {
            return back()->with('error', 'Impossible de désactiver le patron.');
        }
        $user->update(['is_active' => !$user->is_active]);
        $msg = $user->is_active ? 'Compte activé.' : 'Compte désactivé.';
        return back()->with('success', $msg);
    }

    public function destroy(User $user)
    {
        if ($user->isPatron()) {
            return back()->with('error', 'Impossible de supprimer le patron.');
        }
        if ($user->repairOrdersAssigned()->actifs()->exists()) {
            return back()->with('error', 'Ce mécanicien a des interventions en cours. Réassignez-les d\'abord.');
        }
        $user->update(['is_active' => false]);
        return redirect()->route('users.index')->with('success', 'Compte désactivé.');
    }
}
