@extends('layouts.app')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')

@push('topbar-actions')
    <a href="{{ route('repairs.create') }}" class="btn btn-primary btn-sm">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
        </svg>
        Nouvel ordre
    </a>
@endpush

@section('content')
<!-- Stats -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-label">En cours</div>
        <div class="stat-value">{{ $stats['en_cours'] }}</div>
        <div class="stat-icon" style="background:rgba(232,98,42,0.15)">🔧</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Nouveaux</div>
        <div class="stat-value">{{ $stats['nouveaux'] }}</div>
        <div class="stat-icon" style="background:rgba(74,158,255,0.15)">📋</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Terminés aujourd'hui</div>
        <div class="stat-value">{{ $stats['termines_jour'] }}</div>
        <div class="stat-icon" style="background:rgba(62,207,142,0.15)">✅</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Clients total</div>
        <div class="stat-value">{{ $stats['clients_total'] }}</div>
        <div class="stat-icon" style="background:rgba(139,145,168,0.1)">👥</div>
    </div>
    @if(auth()->user()->isPatron())
    <div class="stat-card">
        <div class="stat-label">Revenus du jour</div>
        <div class="stat-value" style="font-size:22px">{{ number_format($revenus['jour'], 0, ',', ' ') }} F</div>
        <div class="stat-icon" style="background:rgba(245,200,66,0.15)">💰</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Revenus semaine</div>
        <div class="stat-value" style="font-size:22px">{{ number_format($revenus['semaine'], 0, ',', ' ') }} F</div>
        <div class="stat-icon" style="background:rgba(245,200,66,0.15)">📈</div>
    </div>
    @endif
</div>

<div style="display:grid; grid-template-columns: 1fr {{ auth()->user()->isPatron() ? '280px' : '' }}; gap:20px; align-items:start">

    <!-- Ordres récents -->
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px">
            <div class="card-title" style="margin:0">Ordres récents</div>
            <a href="{{ route('repairs.index') }}" class="btn btn-ghost btn-sm">Voir tout</a>
        </div>
        <div class="table-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Numéro</th>
                        <th>Client</th>
                        <th>Véhicule</th>
                        <th>Statut</th>
                        <th>Mécanicien</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ordresRecents as $ordre)
                    <tr>
                        <td>
                            <a href="{{ route('repairs.show', $ordre) }}" style="color:var(--accent); text-decoration:none; font-weight:500">
                                {{ $ordre->numero }}
                            </a>
                            @if($ordre->urgence !== 'normal')
                                <span class="urgence-dot {{ $ordre->urgence }}" style="margin-left:6px"></span>
                            @endif
                        </td>
                        <td>{{ $ordre->client->nom_complet }}</td>
                        <td style="color:var(--text-muted)">{{ $ordre->vehicle->immatriculation }}</td>
                        <td>
                            <span class="badge badge-{{ $ordre->statut_color }}">{{ $ordre->statut_label }}</span>
                        </td>
                        <td style="color:var(--text-muted)">
                            {{ $ordre->assignedTo?->name ?? '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align:center; color:var(--text-muted); padding:32px">Aucun ordre de réparation</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(auth()->user()->isPatron())
    <!-- Charge mécaniciens -->
    <div class="card">
        <div class="card-title">Charge mécaniciens</div>
        @forelse($mecaniciens as $mec)
        <div style="margin-bottom:16px">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px">
                <span style="font-size:13px; color:var(--text-primary); font-weight:500">{{ $mec->name }}</span>
                <span style="font-size:12px; color:var(--text-muted)">{{ $mec->charge }} tâche(s)</span>
            </div>
            <div style="background:var(--bg-surface); border-radius:4px; height:6px; overflow:hidden">
                <div style="height:100%; width:{{ min($mec->charge * 20, 100) }}%; background:{{ $mec->charge >= 4 ? 'var(--red)' : ($mec->charge >= 2 ? 'var(--accent)' : 'var(--green)') }}; border-radius:4px; transition:width 0.5s"></div>
            </div>
        </div>
        @empty
        <p style="color:var(--text-muted); font-size:13px">Aucun mécanicien actif.</p>
        @endforelse
        <div style="margin-top:16px">
            <a href="{{ route('planning.index') }}" class="btn btn-secondary" style="width:100%; justify-content:center">
                Voir le planning complet
            </a>
        </div>
    </div>
    @endif

</div>
@endsection
