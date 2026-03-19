@extends('layouts.app')
@section('title', 'Nouveau client')
@section('page-title', 'Nouveau client')

@section('content')
<div style="max-width:680px">
<form action="{{ route('clients.store') }}" method="POST">
@csrf
<div class="card" style="margin-bottom:16px">
    <div class="card-title">Informations personnelles</div>
    <div class="form-grid-2">
        <div class="form-group">
            <label class="form-label">Prénom *</label>
            <input class="form-input" type="text" name="prenom" value="{{ old('prenom') }}" required autofocus placeholder="Jean">
        </div>
        <div class="form-group">
            <label class="form-label">Nom *</label>
            <input class="form-input" type="text" name="nom" value="{{ old('nom') }}" required placeholder="DUPONT">
        </div>
        <div class="form-group">
            <label class="form-label">Téléphone *</label>
            <input class="form-input" type="tel" name="telephone" value="{{ old('telephone') }}" required placeholder="+229 00 00 00 00">
        </div>
        <div class="form-group">
            <label class="form-label">Téléphone 2</label>
            <input class="form-input" type="tel" name="telephone2" value="{{ old('telephone2') }}" placeholder="Optionnel">
        </div>
        <div class="form-group">
            <label class="form-label">Email</label>
            <input class="form-input" type="email" name="email" value="{{ old('email') }}" placeholder="jean@exemple.com">
        </div>
    </div>
    <div class="form-group">
        <label class="form-label">Adresse</label>
        <textarea class="form-textarea" name="adresse" rows="2" placeholder="Quartier, ville…">{{ old('adresse') }}</textarea>
    </div>
    <div class="form-group" style="margin-bottom:0">
        <label class="form-label">Notes internes</label>
        <textarea class="form-textarea" name="notes" rows="2" placeholder="Remarques internes (client habituel, préférences…)">{{ old('notes') }}</textarea>
    </div>
</div>
<div style="display:flex; gap:10px">
    <button type="submit" class="btn btn-primary">Créer le client</button>
    <a href="{{ route('clients.index') }}" class="btn btn-ghost">Annuler</a>
</div>
</form>
</div>
@endsection
