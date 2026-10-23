<?php

namespace App\Support;

use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Mode démonstration : connexion sans identifiants, actions sensibles bloquées,
// données remises à zéro automatiquement (DEMO_MODE=true).
class Demo
{
    public const PASSWORD = 'password';

    public const ACCOUNTS = [
        'patron'      => ['email' => 'patron@garage.local',  'name' => 'Thomas Patron',   'role' => 'patron',     'phone' => '+229 97 00 00 01'],
        'accueil'     => ['email' => 'accueil@garage.local', 'name' => 'Marie Accueil',   'role' => 'accueil',    'phone' => '+229 97 00 00 02'],
        'mecanicien'  => ['email' => 'kofi@garage.local',    'name' => 'Kofi Mécanicien', 'role' => 'mecanicien', 'phone' => '+229 97 00 00 03'],
        'mecanicien2' => ['email' => 'seydou@garage.local',  'name' => 'Seydou Alabi',    'role' => 'mecanicien', 'phone' => '+229 97 00 00 04'],
    ];

    public static function enabled(): bool
    {
        return (bool) config('app.demo');
    }

    public static function isDemoUser(?User $user): bool
    {
        return $user !== null && in_array($user->email, array_column(self::ACCOUNTS, 'email'), true);
    }

    /** Compte de démo pour un rôle, en (re)créant les données si nécessaire. */
    public static function account(string $role): User
    {
        self::resetIfStale();

        return User::where('email', self::ACCOUNTS[$role]['email'])->firstOrFail();
    }

    /** Réinitialise si la dernière remise à zéro date de plus de DEMO_RESET_HOURS heures. */
    public static function resetIfStale(): void
    {
        $last = Cache::get('demo:last_reset');
        $hours = (int) config('app.demo_reset_hours', 24);

        if (! $last || now()->diffInHours($last, absolute: true) >= $hours || ! User::where('email', self::ACCOUNTS['patron']['email'])->exists()) {
            Cache::lock('demo:reset', 60)->get(fn () => self::reset());
        }
    }

    /** Efface les données métier (pas les sessions) et recharge le jeu de démonstration. */
    public static function reset(): void
    {
        DB::transaction(function () {
            foreach (['intervention_notes', 'invoices', 'repair_orders', 'vehicles', 'clients', 'document_sequences'] as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->delete();
                }
            }
            (new DemoSeeder)->run();
        });

        Cache::forever('demo:last_reset', now());
    }
}
