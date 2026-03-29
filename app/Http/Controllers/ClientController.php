<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::with(['vehicles'])->withCount(['repairOrders']);

        if ($search = $request->get('q')) {
            $query->search($search);
        }

        $clients = $query->orderBy('nom')->paginate(20)->withQueryString();

        return view('clients.index', compact('clients'));
    }

    public function create()
    { 
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom'        => 'required|string|max:100',
            'prenom'     => 'required|string|max:100',
            'telephone'  => 'required|string|max:20',
            'telephone2' => 'nullable|string|max:20',
            'email'      => 'nullable|email|max:150',
            'adresse'    => 'nullable|string|max:500',
            'notes'      => 'nullable|string|max:1000',
        ]);

        $data['created_by'] = auth()->id();
        $client = Client::create($data);

        return redirect()->route('clients.show', $client)
            ->with('success', "Client {$client->nom_complet} créé avec succès.");
    }

    public function show(Client $client)
    {
        $client->load(['vehicles.repairOrders.assignedTo', 'repairOrders.vehicle']);
        return view('clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'nom'        => 'required|string|max:100',
            'prenom'     => 'required|string|max:100',
            'telephone'  => 'required|string|max:20',
            'telephone2' => 'nullable|string|max:20',
            'email'      => 'nullable|email|max:150',
            'adresse'    => 'nullable|string|max:500',
            'notes'      => 'nullable|string|max:1000',
        ]);

        $client->update($data);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client mis à jour.');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Client archivé.');
    }

    public function search(Request $request)
    {
        $results = Client::with('vehicles')
            ->search($request->get('q', ''))
            ->limit(10)
            ->get(['id', 'nom', 'prenom', 'telephone']);

        return response()->json($results);
    }
}
