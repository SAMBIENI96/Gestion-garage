<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RepairOrder;
use App\Models\InterventionNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MecanicienApiController extends Controller
{
    // POST /api/login
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = \App\Models\User::where('email', $data['email'])->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Utilisateur introuvable'], 404);
        }

        if (!Hash::check($data['password'], $user->password)) {
            return response()->json(['success' => false, 'message' => 'Mot de passe incorrect'], 401);
        }

        if (!$user->is_active) {
            return response()->json(['success' => false, 'message' => 'Compte désactivé'], 403);
        }

        if (!$user->isMecanicien()) {
            return response()->json(['success' => false, 'message' => 'Accès réservé aux mécaniciens'], 403);
        }

        $user->tokens()->delete();

        $token = $user->createToken('flutter-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $user
        ]);
    }

    // POST /api/logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnecté.']);
    }

    // GET /api/mes-taches
    public function mesTaches(Request $request)
    {
        $orders = RepairOrder::with([
                'client:id,nom,prenom,telephone',
                'vehicle:id,immatriculation,marque,modele,annee,couleur'
            ])
            ->where('assigned_to', $request->user()->id)
            ->whereIn('statut', ['nouveau', 'en_attente_pieces', 'en_cours', 'probleme'])
            ->orderByRaw("
                CASE urgence
                    WHEN 'vip' THEN 1
                    WHEN 'urgent' THEN 2
                    WHEN 'normal' THEN 3
                    ELSE 4
                END
            ")
            ->get()
            ->map(fn($o) => [
                'id' => $o->id,
                'numero' => $o->numero,
                'statut' => $o->statut,
                'statut_label' => $o->statut_label,
                'urgence' => $o->urgence,
                'urgence_label' => $o->urgence_label,
                'description_panne' => $o->description_panne,
                'pieces_estimees' => $o->pieces_estimees,
                'date_entree' => optional($o->date_entree)?->format('d/m/Y'),
                'client' => $o->client,
                'vehicle' => $o->vehicle,
            ]);

        return response()->json($orders);
    }

    // GET /api/mes-taches-terminees
    public function mesTachesTerminees(Request $request)
    {
        $orders = RepairOrder::with([
                'client:id,nom,prenom,telephone',
                'vehicle:id,immatriculation,marque,modele'
            ])
            ->where('assigned_to', $request->user()->id)
            ->where('statut', 'termine')
            ->orderBy('date_sortie_effective', 'desc')
            ->limit(20)
            ->get()
            ->map(fn($o) => [
                'id' => $o->id,
                'numero' => $o->numero,
                'statut' => $o->statut,
                'statut_label' => $o->statut_label,
                'urgence' => $o->urgence,
                'urgence_label' => $o->urgence_label,
                'description_panne' => $o->description_panne,
                'date_entree' => optional($o->date_entree)?->format('d/m/Y'),
                'client' => $o->client,
                'vehicle' => $o->vehicle,
            ]);

        return response()->json($orders);
    }

    // GET /api/taches/{id}
    public function tacheDetail(Request $request, RepairOrder $repair)
    {
        if ($repair->assigned_to !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $repair->load(['client', 'vehicle', 'notes.user:id,name']);

        return response()->json($repair);
    }

    // POST /api/taches/{id}/statut
    public function updateStatut(Request $request, RepairOrder $repair)
    {
        if ($repair->assigned_to !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $data = $request->validate([
            'statut'  => 'required|in:en_attente_pieces,en_cours,termine,probleme',
            'contenu' => 'required|string|max:1000',
        ]);

        $ancienStatut = $repair->statut;

        $updateData = ['statut' => $data['statut']];

        if ($data['statut'] === 'termine') {
            $updateData['date_sortie_effective'] = now();
        }

        $repair->update($updateData);

        $note = InterventionNote::create([
            'repair_order_id' => $repair->id,
            'user_id' => $request->user()->id,
            'contenu' => $data['contenu'],
            'ancien_statut' => $ancienStatut,
            'nouveau_statut' => $data['statut'],
        ]);

        return response()->json([
            'message' => 'Statut mis à jour.',
            'statut' => $repair->statut,
            'note_id' => $note->id,
        ]);
    }

    // POST /api/taches/{id}/note
    public function addNote(Request $request, RepairOrder $repair)
    {
        if ($repair->assigned_to !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $data = $request->validate([
            'contenu' => 'required|string|max:1000',
            'photo' => 'nullable|image|max:4096',
        ]);

        $photoPath = null;

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('interventions', 'public');
        }

        $note = InterventionNote::create([
            'repair_order_id' => $repair->id,
            'user_id' => $request->user()->id,
            'contenu' => $data['contenu'],
            'photo_path' => $photoPath,
        ]);

        return response()->json([
            'message' => 'Note ajoutée.',
            'note_id' => $note->id,
            'photo_url' => $photoPath ? asset('storage/' . $photoPath) : null,
        ]);
    }
}