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
        <div class="stat-icon tone-accent"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085"/></svg></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Nouveaux</div>
        <div class="stat-value">{{ $stats['nouveaux'] }}</div>
        <div class="stat-icon tone-blue"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Terminés aujourd'hui</div>
        <div class="stat-value">{{ $stats['termines_jour'] }}</div>
        <div class="stat-icon tone-green"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Clients total</div>
        <div class="stat-value">{{ $stats['clients_total'] }}</div>
        <div class="stat-icon tone-gray"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg></div>
    </div>
    @if(auth()->user()->isPatron())
    <div class="stat-card">
        <div class="stat-label">Revenus du jour</div>
        <div class="stat-value" style="font-size:22px">{{ number_format($revenus['jour'], 0, ',', ' ') }} F</div>
        <div class="stat-icon tone-yellow"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Revenus semaine</div>
        <div class="stat-value" style="font-size:22px">{{ number_format($revenus['semaine'], 0, ',', ' ') }} F</div>
        <div class="stat-icon tone-yellow"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/></svg></div>
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
