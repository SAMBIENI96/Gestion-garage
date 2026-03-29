<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RepairOrder;
use App\Models\InterventionNote;
use Illuminate\Http\Request;

class MecanicienApiController extends Controller
{
    // POST /api/login
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!\Auth::attempt($data)) {
            return response()->json(['message' => 'Identifiants incorrects.'], 401);
        }

        $user = \Auth::user();

        if (!$user->is_active) {
            return response()->json(['message' => 'Compte désactivé.'], 403);
        }

        if (!$user->isMecanicien()) {
            return response()->json(['message' => 'Accès réservé aux mécaniciens.'], 403);
        }

        $token = $user->createToken('flutter-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
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
        $orders = RepairOrder::with(['client:id,nom,prenom,telephone', 'vehicle:id,immatriculation,marque,modele,annee,couleur'])
            ->where('assigned_to', $request->user()->id)
            ->whereIn('statut', ['nouveau', 'en_attente_pieces', 'en_cours', 'probleme'])
            ->orderByRaw("FIELD(urgence,'vip','urgent','normal')")
            ->get()
            ->map(fn($o) => [
                'id'                => $o->id,
                'numero'            => $o->numero,
                'statut'            => $o->statut,
                'statut_label'      => $o->statut_label,
                'urgence'           => $o->urgence,
                'urgence_label'     => $o->urgence_label,
                'description_panne' => $o->description_panne,
                'pieces_estimees'   => $o->pieces_estimees,
                'date_entree'       => $o->date_entree?->format('d/m/Y'),
                'client'            => $o->client,
                'vehicle'           => $o->vehicle,
            ]);

        return response()->json($orders);
    }

    // GET /api/mes-taches-terminees
    public function mesTachesTerminees(Request $request)
    {
        $orders = RepairOrder::with(['client:id,nom,prenom,telephone', 'vehicle:id,immatriculation,marque,modele'])
            ->where('assigned_to', $request->user()->id)
            ->where('statut', 'termine')
            ->orderBy('date_sortie_effective', 'desc')
            ->limit(20)
            ->get()
            ->map(fn($o) => [
                'id'                => $o->id,
                'numero'            => $o->numero,
                'statut'            => $o->statut,
                'statut_label'      => $o->statut_label,
                'urgence'           => $o->urgence,
                'urgence_label'     => $o->urgence_label,
                'description_panne' => $o->description_panne,
                'date_entree'       => $o->date_entree?->format('d/m/Y'),
                'client'            => $o->client,
                'vehicle'           => $o->vehicle,
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

        return response()->json([
            'id'                => $repair->id,
            'numero'            => $repair->numero,
            'statut'            => $repair->statut,
            'statut_label'      => $repair->statut_label,
            'urgence'           => $repair->urgence,
            'urgence_label'     => $repair->urgence_label,
            'description_panne' => $repair->description_panne,
            'pieces_estimees'   => $repair->pieces_estimees,
            'notes_patron'      => $repair->notes_patron,
            'date_entree'       => $repair->date_entree?->format('d/m/Y'),
            'client'            => $repair->client,
            'vehicle'           => $repair->vehicle,
            'notes'             => $repair->notes->map(fn($n) => [
                'id'             => $n->id,
                'contenu'        => $n->contenu,
                'photo_url'      => $n->photo_path ? asset('storage/' . $n->photo_path) : null,
                'ancien_statut'  => $n->ancien_statut,
                'nouveau_statut' => $n->nouveau_statut,
                'auteur'         => $n->user->name,
                'date'           => $n->created_at->format('d/m/Y H:i'),
            ]),
        ]);
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
        $updateData   = ['statut' => $data['statut']];

        if ($data['statut'] === 'termine') {
            $updateData['date_sortie_effective'] = now();
        }

        $repair->update($updateData);

        $note = InterventionNote::create([
            'repair_order_id' => $repair->id,
            'user_id'         => $request->user()->id,
            'contenu'         => $data['contenu'],
            'ancien_statut'   => $ancienStatut,
            'nouveau_statut'  => $data['statut'],
        ]);

        return response()->json([
            'message' => 'Statut mis à jour.',
            'statut'  => $repair->statut,
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
            'photo'   => 'nullable|image|max:4096',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('interventions', 'public');
        }

        $note = InterventionNote::create([
            'repair_order_id' => $repair->id,
            'user_id'         => $request->user()->id,
            'contenu'         => $data['contenu'],
            'photo_path'      => $photoPath,
        ]);

        return response()->json([
            'message'   => 'Note ajoutée.',
            'note_id'   => $note->id,
            'photo_url' => $photoPath ? asset('storage/' . $photoPath) : null,
        ]);
    }
}