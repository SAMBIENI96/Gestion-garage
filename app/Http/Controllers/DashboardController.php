<?php

namespace App\Http\Controllers;

use App\Models\RepairOrder;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isMecanicien()) {
            return redirect()->route('repairs.mecanicien');
        }

        $stats = [
            'en_cours'     => RepairOrder::whereIn('statut', ['en_cours', 'en_attente_pieces'])->count(),
            'nouveaux'     => RepairOrder::where('statut', 'nouveau')->count(),
            'termines_jour'=> RepairOrder::where('statut', 'termine')
                                ->whereDate('date_sortie_effective', today())->count(),
            'clients_total'=> Client::count(),
        ];

        // Revenus du jour et de la semaine (factures validées/payées)
        $revenus = [
            'jour'   => Invoice::whereIn('statut', ['validee', 'payee'])
                            ->whereDate('date_facture', today())
                            ->sum('total_ttc'),
            'semaine'=> Invoice::whereIn('statut', ['validee', 'payee'])
                            ->whereBetween('date_facture', [now()->startOfWeek(), now()->endOfWeek()])
                            ->sum('total_ttc'),
        ];

        // Ordres récents
        $ordresRecents = RepairOrder::with(['client', 'vehicle', 'assignedTo'])
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        // Charge par mécanicien
        $mecaniciens = User::mecaniciens()
            ->withCount(['repairOrdersAssigned as charge' => fn($q) =>
                $q->whereIn('statut', ['nouveau', 'en_attente_pieces', 'en_cours'])
            ])
            ->get();

        return view('dashboard.index', compact('stats', 'revenus', 'ordresRecents', 'mecaniciens'));
    }
}
