@extends('layouts.app')
@section('title', $client->nom_complet)
@section('page-title', $client->nom_complet)

@push('topbar-actions')
    <a href="{{ route('vehicles.create', ['client_id' => $client->id]) }}" class="btn btn-secondary btn-sm">+ Véhicule</a>
    <a href="{{ route('repairs.create', ['client_id' => $client->id]) }}" class="btn btn-primary btn-sm">+ Ordre de réparation</a>
@endpush

@section('content')
<div style="display:grid; grid-template-columns:300px 1fr; gap:20px; align-items:start">

    <!-- Infos client -->
    <div style="display:flex; flex-direction:column; gap:16px">
        <div class="card">
            <div style="text-align:center; padding-bottom:20px; border-bottom:1px solid var(--border); margin-bottom:20px">
                <div style="width:64px; height:64px; border-radius:50%; background:rgba(74,158,255,0.15); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:28px; color:var(--blue); margin:0 auto 12px">
                    {{ strtoupper(substr($client->prenom, 0, 1)) }}
                </div>
                <div style="font-family:var(--font-display); font-size:18px; font-weight:800; color:var(--text-primary)">{{ $client->nom_complet }}</div>
                <div style="font-size:12px; color:var(--text-muted); margin-top:4px">Client depuis {{ $client->created_at->format('d/m/Y') }}</div>
            </div>

            <div style="display:flex; flex-direction:column; gap:12px">
                <div>
                    <div class="form-label">Téléphone</div>
                    <div style="color:var(--text-primary)">{{ $client->telephone }}</div>
                    @if($client->telephone2)<div style="color:var(--text-muted); font-size:12px">{{ $client->telephone2 }}</div>@endif
                </div>
                @if($client->email)
                <div>
                    <div class="form-label">Email</div>
                    <div style="color:var(--text-primary)">{{ $client->email }}</div>
                </div>
                @endif
                @if($client->adresse)
                <div>
                    <div class="form-label">Adresse</div>
                    <div style="color:var(--text-secondary); font-size:13px">{{ $client->adresse }}</div>
                </div>
                @endif
                @if($client->notes)
                <div>
                    <div class="form-label">Notes internes</div>
                    <div style="color:var(--text-muted); font-size:13px">{{ $client->notes }}</div>
                </div>
                @endif
            </div>

            <div style="margin-top:20px; padding-top:20px; border-top:1px solid var(--border); display:flex; gap:8px">
                <a href="{{ route('clients.edit', $client) }}" class="btn btn-ghost btn-sm" style="flex:1; justify-content:center">Modifier</a>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="card">
            <div class="card-title">Statistiques</div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; text-align:center">
                <div style="background:var(--bg-surface); border-radius:8px; padding:12px">
                    <div style="font-family:var(--font-display); font-size:24px; font-weight:800; color:var(--blue)">{{ $client->vehicles->count() }}</div>
                    <div style="font-size:11px; color:var(--text-muted)">Véhicules</div>
                </div>
                <div style="background:var(--bg-surface); border-radius:8px; padding:12px">
                    <div style="font-family:var(--font-display); font-size:24px; font-weight:800; color:var(--accent)">{{ $client->repairOrders->count() }}</div>
                    <div style="font-size:11px; color:var(--text-muted)">Interventions</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Véhicules et historique -->
    <div style="display:flex; flex-direction:column; gap:20px">

        <!-- Véhicules -->
        <div class="card">
            <div style="display:flex; justify-content:space-between; margin-bottom:16px">
                <div class="card-title" style="margin:0">Véhicules</div>
                <a href="{{ route('vehicles.create', ['client_id' => $client->id]) }}" class="btn btn-ghost btn-sm">+ Ajouter</a>
            </div>
            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap:12px">
                @forelse($client->vehicles as $vehicle)
                <a href="{{ route('vehicles.show', $vehicle) }}" style="text-decoration:none">
                    <div style="background:var(--bg-surface); border:1px solid var(--border); border-radius:10px; padding:14px; transition:border-color 0.15s"
                         onmouseover="this.style.borderColor='var(--border-mid)'" onmouseout="this.style.borderColor='var(--border)'">
                        <div style="font-weight:700; color:var(--accent); font-family:var(--font-display); font-size:15px">{{ $vehicle->immatriculation }}</div>
                        <div style="font-size:13px; color:var(--text-primary); margin-top:4px">{{ $vehicle->marque }} {{ $vehicle->modele }}</div>
                        <div style="font-size:11px; color:var(--text-muted); margin-top:2px">
                            {{ $vehicle->annee ?? '—' }} • {{ $vehicle->couleur ?? '—' }}
                        </div>
                        @if($vehicle->kilometrage)
                        <div style="font-size:11px; color:var(--text-muted)">{{ number_format($vehicle->kilometrage, 0, ',', ' ') }} km</div>
                        @endif
                    </div>
                </a>
                @empty
                <div style="padding:24px; text-align:center; color:var(--text-muted); font-size:13px">
                    Aucun véhicule enregistré.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Historique interventions -->
        <div class="card">
            <div class="card-title">Historique des interventions</div>
            <div class="table-wrap">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>N° Ordre</th>
                            <th>Véhicule</th>
                            <th>Description</th>
                            <th>Date</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($client->repairOrders->sortByDesc('date_entree') as $order)
                        <tr>
                            <td>
                                <a href="{{ route('repairs.show', $order) }}" style="color:var(--accent);text-decoration:none;font-weight:600">{{ $order->numero }}</a>
                            </td>
                            <td style="color:var(--text-muted)">{{ $order->vehicle?->immatriculation }}</td>
                            <td style="max-width:240px">
                                <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--text-secondary);font-size:13px">
                                    {{ $order->description_panne }}
                                </div>
                            </td>
                            <td style="color:var(--text-muted);white-space:nowrap">{{ $order->date_entree->format('d/m/Y') }}</td>
                            <td><span class="badge badge-{{ $order->statut_color }}">{{ $order->statut_label }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:24px">Aucune intervention.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
