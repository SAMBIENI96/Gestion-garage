<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\InterventionNote;
use App\Models\Invoice;
use App\Models\RepairOrder;
use App\Models\User;
use App\Models\Vehicle;
use App\Support\Demo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

// Données de démonstration : un garage réaliste, daté par rapport à aujourd'hui.
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // ── Équipe ───────────────────────────────────────────────
        $team = [];
        foreach (Demo::ACCOUNTS as $key => $u) {
            $team[$key] = User::updateOrCreate(['email' => $u['email']], [
                'name'        => $u['name'],
                'phone'       => $u['phone'],
                'role'        => $u['role'],
                'password'    => Hash::make(Demo::PASSWORD),
                'is_active'   => true,
                'approved_at' => now(),
            ]);
        }
        ['patron' => $patron, 'accueil' => $accueil, 'mecanicien' => $kofi, 'mecanicien2' => $seydou] = $team;

        // ── Clients et véhicules ─────────────────────────────────
        $data = [
            ['Jean', 'AGOSSOU', '+229 96 11 22 33', 'Cotonou, Haie Vive', [['BJ-1234-AA', 'Toyota', 'Corolla', 2018, 85200, 'Blanc']]],
            ['Fatima', 'HOUNSA', '+229 96 44 55 66', 'Cotonou, Fidjrossè', [['BJ-5678-BB', 'Honda', 'CR-V', 2020, 42150, 'Gris']]],
            ['Rodrigue', 'KPOSSOU', '+229 97 77 88 99', 'Porto-Novo', [['BJ-9012-CC', 'Peugeot', '308', 2016, 121400, 'Rouge']]],
            ['Aminata', 'CISSÉ', '+229 96 22 33 44', 'Cotonou, Akpakpa', [['BJ-3456-DD', 'Toyota', 'Hilux', 2019, 63800, 'Noir'], ['BJ-3457-DE', 'Suzuki', 'Swift', 2021, 28900, 'Bleu']]],
            ['Emmanuel', 'DOSSOU', '+229 97 55 66 77', 'Abomey-Calavi', [['BJ-7890-EE', 'Renault', 'Duster', 2017, 98300, 'Blanc']]],
            ['Grâce', 'ADJOVI', '+229 95 12 34 56', 'Cotonou, Cadjèhoun', [['BJ-2468-FF', 'Hyundai', 'Tucson', 2022, 18700, 'Argent']]],
            ['Moussa', 'BIO', '+229 94 65 43 21', 'Parakou', [['BJ-1357-GG', 'Mitsubishi', 'L200', 2015, 176400, 'Blanc']]],
            ['Société TRANSLOG', 'SARL', '+229 21 30 40 50', 'Zone portuaire, Cotonou', [['BJ-8642-HH', 'Toyota', 'Hiace', 2018, 210500, 'Blanc']]],
        ];
        $v = [];
        foreach ($data as [$prenom, $nom, $tel, $adresse, $vehicles]) {
            $client = Client::updateOrCreate(['telephone' => $tel], [
                'prenom' => $prenom, 'nom' => $nom, 'adresse' => $adresse, 'created_by' => $accueil->id,
            ]);
            foreach ($vehicles as [$immat, $marque, $modele, $annee, $km, $couleur]) {
                $v[$immat] = Vehicle::updateOrCreate(['immatriculation' => $immat], [
                    'client_id' => $client->id, 'marque' => $marque, 'modele' => $modele,
                    'annee' => $annee, 'kilometrage' => $km, 'couleur' => $couleur,
                ]);
            }
        }

        // ── Interventions ────────────────────────────────────────
        // [immat, mécanicien, panne, pièces, coût, statut, urgence, entrée (jours), sortie prévue (jours), consigne, historique, facture]
        $orders = [
            ['BJ-1234-AA', $kofi, 'Freins usés à l\'avant, bruit métallique + légère fuite d\'huile moteur.', 'Plaquettes avant, joint de carter', 45000, 'en_cours', 'urgent', -2, 1, 'Client pressé : véhicule de travail.',
                [['nouveau', 'en_cours', 'Diagnostic effectué : plaquettes à 1 mm, joint de carter poreux.']], null],
            ['BJ-5678-BB', $kofi, 'Climatisation ne refroidit plus, odeur d\'humidité dans l\'habitacle.', 'Recharge gaz R134a, filtre habitacle', 35000, 'nouveau', 'vip', -1, 2, 'Cliente VIP : la prévenir dès que c\'est prêt.', [], null],
            ['BJ-9012-CC', $kofi, 'Embrayage qui patine en côte, pédale dure.', 'Kit embrayage complet', 180000, 'en_attente_pieces', 'normal', -4, 3, null,
                [['nouveau', 'en_cours', 'Embrayage usé confirmé à l\'essai.'], ['en_cours', 'en_attente_pieces', 'Kit embrayage commandé chez le fournisseur, livraison prévue sous 48 h.']], null],
            ['BJ-3456-DD', $seydou, 'Vidange + révision des 60 000 km.', 'Huile 5W30, filtres huile / air / gasoil', 65000, 'en_cours', 'normal', -1, 0, null,
                [['nouveau', 'en_cours', 'Vidange faite, contrôle des filtres en cours.']], null],
            ['BJ-1357-GG', $seydou, 'Bruit anormal moteur à froid, fumée bleue à l\'échappement.', null, null, 'probleme', 'urgent', -3, 2, 'Faire un devis avant toute intervention.',
                [['nouveau', 'en_cours', 'Compression faible sur le cylindre 3.'], ['en_cours', 'probleme', 'Segments probablement usés : réfection moteur à chiffrer avec le patron.']], null],
            ['BJ-8642-HH', null, 'Pneus arrière lisses + parallélisme à refaire.', '2 pneus 195/70 R15', 90000, 'nouveau', 'urgent', 0, 1, null, [], null],
            ['BJ-2468-FF', null, 'Voyant moteur allumé au tableau de bord.', null, null, 'nouveau', 'normal', 0, 2, null, [], null],
            ['BJ-7890-EE', $kofi, 'Batterie à plat, démarrage difficile le matin.', 'Batterie 70 Ah', 55000, 'termine', 'normal', -1, 0, null,
                [['nouveau', 'en_cours', 'Batterie testée : HS. Alternateur OK.'], ['en_cours', 'termine', 'Batterie remplacée, charge contrôlée. Véhicule prêt.']],
                ['payee', [['Batterie 70 Ah', 1, 48000, 'piece'], ['Main d\'œuvre', 1, 7000, 'main_oeuvre']], 0]],
            ['BJ-3457-DE', $seydou, 'Amortisseurs avant qui cognent sur les dos-d\'âne.', '2 amortisseurs avant', 85000, 'termine', 'normal', -6, -3, null,
                [['nouveau', 'en_cours', 'Amortisseurs avant HS, coupelles OK.'], ['en_cours', 'termine', 'Amortisseurs remplacés, essai routier OK.']],
                ['validee', [['Amortisseur avant', 2, 32000, 'piece'], ['Main d\'œuvre', 1.5, 12000, 'main_oeuvre']], 5]],
            ['BJ-1234-AA', $kofi, 'Remplacement des essuie-glaces et contrôle des feux.', 'Balais essuie-glace', 12000, 'termine', 'normal', -12, -11, null,
                [['nouveau', 'termine', 'Balais remplacés, ampoule de feu stop changée.']],
                ['payee', [['Balais essuie-glace (paire)', 1, 8000, 'piece'], ['Ampoule feu stop', 1, 1500, 'piece'], ['Main d\'œuvre', 1, 2500, 'main_oeuvre']], 0]],
            ['BJ-5678-BB', $seydou, 'Révision complète avant voyage.', 'Huile, filtres, liquide de frein', 75000, 'termine', 'normal', -20, -19, null,
                [['nouveau', 'termine', 'Révision faite, rien à signaler.']],
                ['brouillon', [['Kit révision', 1, 52000, 'piece'], ['Liquide de frein DOT4', 1, 6500, 'piece'], ['Main d\'œuvre', 1, 15000, 'main_oeuvre']], 0]],
        ];

        foreach ($orders as [$immat, $mech, $panne, $pieces, $cout, $statut, $urgence, $in, $out, $consigne, $history, $invoice]) {
            $vehicle = $v[$immat];
            $entree = now()->startOfDay()->addDays($in);
            $done = $statut === 'termine';

            $order = RepairOrder::create([
                'client_id'             => $vehicle->client_id,
                'vehicle_id'            => $vehicle->id,
                'created_by'            => $accueil->id,
                'assigned_to'           => $mech?->id,
                'description_panne'     => $panne,
                'pieces_estimees'       => $pieces,
                'cout_estime'           => $cout,
                'notes_patron'          => $consigne,
                'statut'                => $statut,
                'urgence'               => $urgence,
                'kilometrage_entree'    => $vehicle->kilometrage,
                'date_entree'           => $entree,
                'date_sortie_prevue'    => now()->startOfDay()->addDays($out),
                'date_sortie_effective' => $done ? now()->startOfDay()->addDays($out)->setTime(16, 30) : null,
            ]);
            $order->forceFill(['created_at' => $entree->copy()->setTime(8, 15)])->save();

            if ($mech) {
                InterventionNote::create([
                    'repair_order_id' => $order->id, 'user_id' => $patron->id,
                    'contenu' => "Assigné à {$mech->name}", 'ancien_statut' => 'nouveau', 'nouveau_statut' => 'nouveau',
                ])->forceFill(['created_at' => $entree->copy()->setTime(8, 40)])->save();
            }
            foreach ($history as $i => [$from, $to, $text]) {
                InterventionNote::create([
                    'repair_order_id' => $order->id, 'user_id' => $mech->id,
                    'contenu' => $text, 'ancien_statut' => $from, 'nouveau_statut' => $to,
                ])->forceFill(['created_at' => $entree->copy()->addDays($i)->setTime(10 + $i * 3, 5)])->save();
            }

            if ($invoice) {
                [$invStatut, $lignes, $remise] = $invoice;
                $lignes = array_map(fn ($l) => ['description' => $l[0], 'quantite' => $l[1], 'prix_unitaire' => $l[2], 'type' => $l[3]], $lignes);
                $sousTotal = array_sum(array_map(fn ($l) => $l['quantite'] * $l['prix_unitaire'], $lignes));
                $montantRemise = round($sousTotal * $remise / 100);
                Invoice::create([
                    'repair_order_id' => $order->id, 'client_id' => $order->client_id, 'created_by' => $patron->id,
                    'lignes' => $lignes, 'sous_total' => $sousTotal, 'remise_pct' => $remise,
                    'remise_montant' => $montantRemise, 'total_ttc' => $sousTotal - $montantRemise,
                    'statut' => $invStatut, 'date_facture' => $order->date_sortie_effective,
                ]);
            }
        }
    }
}
