<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture {{ $invoice->numero }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 13px;
            color: #1a1a2e;
            background: #fff;
            padding: 40px;
        }
        .header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:32px; padding-bottom:24px; border-bottom:2px solid #e8622a; }
        .brand { font-size:28px; font-weight:900; color:#1a1a2e; letter-spacing:-1px; }
        .brand span { color:#e8622a; }
        .invoice-meta { text-align:right; }
        .invoice-num { font-size:22px; font-weight:800; color:#e8622a; }
        .invoice-date { font-size:12px; color:#666; margin-top:4px; }
        .status-badge { display:inline-block; padding:4px 12px; border-radius:20px; font-size:11px; font-weight:700; text-transform:uppercase; margin-top:6px; background:#e8f5e9; color:#2e7d32; }

        .parties { display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:28px; }
        .party-label { font-size:10px; font-weight:700; color:#999; text-transform:uppercase; letter-spacing:1px; margin-bottom:6px; }
        .party-name { font-size:15px; font-weight:700; color:#1a1a2e; }
        .party-detail { font-size:12px; color:#555; margin-top:2px; }

        table { width:100%; border-collapse:collapse; margin-bottom:24px; }
        thead tr { background:#f5f5f5; }
        th { padding:10px 12px; text-align:left; font-size:11px; font-weight:700; color:#555; text-transform:uppercase; letter-spacing:0.5px; }
        th:last-child, td:last-child { text-align:right; }
        td { padding:10px 12px; border-bottom:1px solid #eee; font-size:13px; color:#333; }
        tr:last-child td { border-bottom:none; }

        .type-badge { display:inline-block; padding:2px 8px; border-radius:12px; font-size:10px; font-weight:600; }
        .type-piece { background:#fff3e0; color:#e65100; }
        .type-mo { background:#e3f2fd; color:#1565c0; }
        .type-autre { background:#f5f5f5; color:#555; }

        .totals { display:flex; justify-content:flex-end; margin-bottom:32px; }
        .totals-box { min-width:260px; }
        .total-row { display:flex; justify-content:space-between; padding:7px 0; border-bottom:1px solid #eee; font-size:13px; color:#555; }
        .total-final { display:flex; justify-content:space-between; padding:14px 0 0; font-weight:800; font-size:20px; color:#1a1a2e; }
        .total-final span:last-child { color:#e8622a; }

        .footer { border-top:1px solid #eee; padding-top:20px; display:flex; justify-content:space-between; align-items:center; }
        .footer-note { font-size:11px; color:#999; }
        .signature-box { border:1px dashed #ccc; width:180px; height:60px; border-radius:4px; display:flex; align-items:center; justify-content:center; font-size:11px; color:#bbb; }

        @media print {
            body { padding: 20px; }
            .no-print { display:none; }
        }
    </style>
</head>
<body>

<div class="no-print" style="margin-bottom:20px">
    <button onclick="window.print()" style="background:#e8622a;color:#fff;border:none;padding:10px 20px;border-radius:8px;cursor:pointer;font-size:14px;font-weight:600">
        🖨 Imprimer
    </button>
    <button onclick="window.close()" style="background:#f5f5f5;color:#333;border:1px solid #ddd;padding:10px 20px;border-radius:8px;cursor:pointer;font-size:14px;margin-left:8px">
        Fermer
    </button>
</div>

<!-- En-tête -->
<div class="header">
    <div>
        <div class="brand">Auto<span>Gest</span></div>
        <div style="font-size:12px;color:#666;margin-top:4px">Système de gestion de garage</div>
    </div>
    <div class="invoice-meta">
        <div class="invoice-num">{{ $invoice->numero }}</div>
        <div class="invoice-date">Date : {{ $invoice->date_facture->format('d/m/Y') }}</div>
        <div class="invoice-date" style="margin-top:2px">Ordre : {{ $invoice->repairOrder->numero }}</div>
        <span class="status-badge">{{ $invoice->statut_label }}</span>
    </div>
</div>

<!-- Parties -->
<div class="parties">
    <div>
        <div class="party-label">Facturé à</div>
        <div class="party-name">{{ $invoice->client->nom_complet }}</div>
        <div class="party-detail">{{ $invoice->client->telephone }}</div>
        @if($invoice->client->email)
        <div class="party-detail">{{ $invoice->client->email }}</div>
        @endif
        @if($invoice->client->adresse)
        <div class="party-detail">{{ $invoice->client->adresse }}</div>
        @endif
    </div>
    <div>
        <div class="party-label">Véhicule concerné</div>
        <div class="party-name" style="color:#e8622a">{{ $invoice->repairOrder->vehicle->immatriculation }}</div>
        <div class="party-detail">{{ $invoice->repairOrder->vehicle->marque }} {{ $invoice->repairOrder->vehicle->modele }}
            @if($invoice->repairOrder->vehicle->annee) ({{ $invoice->repairOrder->vehicle->annee }}) @endif
        </div>
        @if($invoice->repairOrder->vehicle->couleur)
        <div class="party-detail">Couleur : {{ $invoice->repairOrder->vehicle->couleur }}</div>
        @endif
    </div>
</div>

<!-- Lignes -->
<table>
    <thead>
        <tr>
            <th>Description</th>
            <th>Type</th>
            <th style="text-align:right">Qté</th>
            <th style="text-align:right">Prix unitaire</th>
            <th style="text-align:right">Montant</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoice->lignes as $ligne)
        <tr>
            <td>{{ $ligne['description'] }}</td>
            <td>
                <span class="type-badge type-{{ $ligne['type'] === 'main_oeuvre' ? 'mo' : $ligne['type'] }}">
                    {{ $ligne['type'] === 'main_oeuvre' ? 'Main d\'œuvre' : ($ligne['type'] === 'piece' ? 'Pièce' : 'Autre') }}
                </span>
            </td>
            <td style="text-align:right">{{ $ligne['quantite'] }}</td>
            <td style="text-align:right">{{ number_format($ligne['prix_unitaire'], 0, ',', ' ') }} F</td>
            <td style="text-align:right;font-weight:600">{{ number_format($ligne['quantite'] * $ligne['prix_unitaire'], 0, ',', ' ') }} F</td>
        </tr>
        @endforeach
    </tbody>
</table>

<!-- Totaux -->
<div class="totals">
    <div class="totals-box">
        <div class="total-row">
            <span>Sous-total</span>
            <span>{{ number_format($invoice->sous_total, 0, ',', ' ') }} F</span>
        </div>
        @if($invoice->remise_pct > 0)
        <div class="total-row" style="color:#e53935">
            <span>Remise ({{ $invoice->remise_pct }}%)</span>
            <span>- {{ number_format($invoice->remise_montant, 0, ',', ' ') }} F</span>
        </div>
        @endif
        <div class="total-final">
            <span>TOTAL TTC</span>
            <span>{{ number_format($invoice->total_ttc, 0, ',', ' ') }} F</span>
        </div>
    </div>
</div>

@if($invoice->notes)
<div style="margin-bottom:24px;padding:12px 16px;background:#fafafa;border-radius:8px;border-left:3px solid #e8622a">
    <div style="font-size:11px;font-weight:700;color:#999;text-transform:uppercase;margin-bottom:4px">Notes</div>
    <p style="font-size:12px;color:#555;line-height:1.6">{{ $invoice->notes }}</p>
</div>
@endif

<!-- Footer -->
<div class="footer">
    <div class="footer-note">
        <div style="font-weight:600;margin-bottom:2px">Merci pour votre confiance.</div>
        <div>Document généré le {{ now()->format('d/m/Y à H:i') }} — AutoGest Pro</div>
    </div>
    <div>
        <div style="font-size:11px;color:#999;margin-bottom:4px;text-align:center">Signature & cachet</div>
        <div class="signature-box">Signature</div>
    </div>
</div>

</body>
</html>
