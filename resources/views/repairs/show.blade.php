@extends('layouts.app')
@section('title', $repair->numero)
@section('page-title', $repair->numero)

@push('topbar-actions')
    @if(auth()->user()->isPatron())
        <a href="{{ route('repairs.edit', $repair) }}" class="btn btn-secondary btn-sm">Modifier</a>
        @if(!$repair->invoice)
            <a href="{{ route('invoices.create', ['repair_order_id' => $repair->id]) }}" class="btn btn-primary btn-sm">Créer facture</a>
        @endif
    @endif
@endpush

@section('content')
<div style="display:grid; grid-template-columns: 1fr 340px; gap:20px; align-items:start">

    <!-- Colonne principale -->
    <div style="display:flex; flex-direction:column; gap:20px">

        <!-- Infos générales -->
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px">
                <div>
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:6px">
                        <span class="badge badge-{{ $repair->statut_color }}" style="font-size:13px; padding:4px 12px">{{ $repair->statut_label }}</span>
                        @if($repair->urgence !== 'normal')
                            <span class="urgence-dot {{ $repair->urgence }}"></span>
                            <span style="font-size:12px; color:var(--text-muted)">{{ $repair->urgence_label }}</span>
                        @endif
                    </div>
                    <div style="font-size:12px; color:var(--text-muted)">
                        Créé le {{ $repair->created_at->format('d/m/Y à H:i') }} par {{ $repair->createdBy->name }}
                    </div>
                </div>
                <div style="font-family:var(--font-display); font-size:22px; font-weight:800; color:var(--accent)">
                    {{ $repair->numero }}
                </div>
            </div>

            <div class="form-grid-2">
                <div>
                    <div class="form-label">Client</div>
                    <a href="{{ route('clients.show', $repair->client) }}" style="color:var(--text-primary);text-decoration:none;font-weight:500">
                        {{ $repair->client->nom_complet }}
                    </a>
                    <div style="font-size:12px;color:var(--text-muted)">{{ $repair->client->telephone }}</div>
                </div>
                <div>
                    <div class="form-label">Véhicule</div>
                    <a href="{{ route('vehicles.show', $repair->vehicle) }}" style="color:var(--text-primary);text-decoration:none;font-weight:500">
                        {{ $repair->vehicle->immatriculation }}
                    </a>
                    <div style="font-size:12px;color:var(--text-muted)">{{ $repair->vehicle->marque }} {{ $repair->vehicle->modele }} {{ $repair->vehicle->annee }}</div>
                </div>
                <div>
                    <div class="form-label">Date d'entrée</div>
                    <span style="color:var(--text-primary)">{{ $repair->date_entree->format('d/m/Y') }}</span>
                </div>
                <div>
                    <div class="form-label">Sortie prévue</div>
                    <span style="color:var(--text-primary)">{{ $repair->date_sortie_prevue?->format('d/m/Y') ?? '—' }}</span>
                </div>
                @if($repair->kilometrage_entree)
                <div>
                    <div class="form-label">Kilométrage</div>
                    <span style="color:var(--text-primary)">{{ number_format($repair->kilometrage_entree, 0, ',', ' ') }} km</span>
                </div>
                @endif
                @if($repair->cout_estime)
                <div>
                    <div class="form-label">Coût estimé</div>
                    <span style="color:var(--text-primary)">{{ number_format($repair->cout_estime, 0, ',', ' ') }} FCFA</span>
                </div>
                @endif
            </div>

            <div style="margin-top:20px">
                <div class="form-label">Description de la panne</div>
                <p style="color:var(--text-secondary); line-height:1.7">{{ $repair->description_panne }}</p>
            </div>

            @if($repair->pieces_estimees)
            <div style="margin-top:16px">
                <div class="form-label">Pièces estimées</div>
                <p style="color:var(--text-secondary); line-height:1.7">{{ $repair->pieces_estimees }}</p>
            </div>
            @endif

            @if($repair->notes_patron)
            <div style="margin-top:16px; padding:12px; background:rgba(232,98,42,0.08); border-radius:10px; border-left:3px solid var(--accent)">
                <div class="form-label" style="color:var(--accent)">Note du patron</div>
                <p style="color:var(--text-secondary)">{{ $repair->notes_patron }}</p>
            </div>
            @endif
        </div>

        <!-- Historique / Notes d'intervention -->
        <div class="card">
            <div class="card-title">Historique des interventions</div>
            @forelse($repair->notes as $note)
            <div style="display:flex; gap:14px; margin-bottom:20px; padding-bottom:20px; border-bottom:1px solid var(--border)">
                <div style="width:36px; height:36px; border-radius:50%; background:var(--bg-hover); display:flex; align-items:center; justify-content:center; font-weight:600; font-size:14px; flex-shrink:0; color:var(--accent)">
                    {{ strtoupper(substr($note->user->name, 0, 1)) }}
                </div>
                <div style="flex:1">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px">
                        <span style="font-weight:500; color:var(--text-primary); font-size:13px">{{ $note->user->name }}</span>
                        <span style="font-size:11px; color:var(--text-muted)">{{ $note->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($note->ancien_statut && $note->nouveau_statut && $note->ancien_statut !== $note->nouveau_statut)
                    <div style="margin-bottom:6px; font-size:11px; color:var(--text-muted)">
                        Statut changé :
                        <span class="badge badge-gray" style="font-size:10px">{{ $note->ancien_statut }}</span>
                        → <span class="badge badge-blue" style="font-size:10px">{{ $note->nouveau_statut }}</span>
                    </div>
                    @endif
                    <p style="color:var(--text-secondary); font-size:13.5px; line-height:1.6">{{ $note->contenu }}</p>
                    @if($note->photo_path)
                    <div style="margin-top:8px">
                        <a href="{{ asset('storage/'.$note->photo_path) }}" target="_blank">
                            <img src="{{ asset('storage/'.$note->photo_path) }}" alt="Photo intervention"
                                 style="max-height:120px; border-radius:8px; border:1px solid var(--border)">
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <p style="color:var(--text-muted); font-size:13px">Aucune note d'intervention.</p>
            @endforelse

            @if(auth()->user()->isMecanicien() && $repair->assigned_to === auth()->id())
            <div style="margin-top:8px; padding-top:20px; border-top:1px solid var(--border)">
                <div class="card-title">Ajouter une mise à jour</div>
                <form action="{{ route('repairs.updateStatut', $repair) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-grid-2" style="margin-bottom:14px">
                        <div class="form-group" style="margin:0">
                            <label class="form-label">Nouveau statut</label>
                            <select class="form-select" name="statut" required>
                                <option value="en_attente_pieces">En attente de pièces</option>
                                <option value="en_cours" {{ $repair->statut === 'en_cours' ? 'selected':'' }}>En cours</option>
                                <option value="termine">Terminé ✓</option>
                                <option value="probleme">Problème détecté ⚠</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin:0">
                            <label class="form-label">Photo (optionnel)</label>
                            <input class="form-input" type="file" name="photo" accept="image/*">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Note *</label>
                        <textarea class="form-textarea" name="contenu" rows="3" required
                                  placeholder="Décrivez le travail effectué ou le problème rencontré…"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Enregistrer la mise à jour</button>
                </form>
            </div>
            @endif
        </div>
    </div>

    <!-- Colonne droite -->
    <div style="display:flex; flex-direction:column; gap:16px">

        <!-- Mécanicien assigné -->
        <div class="card">
            <div class="card-title">Mécanicien assigné</div>
            @if($repair->assignedTo)
                <div style="display:flex; align-items:center; gap:12px; margin-bottom:16px">
                    <div style="width:44px; height:44px; border-radius:50%; background:rgba(62,207,142,0.15); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:18px; color:var(--green)">
                        {{ strtoupper(substr($repair->assignedTo->name, 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-weight:600; color:var(--text-primary)">{{ $repair->assignedTo->name }}</div>
                        <div style="font-size:12px; color:var(--text-muted)">Mécanicien</div>
                    </div>
                </div>
            @else
                <div style="padding:16px; background:rgba(245,200,66,0.08); border-radius:8px; border:1px solid rgba(245,200,66,0.2); margin-bottom:16px; text-align:center; color:var(--yellow); font-size:13px">
                    ⚠ Non assigné
                </div>
            @endif

            @if(auth()->user()->isPatron())
            <form action="{{ route('repairs.assign', $repair) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">{{ $repair->assignedTo ? 'Réassigner à' : 'Assigner à' }}</label>
                    <select class="form-select" name="assigned_to" required>
                        <option value="">— Choisir un mécanicien —</option>
                        @foreach($mecaniciens as $mec)
                            <option value="{{ $mec->id }}" {{ $repair->assigned_to == $mec->id ? 'selected':'' }}>
                                {{ $mec->name }} ({{ $mec->charge_actuelle }} en cours)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Note (optionnel)</label>
                    <input class="form-input" type="text" name="notes_patron"
                           value="{{ $repair->notes_patron }}" placeholder="Urgent, VIP, priorité…">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center">
                    {{ $repair->assignedTo ? 'Réassigner' : 'Assigner' }}
                </button>
            </form>
            @endif
        </div>

        <!-- Facture liée -->
        @if($repair->invoice)
        <div class="card">
            <div class="card-title">Facture</div>
            <div style="display:flex; justify-content:space-between; align-items:center">
                <div>
                    <div style="font-weight:600; color:var(--text-primary)">{{ $repair->invoice->numero }}</div>
                    <span class="badge badge-{{ $repair->invoice->statut === 'payee' ? 'green' : ($repair->invoice->statut === 'validee' ? 'blue' : 'gray') }}">
                        {{ $repair->invoice->statut_label }}
                    </span>
                </div>
                <div style="font-family:var(--font-display); font-size:20px; font-weight:800; color:var(--accent)">
                    {{ number_format($repair->invoice->total_ttc, 0, ',', ' ') }} F
                </div>
            </div>
            <a href="{{ route('invoices.show', $repair->invoice) }}" class="btn btn-secondary btn-sm" style="margin-top:12px; width:100%; justify-content:center">
                Voir la facture
            </a>
        </div>
        @endif

        <!-- Actions rapides -->
        <div class="card">
            <div class="card-title">Liens rapides</div>
            <div style="display:flex; flex-direction:column; gap:8px">
                <a href="{{ route('clients.show', $repair->client) }}" class="btn btn-ghost btn-sm">Voir la fiche client</a>
                <a href="{{ route('vehicles.show', $repair->vehicle) }}" class="btn btn-ghost btn-sm">Historique du véhicule</a>
                @if(auth()->user()->isPatron())
                <a href="{{ route('repairs.edit', $repair) }}" class="btn btn-ghost btn-sm">Modifier l'ordre</a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
