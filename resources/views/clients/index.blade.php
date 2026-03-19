@extends('layouts.app')
@section('title', 'Clients')
@section('page-title', 'Clients')

@push('topbar-actions')
    <a href="{{ route('clients.create') }}" class="btn btn-primary btn-sm">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Nouveau client
    </a>
@endpush

@section('content')
<div class="card" style="margin-bottom:20px; padding:16px">
    <form method="GET" style="display:flex; gap:12px; align-items:flex-end">
        <div style="flex:1">
            <label class="form-label">Recherche rapide</label>
            <input class="form-input" type="text" name="q" value="{{ request('q') }}"
                   placeholder="Nom, prénom, téléphone, email…" autofocus>
        </div>
        <button type="submit" class="btn btn-secondary">Rechercher</button>
        @if(request('q'))
            <a href="{{ route('clients.index') }}" class="btn btn-ghost">Effacer</a>
        @endif
    </form>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Téléphone</th>
                    <th>Email</th>
                    <th>Véhicules</th>
                    <th>Interventions</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $client)
                <tr>
                    <td>
                        <a href="{{ route('clients.show', $client) }}" style="color:var(--text-primary);text-decoration:none;font-weight:500">
                            {{ $client->nom_complet }}
                        </a>
                    </td>
                    <td style="color:var(--text-secondary)">{{ $client->telephone }}</td>
                    <td style="color:var(--text-muted)">{{ $client->email ?? '—' }}</td>
                    <td>
                        <span style="color:var(--blue); font-weight:500">{{ $client->vehicles->count() }}</span>
                    </td>
                    <td>
                        <span style="color:var(--text-secondary)">{{ $client->repair_orders_count }}</span>
                    </td>
                    <td>
                        <div style="display:flex; gap:6px">
                            <a href="{{ route('clients.show', $client) }}" class="btn btn-ghost btn-sm">Fiche</a>
                            <a href="{{ route('repairs.create', ['client_id' => $client->id]) }}" class="btn btn-secondary btn-sm">+ Ordre</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:40px">Aucun client trouvé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $clients->links() }}</div>
</div>
@endsection
