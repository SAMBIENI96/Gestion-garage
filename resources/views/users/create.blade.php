@extends('layouts.app')
@section('title', 'Créer un employé')
@section('page-title', 'Créer un compte employé')

@section('content')
<div style="max-width:520px">
<form action="{{ route('users.store') }}" method="POST">
@csrf
<div class="card" style="margin-bottom:16px">
    <div class="card-title">Informations du compte</div>
    <div class="form-group">
        <label class="form-label">Nom complet *</label>
        <input class="form-input" type="text" name="name" value="{{ old('name') }}" required placeholder="Prénom Nom">
    </div>
    <div class="form-group">
        <label class="form-label">Email *</label>
        <input class="form-input" type="email" name="email" value="{{ old('email') }}" required placeholder="employe@garage.com">
    </div>
    <div class="form-group">
        <label class="form-label">Téléphone</label>
        <input class="form-input" type="tel" name="phone" value="{{ old('phone') }}" placeholder="+229 …">
    </div>
    <div class="form-group">
        <label class="form-label">Rôle *</label>
        <select class="form-select" name="role" required>
            <option value="accueil" {{ old('role')=='accueil' ? 'selected':'' }}>Agent d'accueil</option>
            <option value="mecanicien" {{ old('role')=='mecanicien' ? 'selected':'' }}>Mécanicien</option>
        </select>
        <div style="font-size:11px;color:var(--text-muted);margin-top:4px">
            Accueil : gestion clients & ordres. Mécanicien : accès mobile uniquement.
        </div>
    </div>
    <div class="form-grid-2">
        <div class="form-group">
            <label class="form-label">Mot de passe *</label>
            <input class="form-input" type="password" name="password" required placeholder="Min. 8 caractères">
        </div>
        <div class="form-group">
            <label class="form-label">Confirmer *</label>
            <input class="form-input" type="password" name="password_confirmation" required placeholder="Répéter">
        </div>
    </div>
</div>
<div style="display:flex;gap:10px">
    <button type="submit" class="btn btn-primary">Créer le compte</button>
    <a href="{{ route('users.index') }}" class="btn btn-ghost">Annuler</a>
</div>
</form>
</div>
@endsection
