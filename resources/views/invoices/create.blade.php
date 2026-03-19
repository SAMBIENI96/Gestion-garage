@extends('layouts.app')
@section('title', 'Créer une facture')
@section('page-title', 'Créer une facture')

@section('content')
<form action="{{ route('invoices.store') }}" method="POST" id="invoice-form">
@csrf
<input type="hidden" name="repair_order_id" value="{{ $repair->id }}">

<div style="display:grid; grid-template-columns:1fr 300px; gap:20px; align-items:start">

    <div style="display:flex; flex-direction:column; gap:20px">
        <!-- En-tête facture -->
        <div class="card">
            <div class="card-title">Informations</div>
            <div class="form-grid-2">
                <div>
                    <div class="form-label">Client</div>
                    <div style="color:var(--text-primary); font-weight:500">{{ $repair->client->nom_complet }}</div>
                    <div style="font-size:12px;color:var(--text-muted)">{{ $repair->client->telephone }}</div>
                </div>
                <div>
                    <div class="form-label">Véhicule</div>
                    <div style="color:var(--text-primary); font-weight:500">{{ $repair->vehicle->immatriculation }}</div>
                    <div style="font-size:12px;color:var(--text-muted)">{{ $repair->vehicle->marque }} {{ $repair->vehicle->modele }}</div>
                </div>
                <div>
                    <div class="form-label">Ordre de réparation</div>
                    <span style="color:var(--accent); font-weight:600">{{ $repair->numero }}</span>
                </div>
            </div>
        </div>

        <!-- Lignes de facturation -->
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
                <div class="card-title" style="margin:0">Lignes de facturation</div>
                <button type="button" class="btn btn-secondary btn-sm" onclick="addLigne()">+ Ajouter ligne</button>
            </div>

            <div id="lignes-container">
                <!-- Ligne d'exemple -->
                <div class="ligne-row" style="display:grid;grid-template-columns:1fr 80px 120px 100px 36px;gap:8px;margin-bottom:10px;align-items:center">
                    <input class="form-input" type="text" name="lignes[0][description]" placeholder="Description" required>
                    <select class="form-select" name="lignes[0][type]">
                        <option value="piece">Pièce</option>
                        <option value="main_oeuvre">M.O.</option>
                        <option value="autre">Autre</option>
                    </select>
                    <input class="form-input qte" type="number" name="lignes[0][quantite]" placeholder="Qté" value="1" min="0" step="0.5" required oninput="recalculate()">
                    <input class="form-input pu" type="number" name="lignes[0][prix_unitaire]" placeholder="Prix unit." min="0" step="100" required oninput="recalculate()">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeLigne(this)" style="padding:8px">✕</button>
                </div>
            </div>

            <!-- Récap -->
            <div style="border-top:1px solid var(--border);padding-top:16px;margin-top:8px">
                <div style="display:flex;justify-content:space-between;margin-bottom:8px">
                    <span style="color:var(--text-muted)">Sous-total</span>
                    <span id="sous-total" style="color:var(--text-primary);font-weight:500">0 F</span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                    <span style="color:var(--text-muted)">Remise (%)</span>
                    <input class="form-input" type="number" name="remise_pct" id="remise-input" min="0" max="100" step="1" value="0"
                           style="width:80px;text-align:right" oninput="recalculate()">
                </div>
                <div style="display:flex;justify-content:space-between;border-top:1px solid var(--border);padding-top:12px;margin-top:4px">
                    <span style="font-weight:700;color:var(--text-primary)">TOTAL</span>
                    <span id="total-ttc" style="font-family:var(--font-display);font-size:22px;font-weight:800;color:var(--accent)">0 F</span>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Notes sur la facture</label>
            <textarea class="form-textarea" name="notes" rows="2" placeholder="Remarques, conditions de paiement…"></textarea>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:16px">
        <div class="card">
            <div class="card-title">Actions</div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px;margin-bottom:10px">
                Créer la facture
            </button>
            <a href="{{ route('repairs.show', $repair) }}" class="btn btn-ghost" style="width:100%;justify-content:center">
                Annuler
            </a>
        </div>

        <div class="card" style="background:rgba(232,98,42,0.05);border-color:rgba(232,98,42,0.2)">
            <div style="font-size:12px;color:var(--text-muted);line-height:1.6">
                <strong style="color:var(--text-secondary)">Raccourcis types :</strong><br>
                M.O. = Main d'œuvre<br>
                Pièce = Fourniture<br>
                Autre = Frais divers
            </div>
        </div>
    </div>
</div>
</form>
@endsection

@push('scripts')
<script>
let ligneIndex = 1;

function addLigne() {
    const container = document.getElementById('lignes-container');
    const i = ligneIndex++;
    const div = document.createElement('div');
    div.className = 'ligne-row';
    div.style = 'display:grid;grid-template-columns:1fr 80px 120px 100px 36px;gap:8px;margin-bottom:10px;align-items:center';
    div.innerHTML = `
        <input class="form-input" type="text" name="lignes[${i}][description]" placeholder="Description" required>
        <select class="form-select" name="lignes[${i}][type]">
            <option value="piece">Pièce</option>
            <option value="main_oeuvre">M.O.</option>
            <option value="autre">Autre</option>
        </select>
        <input class="form-input qte" type="number" name="lignes[${i}][quantite]" placeholder="Qté" value="1" min="0" step="0.5" required oninput="recalculate()">
        <input class="form-input pu" type="number" name="lignes[${i}][prix_unitaire]" placeholder="Prix unit." min="0" step="100" required oninput="recalculate()">
        <button type="button" class="btn btn-danger btn-sm" onclick="removeLigne(this)" style="padding:8px">✕</button>
    `;
    container.appendChild(div);
}

function removeLigne(btn) {
    const rows = document.querySelectorAll('.ligne-row');
    if (rows.length > 1) {
        btn.closest('.ligne-row').remove();
        recalculate();
    }
}

function recalculate() {
    let sousTotal = 0;
    document.querySelectorAll('.ligne-row').forEach(row => {
        const qte = parseFloat(row.querySelector('.qte')?.value || 0);
        const pu  = parseFloat(row.querySelector('.pu')?.value || 0);
        sousTotal += qte * pu;
    });
    const remise = parseFloat(document.getElementById('remise-input').value || 0);
    const total  = sousTotal * (1 - remise / 100);
    const fmt = n => n.toLocaleString('fr-FR', {maximumFractionDigits: 0}) + ' F';
    document.getElementById('sous-total').textContent = fmt(sousTotal);
    document.getElementById('total-ttc').textContent  = fmt(total);
}
</script>
@endpush
