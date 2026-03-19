@extends('layouts.app')
@section('title', 'Modifier client')
@section('page-title', 'Modifier ' . $client->nom_complet)

@section('content')
<div style="max-width:680px">
<form action="{{ route('clients.update', $client) }}" method="POST">
@csrf
@method('PUT')
<div class="card" style="margin-bottom:16px">
    <div class="card-title">Informations personnelles</div>
    <div class="form-grid-2">
        <div class="form-group">
            <label class="form-label">Prénom *</label>
            <input class="form-input" type="text" name="prenom" value="{{ old('prenom', $client->prenom) }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">Nom *</label>
            <input class="form-input" type="text" name="nom" value="{{ old('nom', $client->nom) }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">Téléphone *</label>
            <input class="form-input" type="tel" name="telephone" value="{{ old('telephone', $client->telephone) }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">Téléphone 2</label>
            <input class="form-input" type="tel" name="telephone2" value="{{ old('telephone2', $client->telephone2) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Email</label>
            <input class="form-input" type="email" name="email" value="{{ old('email', $client->email) }}">
        </div>
    </div>
    <div class="form-group">
        <label class="form-label">Adresse</label>
        <textarea class="form-textarea" name="adresse" rows="2">{{ old('adresse', $client->adresse) }}</textarea>
    </div>
    <div class="form-group" style="margin-bottom:0">
        <label class="form-label">Notes internes</label>
        <textarea class="form-textarea" name="notes" rows="2">{{ old('notes', $client->notes) }}</textarea>
    </div>
</div>
<div style="display:flex;gap:10px">
    <button type="submit" class="btn btn-primary">Enregistrer</button>
    <a href="{{ route('clients.show', $client) }}" class="btn btn-ghost">Annuler</a>
</div>
</form>
</div>
@endsection
