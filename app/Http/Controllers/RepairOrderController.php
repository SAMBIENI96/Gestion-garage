<?php

namespace App\Http\Controllers;

use App\Models\RepairOrder;
use App\Models\Client;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\InterventionNote;
use Illuminate\Http\Request;

class RepairOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = RepairOrder::with(['client', 'vehicle', 'assignedTo', 'createdBy']);

        if ($search = $request->get('q')) {
            $query->search($search);
        }

        if ($statut = $request->get('statut')) {
            $query->where('statut', $statut);
        }

        if ($urgence = $request->get('urgence')) {
            $query->where('urgence', $urgence);
        }

        $orders = $query->orderByRaw("FIELD(urgence,'vip','urgent','normal')")
                        ->orderBy('date_entree', 'desc')
                        ->paginate(20)
                        ->withQueryString();

        return view('repairs.index', compact('orders'));
    }

    public function create(Request $request)
    {
        $clients  = Client::orderBy('nom')->get(['id', 'nom', 'prenom', 'telephone']);
        $client   = $request->client_id ? Client::with('vehicles')->findOrFail($request->client_id) : null;
        $vehicle  = $request->vehicle_id ? Vehicle::findOrFail($request->vehicle_id) : null;
        return view('repairs.create', compact('clients', 'client', 'vehicle'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'          => 'required|exists:clients,id',
            'vehicle_id'         => 'required|exists:vehicles,id',
            'description_panne'  => 'required|string',
            'pieces_estimees'    => 'nullable|string',
            'cout_estime'        => 'nullable|numeric|min:0',
            'urgence'            => 'required|in:normal,urgent,vip',
            'date_entree'        => 'required|date',
            'date_sortie_prevue' => 'nullable|date|after_or_equal:date_entree',
            'kilometrage_entree' => 'nullable|integer|min:0',
        ]);

        $data['created_by'] = auth()->id();
        $data['statut']     = 'nouveau';

        $order = RepairOrder::create($data);

        return redirect()->route('repairs.show', $order)
            ->with('success', "Ordre {$order->numero} créé.");
    }

    public function show(RepairOrder $repair)
    {
        $repair->load(['client', 'vehicle', 'assignedTo', 'createdBy', 'notes.user', 'invoice']);
        $mecaniciens = User::mecaniciens()->get();
        return view('repairs.show', compact('repair', 'mecaniciens'));
    }

    public function edit(RepairOrder $repair)
    {
        $clients     = Client::orderBy('nom')->get(['id', 'nom', 'prenom']);
        $mecaniciens = User::mecaniciens()->get();
        return view('repairs.edit', compact('repair', 'clients', 'mecaniciens'));
    }

    public function update(Request $request, RepairOrder $repair)
    {
        $data = $request->validate([
            'description_panne'  => 'required|string',
            'pieces_estimees'    => 'nullable|string',
            'cout_estime'        => 'nullable|numeric|min:0',
            'urgence'            => 'required|in:normal,urgent,vip',
            'date_entree'        => 'required|date',
            'date_sortie_prevue' => 'nullable|date',
            'kilometrage_entree' => 'nullable|integer|min:0',
        ]);

        $repair->update($data);

        return redirect()->route('repairs.show', $repair)->with('success', 'Ordre mis à jour.');
    }

    // Patron: assigner un mécanicien
    public function assign(Request $request, RepairOrder $repair)
    {
        $data = $request->validate([
            'assigned_to'  => 'required|exists:users,id',
            'notes_patron' => 'nullable|string|max:500',
        ]);

        $repair->update($data);

        InterventionNote::create([
            'repair_order_id' => $repair->id,
            'user_id'         => auth()->id(),
            'contenu'         => "Assigné à " . $repair->assignedTo->name .
                                 ($data['notes_patron'] ? " — Note : {$data['notes_patron']}" : ''),
            'ancien_statut'   => $repair->statut,
            'nouveau_statut'  => $repair->statut,
        ]);

        return back()->with('success', 'Mécanicien assigné.');
    }

    // Mécanicien: mettre à jour le statut
    public function updateStatut(Request $request, RepairOrder $repair)
    {
        $this->authorize('updateStatut', $repair);

        $data = $request->validate([
            'statut'  => 'required|in:en_attente_pieces,en_cours,termine,probleme',
            'contenu' => 'required|string|max:1000',
            'photo'   => 'nullable|image|max:4096',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('interventions', 'public');
        }

        $ancienStatut = $repair->statut;

        $updateData = ['statut' => $data['statut']];
        if ($data['statut'] === 'termine') {
            $updateData['date_sortie_effective'] = now();
        }

        $repair->update($updateData);

        InterventionNote::create([
            'repair_order_id' => $repair->id,
            'user_id'         => auth()->id(),
            'contenu'         => $data['contenu'],
            'photo_path'      => $photoPath,
            'ancien_statut'   => $ancienStatut,
            'nouveau_statut'  => $data['statut'],
        ]);

        return back()->with('success', 'Statut mis à jour.');
    }

    // Vue mécanicien: ses tâches
    public function mecanicien()
    {
        $orders = RepairOrder::with(['client', 'vehicle', 'notes'])
            ->where('assigned_to', auth()->id())
            ->whereIn('statut', ['nouveau', 'en_attente_pieces', 'en_cours', 'probleme'])
            ->orderByRaw("FIELD(urgence,'vip','urgent','normal')")
            ->get();

        return view('repairs.mecanicien', compact('orders'));
    }

    // Vue planning global (patron)
    public function planning()
    {
        $mecaniciens = User::mecaniciens()
            ->with(['repairOrdersAssigned' => fn($q) =>
                $q->actifs()->with(['client', 'vehicle'])
                  ->orderByRaw("FIELD(urgence,'vip','urgent','normal')")
            ])
            ->get();

        $nonAssignes = RepairOrder::actifs()
            ->whereNull('assigned_to')
            ->with(['client', 'vehicle'])
            ->get();

        return view('planning.index', compact('mecaniciens', 'nonAssignes'));
    }
}
