@extends('layouts.app')
@section('title', 'Factures')
@section('page-title', 'Factures')

@section('content')
<div class="card" style="margin-bottom:20px;padding:16px">
    <form method="GET" style="display:flex;gap:12px;align-items:flex-end">
        <div>
            <label class="form-label">Statut</label>
            <select class="form-select" name="statut" style="width:180px">
                <option value="">Toutes</option>
                <option value="brouillon" {{ request('statut')=='brouillon' ? 'selected':'' }}>Brouillon</option>
                <option value="validee"   {{ request('statut')=='validee'   ? 'selected':'' }}>Validée</option>
                <option value="payee"     {{ request('statut')=='payee'     ? 'selected':'' }}>Payée</option>
                <option value="annulee"   {{ request('statut')=='annulee'   ? 'selected':'' }}>Annulée</option>
            </select>
        </div>
        <button type="submit" class="btn btn-secondary">Filtrer</button>
        @if(request('statut'))
            <a href="{{ route('invoices.index') }}" class="btn btn-ghost">Effacer</a>
        @endif
    </form>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th>N° Facture</th>
                    <th>Client</th>
                    <th>Ordre</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                <tr>
                    <td>
                        <a href="{{ route('invoices.show', $invoice) }}" style="color:var(--accent);text-decoration:none;font-weight:700;font-family:var(--font-display)">
                            {{ $invoice->numero }}
                        </a>
                    </td>
                    <td style="color:var(--text-primary)">{{ $invoice->client->nom_complet }}</td>
                    <td>
                        <a href="{{ route('repairs.show', $invoice->repairOrder) }}" style="color:var(--text-muted);text-decoration:none;font-size:12px">
                            {{ $invoice->repairOrder->numero }}
                        </a>
                    </td>
                    <td style="color:var(--text-muted)">{{ $invoice->date_facture->format('d/m/Y') }}</td>
                    <td>
                        <span style="font-family:var(--font-display);font-weight:700;color:var(--accent)">
                            {{ number_format($invoice->total_ttc, 0, ',', ' ') }} F
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-{{ $invoice->statut === 'payee' ? 'green' : ($invoice->statut === 'validee' ? 'blue' : ($invoice->statut === 'brouillon' ? 'gray' : 'red')) }}">
                            {{ $invoice->statut_label }}
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-ghost btn-sm">Voir</a>
                            <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="btn btn-ghost btn-sm">🖨</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:40px">Aucune facture.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $invoices->links() }}</div>
</div>
@endsection
