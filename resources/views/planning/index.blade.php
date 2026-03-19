@extends('layouts.app')
@section('title', 'Planning atelier')
@section('page-title', 'Planning atelier')

@section('content')
@if($nonAssignes->isNotEmpty())
<div class="card" style="margin-bottom:24px; border-color:rgba(245,200,66,0.3)">
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px">
        <span style="color:var(--yellow); font-size:18px">⚠</span>
        <div class="card-title" style="margin:0; color:var(--yellow)">Non assignés ({{ $nonAssignes->count() }})</div>
    </div>
    <div style="display:flex; flex-wrap:wrap; gap:10px">
        @foreach($nonAssignes as $order)
        <a href="{{ route('repairs.show', $order) }}" style="text-decoration:none">
            <div style="background:var(--bg-hover); border:1px solid var(--border-mid); border-radius:10px; padding:12px 14px; min-width:180px">
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px">
                    <span class="urgence-dot {{ $order->urgence }}"></span>
                    <span style="font-weight:600; color:var(--accent); font-size:13px">{{ $order->numero }}</span>
                </div>
                <div style="font-size:12px; color:var(--text-primary)">{{ $order->client->nom_complet }}</div>
                <div style="font-size:11px; color:var(--text-muted)">{{ $order->vehicle->immatriculation }}</div>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endif

<div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap:20px">
    @forelse($mecaniciens as $mec)
    <div class="card">
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:16px">
            <div style="width:42px; height:42px; border-radius:50%; background:rgba(62,207,142,0.15); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:18px; color:var(--green); flex-shrink:0">
                {{ strtoupper(substr($mec->name, 0, 1)) }}
            </div>
            <div style="flex:1">
                <div style="font-weight:600; color:var(--text-primary); font-family:var(--font-display)">{{ $mec->name }}</div>
                <div style="font-size:12px; color:var(--text-muted)">{{ $mec->repairOrdersAssigned->count() }} intervention(s) active(s)</div>
            </div>
            <div style="font-family:var(--font-display); font-size:28px; font-weight:800; color:{{ $mec->repairOrdersAssigned->count() >= 4 ? 'var(--red)' : ($mec->repairOrdersAssigned->count() >= 2 ? 'var(--accent)' : 'var(--green)') }}">
                {{ $mec->repairOrdersAssigned->count() }}
            </div>
        </div>

        @if($mec->repairOrdersAssigned->isEmpty())
            <div style="padding:20px; text-align:center; color:var(--text-muted); font-size:13px; background:var(--bg-surface); border-radius:8px">
                Disponible — aucune tâche
            </div>
        @else
            <div style="display:flex; flex-direction:column; gap:8px">
                @foreach($mec->repairOrdersAssigned as $order)
                <a href="{{ route('repairs.show', $order) }}" style="text-decoration:none; display:block">
                    <div style="background:var(--bg-surface); border:1px solid var(--border); border-radius:8px; padding:10px 12px; transition:border-color 0.15s"
                         onmouseover="this.style.borderColor='var(--border-mid)'" onmouseout="this.style.borderColor='var(--border)'">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:4px">
                            <div style="display:flex; align-items:center; gap:7px">
                                <span class="urgence-dot {{ $order->urgence }}"></span>
                                <span style="font-weight:600; color:var(--accent); font-size:12px">{{ $order->numero }}</span>
                            </div>
                            <span class="badge badge-{{ $order->statut_color }}" style="font-size:10px">{{ $order->statut_label }}</span>
                        </div>
                        <div style="font-size:12px; color:var(--text-primary)">{{ $order->client->nom_complet }}</div>
                        <div style="font-size:11px; color:var(--text-muted); margin-top:2px">
                            {{ $order->vehicle->immatriculation }} —
                            {{ Str::limit($order->description_panne, 50) }}
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        @endif
    </div>
    @empty
    <div class="card" style="grid-column:1/-1; text-align:center; padding:60px; color:var(--text-muted)">
        Aucun mécanicien actif. <a href="{{ route('users.create') }}" style="color:var(--accent)">Créez un compte mécanicien</a>.
    </div>
    @endforelse
</div>
@endsection
