<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RepairOrder;
use App\Models\InterventionNote;
use App\Models\User;
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

        $user = User::where('email', mb_strtolower($data['email']))->first();

        // Message unique : ne révèle pas si l'adresse existe
        if (! $user || ! $user->password || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['success' => false, 'message' => 'E-mail ou mot de passe incorrect.'], 401);
        }

        if (! $user->is_active || $user->isPending()) {
            return response()->json(['success' => false, 'message' => 'Compte désactivé ou en attente de validation.'], 403);
        }

        if (! $user->isMecanicien()) {
            return response()->json(['success' => false, 'message' => 'Application réservée aux mécaniciens.'], 403);
        }

        $user->tokens()->delete();
        $token = $user->createToken('pwa-mecanicien')->plainTextToken;

        return response()->json([
            'success' => true,
            'token'   => $token,
            'user'    => $this->userPayload($user),
        ]);
    }

    // POST /api/logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnecté.']);
    }

    // GET /api/me
    public function me(Request $request)
    {
        return response()->json($this->userPayload($request->user()));
    }

    // PATCH /api/preferences
    public function preferences(Request $request)
    {
        $data = $request->validate(['theme' => 'required|in:system,light,dark']);
        $request->user()->update($data);

        return response()->json($this->userPayload($request->user()));
    }

    // GET /api/mes-taches
    public function mesTaches(Request $request)
    {
        $orders = RepairOrder::with([
                'client:id,nom,prenom,telephone',
                'vehicle:id,immatriculation,marque,modele,annee,couleur',
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
            ->orderBy('date_entree')
            ->get()
            ->map(fn ($o) => $this->summary($o));

        return response()->json($orders);
    }

    // GET /api/mes-taches-terminees
    public function mesTachesTerminees(Request $request)
    {
        $orders = RepairOrder::with([
                'client:id,nom,prenom,telephone',
                'vehicle:id,immatriculation,marque,modele,annee,couleur',
            ])
            ->where('assigned_to', $request->user()->id)
            ->where('statut', 'termine')
            ->orderBy('date_sortie_effective', 'desc')
            ->limit(30)
            ->get()
            ->map(fn ($o) => $this->summary($o));

        return response()->json($orders);
    }

    // GET /api/taches/{id}
    public function tacheDetail(Request $request, RepairOrder $repair)
    {
        if ((int) $repair->assigned_to !== (int) $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $repair->load(['client', 'vehicle', 'notes' => fn ($q) => $q->latest(), 'notes.user:id,name']);

        return response()->json($this->summary($repair) + [
            'notes_patron'       => $repair->notes_patron,
            'kilometrage_entree' => $repair->kilometrage_entree,
            'date_sortie_prevue' => $repair->date_sortie_prevue?->format('d/m/Y'),
            'notes'              => $repair->notes->map(fn ($n) => [
                'id'             => $n->id,
                'auteur'         => $n->user?->name ?? '—',
                'date'           => $n->created_at?->format('d/m/Y H:i'),
                'date_iso'       => $n->created_at?->toIso8601String(),
                'contenu'        => $n->contenu,
                'ancien_statut'  => $n->ancien_statut,
                'nouveau_statut' => $n->nouveau_statut,
                'ancien_label'   => RepairOrder::STATUT_LABELS[$n->ancien_statut] ?? $n->ancien_statut,
                'nouveau_label'  => RepairOrder::STATUT_LABELS[$n->nouveau_statut] ?? $n->nouveau_statut,
                'photo_url'      => $n->photo_path ? asset('storage/'.$n->photo_path) : null,
            ])->values(),
        ]);
    }

    // POST /api/taches/{id}/statut  (photo facultative)
    public function updateStatut(Request $request, RepairOrder $repair)
    {
        if ((int) $repair->assigned_to !== (int) $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $data = $request->validate([
            'statut'  => 'required|in:en_attente_pieces,en_cours,termine,probleme',
            'contenu' => 'required|string|max:1000',
            'photo'   => 'nullable|image|max:6144',
        ]);

        $ancienStatut = $repair->statut;

        $updateData = ['statut' => $data['statut']];
        if ($data['statut'] === 'termine') {
            $updateData['date_sortie_effective'] = now();
        }
        $repair->update($updateData);

        $note = InterventionNote::create([
            'repair_order_id' => $repair->id,
            'user_id'         => $request->user()->id,
            'contenu'         => $data['contenu'],
            'photo_path'      => $request->hasFile('photo') ? $request->file('photo')->store('interventions', 'public') : null,
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
        if ((int) $repair->assigned_to !== (int) $request->user()->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $data = $request->validate([
            'contenu' => 'required|string|max:1000',
            'photo'   => 'nullable|image|max:6144',
        ]);

        $photoPath = $request->hasFile('photo')
            ? $request->file('photo')->store('interventions', 'public')
            : null;

        $note = InterventionNote::create([
            'repair_order_id' => $repair->id,
            'user_id'         => $request->user()->id,
            'contenu'         => $data['contenu'],
            'photo_path'      => $photoPath,
        ]);

        return response()->json([
            'message'   => 'Note ajoutée.',
            'note_id'   => $note->id,
            'photo_url' => $photoPath ? asset('storage/'.$photoPath) : null,
        ]);
    }

    private function userPayload(User $user): array
    {
        return [
            'id'        => $user->id,
            'name'      => $user->name,
            'email'     => $user->email,
            'phone'     => $user->phone,
            'initiales' => $user->initiales,
            'avatar'    => $user->avatar_url,
            'theme'     => $user->theme ?? 'system',
        ];
    }

    private function summary(RepairOrder $o): array
    {
        return [
            'id'                => $o->id,
            'numero'            => $o->numero,
            'statut'            => $o->statut,
            'statut_label'      => $o->statut_label,
            'urgence'           => $o->urgence,
            'urgence_label'     => $o->urgence_label,
            'description_panne' => $o->description_panne,
            'pieces_estimees'   => $o->pieces_estimees,
            'date_entree'       => $o->date_entree?->format('d/m/Y'),
            'date_sortie'       => $o->date_sortie_effective?->format('d/m/Y H:i'),
            'date_sortie_iso'   => $o->date_sortie_effective?->toIso8601String(),
            'client'            => $o->client ? [
                'prenom'    => $o->client->prenom,
                'nom'       => $o->client->nom,
                'telephone' => $o->client->telephone,
            ] : null,
            'vehicle'           => $o->vehicle ? [
                'immatriculation' => $o->vehicle->immatriculation,
                'marque'          => $o->vehicle->marque,
                'modele'          => $o->vehicle->modele,
                'annee'           => $o->vehicle->annee,
                'couleur'         => $o->vehicle->couleur,
            ] : null,
        ];
    }
}
