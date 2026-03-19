@extends('layouts.app')
@section('title', 'Ajouter un véhicule')
@section('page-title', 'Ajouter un véhicule')

@section('content')
<div style="max-width:640px">
<form action="{{ route('vehicles.store') }}" method="POST">
@csrf
<div class="card" style="margin-bottom:16px">
    <div class="card-title">Informations du véhicule</div>

    <div class="form-group">
        <label class="form-label">Client propriétaire *</label>
        <select class="form-select" name="client_id" required>
            <option value="">— Sélectionner un client —</option>
            @foreach($clients as $c)
                <option value="{{ $c->id }}" {{ (old('client_id', $client?->id) == $c->id) ? 'selected':'' }}>
                    {{ $c->nom_complet }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-grid-2">
        <div class="form-group">
            <label class="form-label">Immatriculation *</label>
            <input class="form-input" type="text" name="immatriculation"
                   value="{{ old('immatriculation') }}" required placeholder="ex: BJ-1234-AA" style="text-transform:uppercase">
        </div>
        <div class="form-group">
            <label class="form-label">Couleur</label>
            <input class="form-input" type="text" name="couleur" value="{{ old('couleur') }}" placeholder="Blanc, noir, rouge…">
        </div>
        <div class="form-group">
            <label class="form-label">Marque *</label>
            <input class="form-input" type="text" name="marque" value="{{ old('marque') }}" required placeholder="Toyota, Peugeot…">
        </div>
        <div class="form-group">
            <label class="form-label">Modèle *</label>
            <input class="form-input" type="text" name="modele" value="{{ old('modele') }}" required placeholder="Corolla, 206…">
        </div>
        <div class="form-group">
            <label class="form-label">Année</label>
            <input class="form-input" type="number" name="annee" value="{{ old('annee') }}"
                   min="1950" max="{{ date('Y') + 1 }}" placeholder="{{ date('Y') }}">
        </div>
        <div class="form-group">
            <label class="form-label">Kilométrage</label>
            <input class="form-input" type="number" name="kilometrage" value="{{ old('kilometrage') }}" min="0" placeholder="ex: 85000">
        </div>
    </div>

    <div class="form-group">
        <label class="form-label">Numéro de châssis</label>
        <input class="form-input" type="text" name="numero_chassis" value="{{ old('numero_chassis') }}" placeholder="VIN optionnel">
    </div>

    <div class="form-group" style="margin-bottom:0">
        <label class="form-label">Notes</label>
        <textarea class="form-textarea" name="notes" rows="2" placeholder="Remarques sur le véhicule…">{{ old('notes') }}</textarea>
    </div>
</div>

<div style="display:flex;gap:10px">
    <button type="submit" class="btn btn-primary">Enregistrer le véhicule</button>
    <a href="{{ $client ? route('clients.show', $client) : route('vehicles.index') }}" class="btn btn-ghost">Annuler</a>
</div>
</form>
</div>
@endsection

@push('scripts')
<script>
// Auto uppercase immatriculation
document.querySelector('[name="immatriculation"]').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});
</script>
@endpush
