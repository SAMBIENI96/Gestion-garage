@extends('layouts.app')
@section('title', 'Nouvel ordre de réparation')
@section('page-title', 'Nouvel ordre de réparation')

@section('content')
<form action="{{ route('repairs.store') }}" method="POST">
@csrf
<div style="display:grid; grid-template-columns: 1fr 360px; gap:20px; align-items:start">

    <!-- Colonne principale -->
    <div style="display:flex; flex-direction:column; gap:20px">

        <!-- Client & Véhicule -->
        <div class="card">
            <div class="card-title">Client & Véhicule</div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Client *</label>
                    <select class="form-select" name="client_id" id="client_id" required>
                        <option value="">— Sélectionner un client —</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}" {{ (old('client_id', $client?->id) == $c->id) ? 'selected' : '' }}>
                                {{ $c->nom_complet }} — {{ $c->telephone }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Véhicule *</label>
                    <select class="form-select" name="vehicle_id" id="vehicle_id" required>
                        <option value="">— Sélectionner d'abord un client —</option>
                        @if($client)
                            @foreach($client->vehicles as $v)
                                <option value="{{ $v->id }}" {{ old('vehicle_id', $vehicle?->id) == $v->id ? 'selected' : '' }}>
                                    {{ $v->immatriculation }} — {{ $v->marque }} {{ $v->modele }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <div style="display:flex; gap:10px; margin-top:-8px">
                <a href="{{ route('clients.create') }}" class="btn btn-ghost btn-sm">+ Nouveau client</a>
                <a href="#" id="add-vehicle-btn" class="btn btn-ghost btn-sm" style="{{ $client ? '' : 'opacity:0.4; pointer-events:none' }}">+ Ajouter véhicule</a>
            </div>
        </div>

        <!-- Panne -->
        <div class="card">
            <div class="card-title">Détails de la panne</div>

            <div class="form-group">
                <label class="form-label">Description de la panne *</label>
                <textarea class="form-textarea" name="description_panne" rows="4"
                          placeholder="Décrivez le problème signalé par le client…" required>{{ old('description_panne') }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Pièces estimées / demandées</label>
                <textarea class="form-textarea" name="pieces_estimees" rows="3"
                          placeholder="Ex: Plaquettes de frein avant, filtre à huile, courroie…">{{ old('pieces_estimees') }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Coût estimé (FCFA)</label>
                <input class="form-input" type="number" name="cout_estime" min="0" step="500"
                       value="{{ old('cout_estime') }}" placeholder="0">
            </div>
        </div>
    </div>

    <!-- Colonne droite -->
    <div style="display:flex; flex-direction:column; gap:20px">
        <div class="card">
            <div class="card-title">Paramètres</div>

            <div class="form-group">
                <label class="form-label">Urgence *</label>
                <select class="form-select" name="urgence" required>
                    <option value="normal" {{ old('urgence')=='normal' ? 'selected':'' }}>Normal</option>
                    <option value="urgent" {{ old('urgence')=='urgent' ? 'selected':'' }}>🔴 Urgent</option>
                    <option value="vip"    {{ old('urgence')=='vip'    ? 'selected':'' }}>⭐ VIP</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Date d'entrée *</label>
                <input class="form-input" type="date" name="date_entree"
                       value="{{ old('date_entree', date('Y-m-d')) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Date de sortie prévue</label>
                <input class="form-input" type="date" name="date_sortie_prevue"
                       value="{{ old('date_sortie_prevue') }}">
            </div>

            <div class="form-group">
                <label class="form-label">Kilométrage à l'entrée</label>
                <input class="form-input" type="number" name="kilometrage_entree"
                       value="{{ old('kilometrage_entree') }}" placeholder="ex: 85000" min="0">
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px">
            Créer l'ordre de réparation
        </button>
        <a href="{{ route('repairs.index') }}" class="btn btn-ghost" style="width:100%; justify-content:center">
            Annuler
        </a>
    </div>

</div>
</form>
@endsection

@push('scripts')
<script>
document.getElementById('client_id').addEventListener('change', function() {
    const clientId = this.value;
    const vehicleSelect = document.getElementById('vehicle_id');
    vehicleSelect.innerHTML = '<option value="">Chargement…</option>';

    if (!clientId) {
        vehicleSelect.innerHTML = '<option value="">— Sélectionner d\'abord un client —</option>';
        return;
    }

    fetch(`/clients/${clientId}/vehicles`)
        .then(r => r.json())
        .then(data => {
            if (data.length === 0) {
                vehicleSelect.innerHTML = '<option value="">Aucun véhicule — Ajoutez-en un</option>';
            } else {
                vehicleSelect.innerHTML = '<option value="">— Sélectionner un véhicule —</option>';
                data.forEach(v => {
                    vehicleSelect.innerHTML += `<option value="${v.id}">${v.immatriculation} — ${v.marque} ${v.modele}</option>`;
                });
            }
            // Update add-vehicle link
            const btn = document.getElementById('add-vehicle-btn');
            btn.href = `/vehicles/create?client_id=${clientId}`;
            btn.style.opacity = '1';
            btn.style.pointerEvents = 'auto';
        });
});
</script>
@endpush
