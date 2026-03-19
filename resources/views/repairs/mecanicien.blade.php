@extends('layouts.app')
@section('title', 'Mes interventions')
@section('page-title', 'Mes interventions')

@section('content')
@if($orders->isEmpty())
<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:80px 24px;text-align:center">
    <div style="font-size:48px;margin-bottom:16px">✅</div>
    <div style="font-family:var(--font-display);font-size:22px;font-weight:700;color:var(--text-primary);margin-bottom:8px">
        Aucune tâche en cours
    </div>
    <div style="color:var(--text-muted);font-size:14px">
        Le patron vous assignera une intervention prochainement.
    </div>
</div>
@else
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px">
    @foreach($orders as $order)
    <div class="card" style="border-color:{{ $order->urgence === 'vip' ? 'rgba(245,200,66,0.4)' : ($order->urgence === 'urgent' ? 'rgba(240,77,77,0.3)' : 'var(--border)') }}">
        <!-- Header -->
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px">
            <div>
                <div style="display:flex;align-items:center;gap:8px">
                    <span style="font-family:var(--font-display);font-weight:800;color:var(--accent);font-size:16px">{{ $order->numero }}</span>
                    @if($order->urgence !== 'normal')
                        <span class="urgence-dot {{ $order->urgence }}"></span>
                        <span style="font-size:11px;font-weight:600;color:{{ $order->urgence === 'vip' ? 'var(--yellow)' : 'var(--red)' }}">{{ strtoupper($order->urgence_label) }}</span>
                    @endif
                </div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px">Entré le {{ $order->date_entree->format('d/m/Y') }}</div>
            </div>
            <span class="badge badge-{{ $order->statut_color }}">{{ $order->statut_label }}</span>
        </div>

        <!-- Client & Véhicule -->
        <div style="background:var(--bg-surface);border-radius:8px;padding:12px;margin-bottom:14px">
            <div style="display:flex;gap:16px">
                <div style="flex:1">
                    <div style="font-size:11px;color:var(--text-muted);margin-bottom:2px">CLIENT</div>
                    <div style="font-weight:500;color:var(--text-primary);font-size:13px">{{ $order->client->nom_complet }}</div>
                    <div style="font-size:12px;color:var(--text-muted)">{{ $order->client->telephone }}</div>
                </div>
                <div style="flex:1">
                    <div style="font-size:11px;color:var(--text-muted);margin-bottom:2px">VÉHICULE</div>
                    <div style="font-weight:600;color:var(--accent);font-size:14px">{{ $order->vehicle->immatriculation }}</div>
                    <div style="font-size:12px;color:var(--text-muted)">{{ $order->vehicle->marque }} {{ $order->vehicle->modele }}</div>
                </div>
            </div>
        </div>

        <!-- Description -->
        <div style="margin-bottom:14px">
            <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;text-transform:uppercase;letter-spacing:0.5px">Panne signalée</div>
            <p style="color:var(--text-secondary);font-size:13px;line-height:1.6">{{ $order->description_panne }}</p>
        </div>

        @if($order->pieces_estimees)
        <div style="background:rgba(232,98,42,0.06);border-radius:8px;padding:10px 12px;margin-bottom:14px;border-left:2px solid var(--accent)">
            <div style="font-size:11px;color:var(--accent);margin-bottom:2px;font-weight:600">PIÈCES</div>
            <p style="color:var(--text-secondary);font-size:12px;line-height:1.5">{{ $order->pieces_estimees }}</p>
        </div>
        @endif

        <!-- Mise à jour rapide -->
        <div style="border-top:1px solid var(--border);padding-top:14px">
            <div style="font-size:11px;color:var(--text-muted);margin-bottom:10px;text-transform:uppercase;letter-spacing:0.5px">Mettre à jour</div>
            <form action="{{ route('repairs.updateStatut', $order) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group" style="margin-bottom:10px">
                    <select class="form-select" name="statut" style="font-size:13px">
                        <option value="en_attente_pieces" {{ $order->statut === 'en_attente_pieces' ? 'selected':'' }}>⏳ En attente de pièces</option>
                        <option value="en_cours" {{ $order->statut === 'en_cours' ? 'selected':'' }}>🔧 En cours</option>
                        <option value="termine">✅ Terminé</option>
                        <option value="probleme">⚠ Problème détecté</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:10px">
                    <textarea class="form-textarea" name="contenu" rows="2" required
                              placeholder="Note sur l'avancement…" style="font-size:13px"></textarea>
                </div>
                <div style="display:flex;gap:8px;align-items:center">
                    <button type="submit" class="btn btn-primary btn-sm" style="flex:1;justify-content:center">
                        Enregistrer
                    </button>
                    <a href="{{ route('repairs.show', $order) }}" class="btn btn-ghost btn-sm">Détails</a>
                </div>
            </form>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection
