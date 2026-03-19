@extends('layouts.app')
@section('title', 'Modifier ' . $repair->numero)
@section('page-title', 'Modifier ' . $repair->numero)

@section('content')
<form action="{{ route('repairs.update', $repair) }}" method="POST">
@csrf
@method('PUT')
<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start">

    <div style="display:flex;flex-direction:column;gap:20px">
        <div class="card">
            <div class="card-title">Véhicule</div>
            <div style="background:var(--bg-surface);border-radius:10px;padding:14px;display:flex;gap:16px">
                <div style="flex:1">
                    <div class="form-label">Client</div>
                    <div style="color:var(--text-primary);font-weight:500">{{ $repair->client->nom_complet }}</div>
                </div>
                <div style="flex:1">
                    <div class="form-label">Véhicule</div>
                    <div style="color:var(--accent);font-weight:700">{{ $repair->vehicle->immatriculation }}</div>
                    <div style="font-size:12px;color:var(--text-muted)">{{ $repair->vehicle->marque }} {{ $repair->vehicle->modele }}</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-title">Détails</div>
            <div class="form-group">
                <label class="form-label">Description de la panne *</label>
                <textarea class="form-textarea" name="description_panne" rows="4" required>{{ old('description_panne', $repair->description_panne) }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Pièces estimées</label>
                <textarea class="form-textarea" name="pieces_estimees" rows="3">{{ old('pieces_estimees', $repair->pieces_estimees) }}</textarea>
            </div>
            <div class="form-group" style="margin-bottom:0">
                <label class="form-label">Coût estimé (FCFA)</label>
                <input class="form-input" type="number" name="cout_estime" value="{{ old('cout_estime', $repair->cout_estime) }}" min="0" step="500">
            </div>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:16px">
        <div class="card">
            <div class="card-title">Paramètres</div>
            <div class="form-group">
                <label class="form-label">Urgence</label>
                <select class="form-select" name="urgence">
                    <option value="normal" {{ old('urgence',$repair->urgence)=='normal' ? 'selected':'' }}>Normal</option>
                    <option value="urgent" {{ old('urgence',$repair->urgence)=='urgent' ? 'selected':'' }}>🔴 Urgent</option>
                    <option value="vip"    {{ old('urgence',$repair->urgence)=='vip'    ? 'selected':'' }}>⭐ VIP</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Date d'entrée</label>
                <input class="form-input" type="date" name="date_entree" value="{{ old('date_entree', $repair->date_entree->format('Y-m-d')) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Date de sortie prévue</label>
                <input class="form-input" type="date" name="date_sortie_prevue" value="{{ old('date_sortie_prevue', $repair->date_sortie_prevue?->format('Y-m-d')) }}">
            </div>
            <div class="form-group" style="margin-bottom:0">
                <label class="form-label">Kilométrage</label>
                <input class="form-input" type="number" name="kilometrage_entree" value="{{ old('kilometrage_entree', $repair->kilometrage_entree) }}" min="0">
            </div>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px">Enregistrer</button>
        <a href="{{ route('repairs.show', $repair) }}" class="btn btn-ghost" style="width:100%;justify-content:center">Annuler</a>
    </div>
</div>
</form>
@endsection
