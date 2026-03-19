@extends('layouts.app')
@section('title', 'Modifier employé')
@section('page-title', 'Modifier ' . $user->name)

@section('content')
<div style="max-width:520px">
<form action="{{ route('users.update', $user) }}" method="POST">
@csrf
@method('PUT')
<div class="card" style="margin-bottom:16px">
    <div class="card-title">Informations du compte</div>
    <div class="form-group">
        <label class="form-label">Nom complet *</label>
        <input class="form-input" type="text" name="name" value="{{ old('name', $user->name) }}" required>
    </div>
    <div class="form-group">
        <label class="form-label">Email *</label>
        <input class="form-input" type="email" name="email" value="{{ old('email', $user->email) }}" required>
    </div>
    <div class="form-group">
        <label class="form-label">Téléphone</label>
        <input class="form-input" type="tel" name="phone" value="{{ old('phone', $user->phone) }}">
    </div>
    <div class="form-group">
        <label class="form-label">Rôle *</label>
        <select class="form-select" name="role" required>
            <option value="accueil"    {{ old('role', $user->role) == 'accueil'    ? 'selected':'' }}>Agent d'accueil</option>
            <option value="mecanicien" {{ old('role', $user->role) == 'mecanicien' ? 'selected':'' }}>Mécanicien</option>
        </select>
    </div>
    <div class="form-grid-2">
        <div class="form-group">
            <label class="form-label">Nouveau mot de passe</label>
            <input class="form-input" type="password" name="password" placeholder="Laisser vide = inchangé">
        </div>
        <div class="form-group">
            <label class="form-label">Confirmer</label>
            <input class="form-input" type="password" name="password_confirmation" placeholder="Répéter">
        </div>
    </div>
</div>
<div style="display:flex;gap:10px">
    <button type="submit" class="btn btn-primary">Enregistrer</button>
    <a href="{{ route('users.index') }}" class="btn btn-ghost">Annuler</a>
</div>
</form>
</div>
@endsection
