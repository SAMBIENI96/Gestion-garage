<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Client;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::with('client');

        if ($search = $request->get('q')) {
            $query->search($search)->orWhereHas('client', fn($q) =>
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
            );
        }

        $vehicles = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('vehicles.index', compact('vehicles'));
    }

    public function create(Request $request)
    {
        $client = $request->client_id ? Client::findOrFail($request->client_id) : null;
        $clients = Client::orderBy('nom')->get(['id', 'nom', 'prenom']);
        return view('vehicles.create', compact('clients', 'client'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'       => 'required|exists:clients,id',
            'immatriculation' => 'required|string|max:20|unique:vehicles,immatriculation',
            'marque'          => 'required|string|max:80',
            'modele'          => 'required|string|max:100',
            'annee'           => 'nullable|integer|min:1950|max:' . (date('Y') + 1),
            'kilometrage'     => 'nullable|integer|min:0',
            'couleur'         => 'nullable|string|max:50',
            'numero_chassis'  => 'nullable|string|max:30',
            'notes'           => 'nullable|string|max:1000',
        ]);

        $vehicle = Vehicle::create($data);

        return redirect()->route('clients.show', $vehicle->client_id)
            ->with('success', "Véhicule {$vehicle->designation} ajouté.");
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load(['client', 'repairOrders.assignedTo', 'repairOrders.notes']);
        return view('vehicles.show', compact('vehicle'));
    }

    public function edit(Vehicle $vehicle)
    {
        $clients = Client::orderBy('nom')->get(['id', 'nom', 'prenom']);
        return view('vehicles.edit', compact('vehicle', 'clients'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'client_id'       => 'required|exists:clients,id',
            'immatriculation' => "required|string|max:20|unique:vehicles,immatriculation,{$vehicle->id}",
            'marque'          => 'required|string|max:80',
            'modele'          => 'required|string|max:100',
            'annee'           => 'nullable|integer|min:1950|max:' . (date('Y') + 1),
            'kilometrage'     => 'nullable|integer|min:0',
            'couleur'         => 'nullable|string|max:50',
            'numero_chassis'  => 'nullable|string|max:30',
            'notes'           => 'nullable|string|max:1000',
        ]);

        $vehicle->update($data);

        return redirect()->route('vehicles.show', $vehicle)->with('success', 'Véhicule mis à jour.');
    }

    public function byClient(Client $client)
    {
        return response()->json($client->vehicles()->get(['id', 'immatriculation', 'marque', 'modele']));
    }
}
