@extends('layouts.app')
@section('title', 'Modifier véhicule')
@section('page-title', 'Modifier ' . $vehicle->immatriculation)

@section('content')
<div style="max-width:640px">
<form action="{{ route('vehicles.update', $vehicle) }}" method="POST">
@csrf
@method('PUT')
<div class="card" style="margin-bottom:16px">
    <div class="card-title">Informations du véhicule</div>
    <div class="form-group">
        <label class="form-label">Client propriétaire *</label>
        <select class="form-select" name="client_id" required>
            @foreach($clients as $c)
                <option value="{{ $c->id }}" {{ old('client_id', $vehicle->client_id) == $c->id ? 'selected':'' }}>
                    {{ $c->nom_complet }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="form-grid-2">
        <div class="form-group">
            <label class="form-label">Immatriculation *</label>
            <input class="form-input" type="text" name="immatriculation" value="{{ old('immatriculation', $vehicle->immatriculation) }}" required style="text-transform:uppercase">
        </div>
        <div class="form-group">
            <label class="form-label">Couleur</label>
            <input class="form-input" type="text" name="couleur" value="{{ old('couleur', $vehicle->couleur) }}">
        </div>
        <div class="form-group">
            <label class="form-label">Marque *</label>
            <input class="form-input" type="text" name="marque" value="{{ old('marque', $vehicle->marque) }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">Modèle *</label>
            <input class="form-input" type="text" name="modele" value="{{ old('modele', $vehicle->modele) }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">Année</label>
            <input class="form-input" type="number" name="annee" value="{{ old('annee', $vehicle->annee) }}" min="1950" max="{{ date('Y')+1 }}">
        </div>
        <div class="form-group">
            <label class="form-label">Kilométrage</label>
            <input class="form-input" type="number" name="kilometrage" value="{{ old('kilometrage', $vehicle->kilometrage) }}" min="0">
        </div>
    </div>
    <div class="form-group">
        <label class="form-label">Numéro de châssis</label>
        <input class="form-input" type="text" name="numero_chassis" value="{{ old('numero_chassis', $vehicle->numero_chassis) }}">
    </div>
    <div class="form-group" style="margin-bottom:0">
        <label class="form-label">Notes</label>
        <textarea class="form-textarea" name="notes" rows="2">{{ old('notes', $vehicle->notes) }}</textarea>
    </div>
</div>
<div style="display:flex;gap:10px">
    <button type="submit" class="btn btn-primary">Enregistrer</button>
    <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-ghost">Annuler</a>
</div>
</form>
</div>
@endsection
