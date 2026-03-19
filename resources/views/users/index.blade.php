@extends('layouts.app')
@section('title', 'Utilisateurs')
@section('page-title', 'Gestion des utilisateurs')

@push('topbar-actions')
    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">+ Ajouter employé</a>
@endpush

@section('content')
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
