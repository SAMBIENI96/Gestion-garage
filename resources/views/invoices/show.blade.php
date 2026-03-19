@extends('layouts.app')
@section('title', $invoice->numero)
@section('page-title', $invoice->numero)

@push('topbar-actions')
    <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="btn btn-ghost btn-sm">🖨 Imprimer</a>
    @if($invoice->statut === 'brouillon')
        <form action="{{ route('invoices.valider', $invoice) }}" method="POST" style="display:inline">@csrf
            <button class="btn btn-primary btn-sm">Valider la facture</button>
        </form>
    @elseif($invoice->statut === 'validee')
        <form action="{{ route('invoices.marquerPayee', $invoice) }}" method="POST" style="display:inline">@csrf
            <button class="btn btn-primary btn-sm" style="background:var(--green)">Marquer payée</button>
        </form>
    @endif
@endpush

@section('content')
<div style="max-width:800px">
<div class="card">
    <!-- En-tête -->
    <div style="display:flex;justify-content:space-between;align-items:flex-start;padding-bottom:24px;border-bottom:1px solid var(--border);margin-bottom:24px">
        <div>
            <div style="font-family:var(--font-display);font-size:28px;font-weight:800;color:var(--text-primary)">
                Auto<span style="color:var(--accent)">Gest</span>
            </div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:4px">Gestion de garage professionnelle</div>
        </div>
        <div style="text-align:right">
            <div style="font-family:var(--font-display);font-size:22px;font-weight:800;color:var(--accent)">{{ $invoice->numero }}</div>
            <div style="font-size:13px;color:var(--text-muted)">{{ $invoice->date_facture->format('d/m/Y') }}</div>
            <span class="badge badge-{{ $invoice->statut === 'payee' ? 'green' : ($invoice->statut === 'validee' ? 'blue' : 'gray') }}" style="margin-top:6px">
                {{ $invoice->statut_label }}
            </span>
        </div>
    </div>

    <!-- Client & Véhicule -->
    <div class="form-grid-2" style="margin-bottom:24px">
        <div>
            <div class="form-label">Facturé à</div>
            <div style="font-weight:600;color:var(--text-primary)">{{ $invoice->client->nom_complet }}</div>
            <div style="font-size:13px;color:var(--text-muted)">{{ $invoice->client->telephone }}</div>
            @if($invoice->client->adresse)
            <div style="font-size:12px;color:var(--text-muted)">{{ $invoice->client->adresse }}</div>
            @endif
        </div>
        <div>
            <div class="form-label">Véhicule</div>
            <div style="font-weight:600;color:var(--text-primary)">{{ $invoice->repairOrder->vehicle->immatriculation }}</div>
            <div style="font-size:13px;color:var(--text-muted)">{{ $invoice->repairOrder->vehicle->marque }} {{ $invoice->repairOrder->vehicle->modele }}</div>
            <div style="font-size:12px;color:var(--text-muted)">Ordre : {{ $invoice->repairOrder->numero }}</div>
        </div>
    </div>

    <!-- Lignes -->
    <div class="table-wrap" style="margin-bottom:24px">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Type</th>
                    <th style="text-align:right">Qté</th>
                    <th style="text-align:right">Prix unit.</th>
                    <th style="text-align:right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->lignes as $ligne)
                <tr>
                    <td>{{ $ligne['description'] }}</td>
                    <td>
                        <span class="badge {{ $ligne['type'] === 'main_oeuvre' ? 'badge-blue' : ($ligne['type'] === 'piece' ? 'badge-orange' : 'badge-gray') }}">
                            {{ $ligne['type'] === 'main_oeuvre' ? 'Main d\'œuvre' : ($ligne['type'] === 'piece' ? 'Pièce' : 'Autre') }}
                        </span>
                    </td>
                    <td style="text-align:right">{{ $ligne['quantite'] }}</td>
                    <td style="text-align:right;color:var(--text-muted)">{{ number_format($ligne['prix_unitaire'], 0, ',', ' ') }} F</td>
                    <td style="text-align:right;font-weight:500;color:var(--text-primary)">
                        {{ number_format($ligne['quantite'] * $ligne['prix_unitaire'], 0, ',', ' ') }} F
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Totaux -->
    <div style="display:flex;justify-content:flex-end">
        <div style="min-width:260px">
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
                <span style="color:var(--text-muted)">Sous-total</span>
                <span style="color:var(--text-primary)">{{ number_format($invoice->sous_total, 0, ',', ' ') }} F</span>
            </div>
            @if($invoice->remise_pct > 0)
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
                <span style="color:var(--text-muted)">Remise ({{ $invoice->remise_pct }}%)</span>
                <span style="color:var(--red)">- {{ number_format($invoice->remise_montant, 0, ',', ' ') }} F</span>
            </div>
            @endif
            <div style="display:flex;justify-content:space-between;padding:16px 0 0;align-items:center">
                <span style="font-weight:700;color:var(--text-primary);font-size:16px">TOTAL TTC</span>
                <span style="font-family:var(--font-display);font-size:28px;font-weight:800;color:var(--accent)">
                    {{ number_format($invoice->total_ttc, 0, ',', ' ') }} F
                </span>
            </div>
        </div>
    </div>

    @if($invoice->notes)
    <div style="margin-top:24px;padding-top:20px;border-top:1px solid var(--border)">
        <div class="form-label">Notes</div>
        <p style="color:var(--text-secondary);font-size:13px">{{ $invoice->notes }}</p>
    </div>
    @endif
</div>
</div>
@endsection
