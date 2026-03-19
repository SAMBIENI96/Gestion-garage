<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\RepairOrder;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['client', 'repairOrder']);

        if ($statut = $request->get('statut')) {
            $query->where('statut', $statut);
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('invoices.index', compact('invoices'));
    }

    public function create(Request $request)
    {
        $repair = RepairOrder::with(['client', 'vehicle'])->findOrFail($request->repair_order_id);
        return view('invoices.create', compact('repair'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'repair_order_id' => 'required|exists:repair_orders,id',
            'lignes'          => 'required|array|min:1',
            'lignes.*.description'   => 'required|string',
            'lignes.*.quantite'      => 'required|numeric|min:0',
            'lignes.*.prix_unitaire' => 'required|numeric|min:0',
            'lignes.*.type'          => 'required|in:piece,main_oeuvre,autre',
            'remise_pct'      => 'nullable|numeric|min:0|max:100',
            'notes'           => 'nullable|string',
        ]);

        $lignes    = $data['lignes'];
        $sousTotal = collect($lignes)->sum(fn($l) => $l['quantite'] * $l['prix_unitaire']);
        $remisePct = (float)($data['remise_pct'] ?? 0);
        $remiseMnt = $sousTotal * $remisePct / 100;
        $total     = $sousTotal - $remiseMnt;

        $repair = RepairOrder::findOrFail($data['repair_order_id']);

        Invoice::create([
            'repair_order_id' => $repair->id,
            'client_id'       => $repair->client_id,
            'created_by'      => auth()->id(),
            'lignes'          => $lignes,
            'sous_total'      => $sousTotal,
            'remise_pct'      => $remisePct,
            'remise_montant'  => $remiseMnt,
            'total_ttc'       => $total,
            'statut'          => 'brouillon',
            'date_facture'    => today(),
            'notes'           => $data['notes'] ?? null,
        ]);

        return redirect()->route('invoices.index')->with('success', 'Facture créée.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['client', 'repairOrder.vehicle', 'createdBy']);
        return view('invoices.show', compact('invoice'));
    }

    public function valider(Invoice $invoice)
    {
        $invoice->update(['statut' => 'validee']);
        return back()->with('success', 'Facture validée.');
    }

    public function marquerPayee(Invoice $invoice)
    {
        $invoice->update(['statut' => 'payee']);
        return back()->with('success', 'Facture marquée payée.');
    }

    public function print(Invoice $invoice)
    {
        $invoice->load(['client', 'repairOrder.vehicle', 'createdBy']);
        return view('invoices.print', compact('invoice'));
    }
}
