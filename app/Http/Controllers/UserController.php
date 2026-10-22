<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users   = User::approved()->orderBy('role')->orderBy('name')->get();
        $pending = User::pending()->latest()->get();
        return view('users.index', compact('users', 'pending'));
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
        $data['approved_at'] = now();
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

    public function approve(Request $request, User $user)
    {
        abort_unless($user->isPending(), 404);

        $data = $request->validate(['role' => 'required|in:accueil,mecanicien']);

        $user->update([
            'role'        => $data['role'],
            'is_active'   => true,
            'approved_at' => now(),
        ]);

        return back()->with('success', "Accès accordé à {$user->name}.");
    }

    public function reject(User $user)
    {
        abort_unless($user->isPending(), 404);

        $user->delete();

        return back()->with('success', 'Demande refusée.');
    }

    public function toggleActive(User $user)
    {
        if ($user->isPending()) {
            return back()->with('error', 'Validez d\'abord la demande d\'accès.');
        }
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
