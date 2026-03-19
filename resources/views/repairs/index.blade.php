@extends('layouts.app')
@section('title', 'Ordres de réparation')
@section('page-title', 'Ordres de réparation')

@push('topbar-actions')
    <a href="{{ route('repairs.create') }}" class="btn btn-primary btn-sm">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Nouvel ordre
    </a>
@endpush

@section('content')
<!-- Filtres -->
<div class="card" style="margin-bottom:20px; padding:16px">
    <form method="GET" style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end">
        <div style="flex:1; min-width:200px">
            <label class="form-label">Recherche</label>
            <input class="form-input" type="text" name="q" value="{{ request('q') }}"
                   placeholder="N° ordre, client, immatriculation…">
        </div>
        <div>
            <label class="form-label">Statut</label>
            <select class="form-select" name="statut" style="width:180px">
                <option value="">Tous les statuts</option>
                <option value="nouveau" {{ request('statut')=='nouveau' ? 'selected':'' }}>Nouveau</option>
                <option value="en_attente_pieces" {{ request('statut')=='en_attente_pieces' ? 'selected':'' }}>En attente pièces</option>
                <option value="en_cours" {{ request('statut')=='en_cours' ? 'selected':'' }}>En cours</option>
                <option value="termine" {{ request('statut')=='termine' ? 'selected':'' }}>Terminé</option>
                <option value="probleme" {{ request('statut')=='probleme' ? 'selected':'' }}>Problème</option>
            </select>
        </div>
        <div>
            <label class="form-label">Urgence</label>
            <select class="form-select" name="urgence" style="width:140px">
                <option value="">Toutes</option>
                <option value="vip" {{ request('urgence')=='vip' ? 'selected':'' }}>VIP</option>
                <option value="urgent" {{ request('urgence')=='urgent' ? 'selected':'' }}>Urgent</option>
                <option value="normal" {{ request('urgence')=='normal' ? 'selected':'' }}>Normal</option>
            </select>
        </div>
        <button type="submit" class="btn btn-secondary">Filtrer</button>
        @if(request()->hasAny(['q','statut','urgence']))
            <a href="{{ route('repairs.index') }}" class="btn btn-ghost">Effacer</a>
        @endif
    </form>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Urgence</th>
                    <th>N° Ordre</th>
                    <th>Client</th>
                    <th>Véhicule</th>
                    <th>Description</th>
                    <th>Date entrée</th>
                    <th>Statut</th>
                    <th>Mécanicien</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td style="text-align:center">
                        <span class="urgence-dot {{ $order->urgence }}" title="{{ $order->urgence_label }}"></span>
                    </td>
                    <td>
                        <a href="{{ route('repairs.show', $order) }}" style="color:var(--accent);text-decoration:none;font-weight:600;font-family:var(--font-display)">
                            {{ $order->numero }}
                        </a>
                    </td>
                    <td>
                        <a href="{{ route('clients.show', $order->client) }}" style="color:var(--text-primary);text-decoration:none">
                            {{ $order->client->nom_complet }}
                        </a>
                        <div style="font-size:11px;color:var(--text-muted)">{{ $order->client->telephone }}</div>
                    </td>
                    <td>
                        <div style="font-weight:500">{{ $order->vehicle->immatriculation }}</div>
                        <div style="font-size:11px;color:var(--text-muted)">{{ $order->vehicle->marque }} {{ $order->vehicle->modele }}</div>
                    </td>
                    <td style="max-width:220px">
                        <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--text-secondary)">
                            {{ $order->description_panne }}
                        </div>
                    </td>
                    <td style="color:var(--text-muted);white-space:nowrap">{{ $order->date_entree->format('d/m/Y') }}</td>
                    <td><span class="badge badge-{{ $order->statut_color }}">{{ $order->statut_label }}</span></td>
                    <td style="color:var(--text-muted)">{{ $order->assignedTo?->name ?? '—' }}</td>
                    <td>
                        <a href="{{ route('repairs.show', $order) }}" class="btn btn-ghost btn-sm">Voir</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center;color:var(--text-muted);padding:40px">
                        Aucun ordre de réparation trouvé.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">
        {{ $orders->links() }}
    </div>
</div>
@endsection
