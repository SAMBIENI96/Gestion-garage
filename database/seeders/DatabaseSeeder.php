<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Client;
use App\Models\Vehicle;
use App\Models\RepairOrder;
use App\Models\InterventionNote;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── UTILISATEURS (SAFE - updateOrCreate) ───────────────
        $users = [
            [
                'email' => 'patron@garage.local',
                'name'  => 'Thomas Patron',
                'phone' => '+229 97 00 00 01',
                'role'  => 'patron',
            ],
            [
                'email' => 'accueil@garage.local',
                'name'  => 'Marie Accueil',
                'phone' => '+229 97 00 00 02',
                'role'  => 'accueil',
            ],
            [
                'email' => 'kofi@garage.local',
                'name'  => 'Kofi Mécanicien',
                'phone' => '+229 97 00 00 03',
                'role'  => 'mecanicien',
            ],
            [
                'email' => 'seydou@garage.local',
                'name'  => 'Seydou Alabi',
                'phone' => '+229 97 00 00 04',
                'role'  => 'mecanicien',
            ],
        ];

        $createdUsers = [];

        foreach ($users as $u) {
            $createdUsers[$u['email']] = User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name'      => $u['name'],
                    'phone'     => $u['phone'],
                    'role'      => $u['role'],
                    'password'  => Hash::make('password'),
                    'is_active' => true,
                ]
            );
        }

        $patron  = $createdUsers['patron@garage.local'];
        $accueil = $createdUsers['accueil@garage.local'];
        $mec1    = $createdUsers['kofi@garage.local'];
        $mec2    = $createdUsers['seydou@garage.local'];

        // ── CLIENTS ───────────────────────────────────────────
        $clientsData = [
            ['prenom' => 'Jean', 'nom' => 'AGOSSOU', 'telephone' => '+229 96 11 22 33', 'email' => 'jean.agossou@email.com', 'adresse' => 'Cotonou'],
            ['prenom' => 'Fatima', 'nom' => 'HOUNSA', 'telephone' => '+229 96 44 55 66', 'email' => null, 'adresse' => 'Cotonou'],
            ['prenom' => 'Rodrigue', 'nom' => 'KPOSSOU', 'telephone' => '+229 97 77 88 99', 'email' => 'r.kpossou@mail.com', 'adresse' => 'Porto-Novo'],
            ['prenom' => 'Aminata', 'nom' => 'CISSE', 'telephone' => '+229 96 22 33 44', 'email' => null, 'adresse' => 'Cotonou'],
            ['prenom' => 'Emmanuel', 'nom' => 'DOSSOU', 'telephone' => '+229 97 55 66 77', 'email' => 'e.dossou@gmail.com', 'adresse' => 'Cotonou'],
        ];

        $clients = [];

        foreach ($clientsData as $c) {
            $clients[] = Client::create($c + ['created_by' => $accueil->id]);
        }

        // ── VEHICULES ─────────────────────────────────────────
        $vehicles = [
            ['client_id' => $clients[0]->id, 'immatriculation' => 'BJ-1234-AA', 'marque' => 'Toyota', 'modele' => 'Corolla', 'annee' => 2018, 'kilometrage' => 85000, 'couleur' => 'Blanc'],
            ['client_id' => $clients[1]->id, 'immatriculation' => 'BJ-5678-BB', 'marque' => 'Honda', 'modele' => 'CR-V', 'annee' => 2020, 'kilometrage' => 42000, 'couleur' => 'Gris'],
            ['client_id' => $clients[2]->id, 'immatriculation' => 'BJ-9012-CC', 'marque' => 'Peugeot', 'modele' => '308', 'annee' => 2016, 'kilometrage' => 121000, 'couleur' => 'Rouge'],
            ['client_id' => $clients[3]->id, 'immatriculation' => 'BJ-3456-DD', 'marque' => 'Toyota', 'modele' => 'Hilux', 'annee' => 2019, 'kilometrage' => 63000, 'couleur' => 'Noir'],
            ['client_id' => $clients[4]->id, 'immatriculation' => 'BJ-7890-EE', 'marque' => 'Renault', 'modele' => 'Duster', 'annee' => 2017, 'kilometrage' => 98000, 'couleur' => 'Blanc'],
        ];

        $vehiclesCreated = [];

        foreach ($vehicles as $v) {
            $vehiclesCreated[] = Vehicle::create($v);
        }

        // ── ORDRES DE REPARATION ──────────────────────────────
        $orders = [
            [
                'client_id' => $clients[0]->id,
                'vehicle_id' => $vehiclesCreated[0]->id,
                'created_by' => $accueil->id,
                'assigned_to' => $mec1->id,
                'description_panne' => 'Freins usés + fuite huile',
                'pieces_estimees' => 'Plaquettes + joint',
                'cout_estime' => 45000,
                'statut' => 'en_cours',
                'urgence' => 'urgent',
                'date_entree' => now()->subDays(2),
                'date_sortie_prevue' => now()->addDay(),
                'kilometrage_entree' => 85200,
            ],
        ];

        $ordersCreated = [];

        foreach ($orders as $o) {
            $ordersCreated[] = RepairOrder::create($o);
        }

        // ── NOTES ─────────────────────────────────────────────
        InterventionNote::create([
            'repair_order_id' => $ordersCreated[0]->id,
            'user_id' => $mec1->id,
            'contenu' => 'Diagnostic effectué, pièces à remplacer.',
            'ancien_statut' => 'nouveau',
            'nouveau_statut' => 'en_cours',
        ]);

        $this->command->info('✅ Seed exécuté avec succès (SAFE MODE)');
    }
}