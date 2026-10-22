@extends('layouts.app')
@section('title', 'Utilisateurs')
@section('page-title', 'Gestion des utilisateurs')

@push('topbar-actions')
    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">+ Ajouter employé</a>
@endpush

@section('content')
@if($pending->isNotEmpty())
<section class="card pending-card" aria-labelledby="pending-title">
    <div class="pending-head">
        <div>
            <h2 class="card-title" id="pending-title" style="margin:0">Demandes d'accès <span class="nav-badge" style="margin-left:6px">{{ $pending->count() }}</span></h2>
            <p class="card-sub" style="margin:4px 0 0">Ces personnes se sont inscrites. Choisissez leur rôle puis accordez l'accès, ou refusez la demande.</p>
        </div>
    </div>

    <ul class="request-list">
        @foreach($pending as $req)
        <li class="request">
            @if($req->avatar_url)
                <img class="avatar" src="{{ $req->avatar_url }}" alt="" referrerpolicy="no-referrer">
            @else
                <span class="avatar" aria-hidden="true">{{ $req->initiales }}</span>
            @endif
            <div class="request-info">
                <div class="request-name">{{ $req->name }}</div>
                <div class="request-meta">
                    {{ $req->email }}@if($req->phone) · {{ $req->phone }}@endif
                    · {{ $req->created_at?->diffForHumans() }}
                    · <span class="badge {{ $req->google_id ? 'badge-blue' : 'badge-gray' }}">{{ $req->google_id ? 'Google' : 'Formulaire' }}</span>
                </div>
            </div>
            <form action="{{ route('users.approve', $req) }}" method="POST" class="request-actions">
                @csrf
                <label class="sr-only" for="role-{{ $req->id }}">Rôle</label>
                <select class="form-select" name="role" id="role-{{ $req->id }}" style="width:auto">
                    <option value="accueil" @selected($req->role === 'accueil')>Agent d'accueil</option>
                    <option value="mecanicien" @selected($req->role === 'mecanicien')>Mécanicien</option>
                </select>
                <button type="submit" class="btn btn-primary btn-sm"><span>Accorder l'accès</span></button>
                <button type="submit" class="btn btn-ghost btn-sm" form="reject-{{ $req->id }}"><span>Refuser</span></button>
            </form>
            <form id="reject-{{ $req->id }}" action="{{ route('users.reject', $req) }}" method="POST" hidden
                  onsubmit="return confirm('Refuser cette demande d\'accès ?')">
                @csrf
                @method('DELETE')
            </form>
        </li>
        @endforeach
    </ul>
</section>
@endif

<div class="card">
    <div class="table-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Rôle</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>
                        <div style="font-weight:500;color:var(--text-primary)">{{ $user->name }}</div>
                        @if($user->id === auth()->id())
                            <span style="font-size:10px;color:var(--text-muted)">(vous)</span>
                        @endif
                    </td>
                    <td style="color:var(--text-muted)">{{ $user->email }}</td>
                    <td style="color:var(--text-muted)">{{ $user->phone ?? '—' }}</td>
                    <td><span class="role-badge {{ $user->role }}">{{ $user->role_label }}</span></td>
                    <td>
                        @if($user->is_active)
                            <span class="badge badge-green">Actif</span>
                        @else
                            <span class="badge badge-gray">Inactif</span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex;gap:6px">
                            @unless($user->isPatron())
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-ghost btn-sm">Modifier</a>
                            <form action="{{ route('users.toggleActive', $user) }}" method="POST" style="display:inline">@csrf
                                <button class="btn btn-sm {{ $user->is_active ? 'btn-danger' : 'btn-secondary' }}">
                                    {{ $user->is_active ? 'Désactiver' : 'Activer' }}
                                </button>
                            </form>
                            @endunless
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
