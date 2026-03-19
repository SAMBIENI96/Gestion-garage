@extends('layouts.app')
@section('title', $vehicle->immatriculation)
@section('page-title', $vehicle->immatriculation)

@push('topbar-actions')
    <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-secondary btn-sm">Modifier</a>
    <a href="{{ route('repairs.create', ['vehicle_id' => $vehicle->id, 'client_id' => $vehicle->client_id]) }}" class="btn btn-primary btn-sm">
        + Ordre de réparation
    </a>
@endpush

@section('content')
<div style="display:grid;grid-template-columns:280px 1fr;gap:20px;align-items:start">

    <!-- Infos véhicule -->
    <div class="card">
        <div style="text-align:center;padding-bottom:20px;border-bottom:1px solid var(--border);margin-bottom:20px">
            <div style="font-size:48px;margin-bottom:8px">🚗</div>
            <div style="font-family:var(--font-display);font-size:22px;font-weight:800;color:var(--accent)">
                {{ $vehicle->immatriculation }}
            </div>
            <div style="font-size:14px;color:var(--text-secondary);margin-top:4px">
                {{ $vehicle->marque }} {{ $vehicle->modele }}
            </div>
        </div>
        <div style="display:flex;flex-direction:column;gap:12px">
            <div>
                <div class="form-label">Propriétaire</div>
                <a href="{{ route('clients.show', $vehicle->client) }}" style="color:var(--text-primary);text-decoration:none;font-weight:500">
                    {{ $vehicle->client->nom_complet }}
                </a>
            </div>
            <div>
                <div class="form-label">Année</div>
                <span style="color:var(--text-primary)">{{ $vehicle->annee ?? '—' }}</span>
            </div>
            <div>
                <div class="form-label">Couleur</div>
                <span style="color:var(--text-primary)">{{ $vehicle->couleur ?? '—' }}</span>
            </div>
            @if($vehicle->kilometrage)
            <div>
                <div class="form-label">Kilométrage</div>
                <span style="color:var(--text-primary)">{{ number_format($vehicle->kilometrage, 0, ',', ' ') }} km</span>
            </div>
            @endif
            @if($vehicle->numero_chassis)
            <div>
                <div class="form-label">N° Châssis</div>
                <span style="color:var(--text-muted);font-size:12px;font-family:monospace">{{ $vehicle->numero_chassis }}</span>
            </div>
            @endif
            @if($vehicle->notes)
            <div>
                <div class="form-label">Notes</div>
                <p style="color:var(--text-muted);font-size:13px">{{ $vehicle->notes }}</p>
            </div>
            @endif
        </div>

        <div style="margin-top:20px;padding-top:20px;border-top:1px solid var(--border)">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;text-align:center">
                <div style="background:var(--bg-surface);border-radius:8px;padding:10px">
                    <div style="font-family:var(--font-display);font-size:22px;font-weight:800;color:var(--accent)">
                        {{ $vehicle->repairOrders->count() }}
                    </div>
                    <div style="font-size:11px;color:var(--text-muted)">Interventions</div>
                </div>
                <div style="background:var(--bg-surface);border-radius:8px;padding:10px">
                    <div style="font-family:var(--font-display);font-size:22px;font-weight:800;color:var(--green)">
                        {{ $vehicle->repairOrders->where('statut','termine')->count() }}
                    </div>
                    <div style="font-size:11px;color:var(--text-muted)">Terminées</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Historique interventions -->
    <div class="card">
        <div class="card-title">Historique complet des interventions</div>
        @forelse($vehicle->repairOrders->sortByDesc('date_entree') as $order)
        <div style="border:1px solid var(--border);border-radius:10px;padding:16px;margin-bottom:12px;transition:border-color 0.15s"
             onmouseover="this.style.borderColor='var(--border-mid)'" onmouseout="this.style.borderColor='var(--border)'">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px">
                <div style="display:flex;align-items:center;gap:10px">
                    <a href="{{ route('repairs.show', $order) }}" style="color:var(--accent);text-decoration:none;font-family:var(--font-display);font-weight:700;font-size:15px">
                        {{ $order->numero }}
                    </a>
                    @if($order->urgence !== 'normal')
                        <span class="urgence-dot {{ $order->urgence }}"></span>
                    @endif
                </div>
                <div style="display:flex;align-items:center;gap:8px">
                    <span class="badge badge-{{ $order->statut_color }}">{{ $order->statut_label }}</span>
                    <span style="font-size:12px;color:var(--text-muted)">{{ $order->date_entree->format('d/m/Y') }}</span>
                </div>
            </div>
            <p style="color:var(--text-secondary);font-size:13px;line-height:1.6;margin-bottom:8px">{{ $order->description_panne }}</p>
            @if($order->assignedTo)
            <div style="font-size:12px;color:var(--text-muted)">
                Mécanicien : <span style="color:var(--text-secondary)">{{ $order->assignedTo->name }}</span>
            </div>
            @endif
            @if($order->notes->isNotEmpty())
            <div style="margin-top:10px;padding-top:10px;border-top:1px solid var(--border);font-size:12px;color:var(--text-muted)">
                {{ $order->notes->count() }} note(s) d'intervention
            </div>
            @endif
        </div>
        @empty
        <div style="padding:40px;text-align:center;color:var(--text-muted)">
            Aucune intervention enregistrée pour ce véhicule.
        </div>
        @endforelse
    </div>
</div>
@endsection
