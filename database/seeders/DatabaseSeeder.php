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
        // ── Utilisateurs ──────────────────────────────────────
        $patron = User::create([
            'name'      => 'Thomas Patron',
            'email'     => 'patron@garage.local',
            'phone'     => '+229 97 00 00 01',
            'role'      => 'patron',
            'password'  => Hash::make('password'),
            'is_active' => true,
        ]);

        $accueil = User::create([
            'name'      => 'Marie Accueil',
            'email'     => 'accueil@garage.local',
            'phone'     => '+229 97 00 00 02',
            'role'      => 'accueil',
            'password'  => Hash::make('password'),
            'is_active' => true,
        ]);

        $mec1 = User::create([
            'name'      => 'Kofi Mécanicien',
            'email'     => 'kofi@garage.local',
            'phone'     => '+229 97 00 00 03',
            'role'      => 'mecanicien',
            'password'  => Hash::make('password'),
            'is_active' => true,
        ]);

        $mec2 = User::create([
            'name'      => 'Seydou Alabi',
            'email'     => 'seydou@garage.local',
            'phone'     => '+229 97 00 00 04',
            'role'      => 'mecanicien',
            'password'  => Hash::make('password'),
            'is_active' => true,
        ]);

        // ── Clients ───────────────────────────────────────────
        $clients = [
            ['prenom' => 'Jean', 'nom' => 'AGOSSOU', 'telephone' => '+229 96 11 22 33', 'email' => 'jean.agossou@email.com', 'adresse' => 'Cotonou, Akpakpa'],
            ['prenom' => 'Fatima', 'nom' => 'HOUNSA', 'telephone' => '+229 96 44 55 66', 'email' => null, 'adresse' => 'Cotonou, Fidjrossè'],
            ['prenom' => 'Rodrigue', 'nom' => 'KPOSSOU', 'telephone' => '+229 97 77 88 99', 'email' => 'r.kpossou@mail.com', 'adresse' => 'Porto-Novo'],
            ['prenom' => 'Aminata', 'nom' => 'CISSE', 'telephone' => '+229 96 22 33 44', 'email' => null, 'adresse' => 'Cotonou, Cadjehoun'],
            ['prenom' => 'Emmanuel', 'nom' => 'DOSSOU', 'telephone' => '+229 97 55 66 77', 'email' => 'e.dossou@gmail.com', 'adresse' => 'Cotonou, Godomey'],
        ];

        $clientModels = [];
        foreach ($clients as $data) {
            $clientModels[] = Client::create(array_merge($data, ['created_by' => $accueil->id]));
        }

        // ── Véhicules ─────────────────────────────────────────
        $vehicles = [
            ['client_id' => $clientModels[0]->id, 'immatriculation' => 'BJ-1234-AA', 'marque' => 'Toyota', 'modele' => 'Corolla', 'annee' => 2018, 'kilometrage' => 85000, 'couleur' => 'Blanc'],
            ['client_id' => $clientModels[0]->id, 'immatriculation' => 'BJ-5678-BB', 'marque' => 'Honda', 'modele' => 'CR-V', 'annee' => 2020, 'kilometrage' => 42000, 'couleur' => 'Gris'],
            ['client_id' => $clientModels[1]->id, 'immatriculation' => 'BJ-9012-CC', 'marque' => 'Peugeot', 'modele' => '308', 'annee' => 2016, 'kilometrage' => 121000, 'couleur' => 'Rouge'],
            ['client_id' => $clientModels[2]->id, 'immatriculation' => 'BJ-3456-DD', 'marque' => 'Toyota', 'modele' => 'Hilux', 'annee' => 2019, 'kilometrage' => 63000, 'couleur' => 'Noir'],
            ['client_id' => $clientModels[3]->id, 'immatriculation' => 'BJ-7890-EE', 'marque' => 'Renault', 'modele' => 'Duster', 'annee' => 2017, 'kilometrage' => 98000, 'couleur' => 'Blanc'],
            ['client_id' => $clientModels[4]->id, 'immatriculation' => 'BJ-2345-FF', 'marque' => 'Mercedes', 'modele' => 'C200', 'annee' => 2021, 'kilometrage' => 28000, 'couleur' => 'Argent'],
        ];

        $vehicleModels = [];
        foreach ($vehicles as $data) {
            $vehicleModels[] = Vehicle::create($data);
        }

        // ── Ordres de réparation ──────────────────────────────
        $orders = [
            [
                'client_id' => $clientModels[0]->id,
                'vehicle_id' => $vehicleModels[0]->id,
                'created_by' => $accueil->id,
                'assigned_to' => $mec1->id,
                'description_panne' => 'Freins avant usés, bruit de grincement lors du freinage. Client signale aussi une fuite d\'huile moteur.',
                'pieces_estimees' => 'Plaquettes de frein avant (x4), joint de carter moteur',
                'cout_estime' => 45000,
                'statut' => 'en_cours',
                'urgence' => 'urgent',
                'date_entree' => now()->subDays(2)->toDateString(),
                'date_sortie_prevue' => now()->addDay()->toDateString(),
                'kilometrage_entree' => 85200,
            ],
            [
                'client_id' => $clientModels[1]->id,
                'vehicle_id' => $vehicleModels[2]->id,
                'created_by' => $accueil->id,
                'assigned_to' => $mec2->id,
                'description_panne' => 'Vidange moteur et filtre à huile. Contrôle général : pneus, niveaux, freins.',
                'pieces_estimees' => 'Huile moteur 5W40 (5L), filtre à huile, filtre à air',
                'cout_estime' => 28000,
                'statut' => 'en_attente_pieces',
                'urgence' => 'normal',
                'date_entree' => now()->subDay()->toDateString(),
                'date_sortie_prevue' => now()->addDays(2)->toDateString(),
                'kilometrage_entree' => 121500,
            ],
            [
                'client_id' => $clientModels[2]->id,
                'vehicle_id' => $vehicleModels[3]->id,
                'created_by' => $accueil->id,
                'assigned_to' => null,
                'description_panne' => 'Batterie faible, démarrage difficile le matin. Vérification alternateur demandée.',
                'pieces_estimees' => 'Batterie 12V 70Ah',
                'cout_estime' => 55000,
                'statut' => 'nouveau',
                'urgence' => 'vip',
                'date_entree' => now()->toDateString(),
                'date_sortie_prevue' => now()->addDays(1)->toDateString(),
                'kilometrage_entree' => 63200,
            ],
            [
                'client_id' => $clientModels[4]->id,
                'vehicle_id' => $vehicleModels[5]->id,
                'created_by' => $accueil->id,
                'assigned_to' => $mec1->id,
                'description_panne' => 'Climatisation ne refroidit plus. Recharge gaz et vérification compresseur.',
                'pieces_estimees' => 'Gaz R134a, joint de compresseur si nécessaire',
                'cout_estime' => 35000,
                'statut' => 'termine',
                'urgence' => 'normal',
                'date_entree' => now()->subDays(5)->toDateString(),
                'date_sortie_prevue' => now()->subDays(3)->toDateString(),
                'date_sortie_effective' => now()->subDays(3),
                'kilometrage_entree' => 28100,
            ],
        ];

        $orderModels = [];
        foreach ($orders as $data) {
            $orderModels[] = RepairOrder::create($data);
        }

        // Notes d'intervention sur le premier ordre
        InterventionNote::create([
            'repair_order_id' => $orderModels[0]->id,
            'user_id'         => $mec1->id,
            'contenu'         => 'Démontage roue avant droite effectué. Plaquettes usées à 5%. Remplacement immédiat nécessaire. Fuite huile confirmée au niveau du joint de carter.',
            'ancien_statut'   => 'nouveau',
            'nouveau_statut'  => 'en_cours',
        ]);

        InterventionNote::create([
            'repair_order_id' => $orderModels[0]->id,
            'user_id'         => $patron->id,
            'contenu'         => 'Assigné à Kofi — Priorité haute, client fidèle.',
            'ancien_statut'   => 'nouveau',
            'nouveau_statut'  => 'nouveau',
        ]);

        // Note sur l'ordre terminé
        InterventionNote::create([
            'repair_order_id' => $orderModels[3]->id,
            'user_id'         => $mec1->id,
            'contenu'         => 'Recharge gaz R134a effectuée (500g). Compresseur en bon état. Climatisation fonctionne parfaitement. Client satisfait.',
            'ancien_statut'   => 'en_cours',
            'nouveau_statut'  => 'termine',
        ]);

        $this->command->info('✅ Données de démonstration créées avec succès !');
        $this->command->table(
            ['Utilisateur', 'Email', 'Mot de passe', 'Rôle'],
            [
                ['Thomas Patron', 'patron@garage.local', 'password', 'Patron'],
                ['Marie Accueil', 'accueil@garage.local', 'password', 'Accueil'],
                ['Kofi Mécanicien', 'kofi@garage.local', 'password', 'Mécanicien'],
                ['Seydou Alabi', 'seydou@garage.local', 'password', 'Mécanicien'],
            ]
        );
    }
}
