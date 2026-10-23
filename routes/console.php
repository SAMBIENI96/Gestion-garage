<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Artisan;

// Données de démonstration, seulement sur une base vide :
// un redémarrage ne remet jamais les mots de passe à zéro.
Artisan::command('demo:seed', function () {
    if (User::exists()) {
        $this->info('Base déjà initialisée : données de démo ignorées.');
        return;
    }

    $this->call('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true]);
})->purpose('Charge les comptes et données de démonstration si la base est vide');

// Remise à zéro des données de démonstration (mode démo uniquement)
Artisan::command('demo:reset {--force : Exécuter même si DEMO_MODE est désactivé}', function () {
    if (! \App\Support\Demo::enabled() && ! $this->option('force')) {
        $this->error('DEMO_MODE est désactivé : rien n\'a été effacé.');
        return 1;
    }

    \App\Support\Demo::reset();
    $this->info('Données de démonstration réinitialisées.');
})->purpose('Efface clients, véhicules, interventions et factures puis recharge la démo');
