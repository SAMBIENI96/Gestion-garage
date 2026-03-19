@extends('layouts.app')
@section('title', 'Véhicules')
@section('page-title', 'Véhicules')

@push('topbar-actions')
    <a href="{{ route('vehicles.create') }}" class="btn btn-primary btn-sm">+ Ajouter véhicule</a>
@endpush

@section('content')
<div class="card" style="margin-bottom:20px;padding:16px">
    <form method="GET" style="display:flex;gap:12px;align-items:flex-end">
        <div style="flex:1">
            <label class="form-label">Recherche</label>
            <input class="form-input" type="text" name="q" value="{{ request('q') }}"
                   placeholder="Immatriculation, marque, modèle, nom client…">
        </div>
        <button type="submit" class="btn btn-secondary">Chercher</button>
        @if(request('q'))
            <a href="{{ route('vehicles.index') }}" class="btn btn-ghost">Effacer</a>
        @endif
    </form>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Immatriculation</th>
                    <th>Marque / Modèle</th>
                    <th>Année</th>
                    <th>Couleur</th>
                    <th>Kilométrage</th>
                    <th>Client</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vehicles as $vehicle)
                <tr>
                    <td>
                        <a href="{{ route('vehicles.show', $vehicle) }}" style="color:var(--accent);text-decoration:none;font-weight:700;font-family:var(--font-display)">
                            {{ $vehicle->immatriculation }}
                        </a>
                    </td>
                    <td>
                        <div style="font-weight:500;color:var(--text-primary)">{{ $vehicle->marque }}</div>
                        <div style="font-size:12px;color:var(--text-muted)">{{ $vehicle->modele }}</div>
                    </td>
                    <td style="color:var(--text-muted)">{{ $vehicle->annee ?? '—' }}</td>
                    <td style="color:var(--text-muted)">{{ $vehicle->couleur ?? '—' }}</td>
                    <td style="color:var(--text-muted)">
                        {{ $vehicle->kilometrage ? number_format($vehicle->kilometrage, 0, ',', ' ').' km' : '—' }}
                    </td>
                    <td>
                        <a href="{{ route('clients.show', $vehicle->client) }}" style="color:var(--text-primary);text-decoration:none">
                            {{ $vehicle->client->nom_complet }}
                        </a>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-ghost btn-sm">Historique</a>
                            <a href="{{ route('repairs.create', ['vehicle_id' => $vehicle->id, 'client_id' => $vehicle->client_id]) }}" class="btn btn-secondary btn-sm">+ Ordre</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:40px">Aucun véhicule trouvé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $vehicles->links() }}</div>
</div>
@endsection
