<?php
// ═══════════════════════════════════════════════════════
// AJOUTER cette méthode dans MecanicienApiController.php
// après la méthode mesTaches()
// ═══════════════════════════════════════════════════════

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

// ═══════════════════════════════════════════════════════
// AJOUTER aussi dans routes/api.php :
// Route::get('/mes-taches-terminees', [MecanicienApiController::class, 'mesTachesTerminees']);
// ═══════════════════════════════════════════════════════
