<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\RepairOrderController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\MecanicienAppController;

/*
|--------------------------------------------------------------------------
| Auth routes (guests only)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');

    Route::get('/inscription',  [RegisterController::class, 'show'])->name('register');
    Route::post('/inscription', [RegisterController::class, 'store'])->middleware('throttle:5,1');

    Route::get('/auth/google',          [GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

    // Démo sans identifiants (DEMO_MODE=true)
    Route::post('/demo/{role}', [DemoController::class, 'login'])->name('demo.login')->middleware('throttle:20,1');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ── Paramètres (tous les rôles) ────────────────────────────
    Route::get('/parametres',               [SettingsController::class, 'edit'])->name('settings.edit');
    Route::patch('/parametres/theme',       [SettingsController::class, 'updateTheme'])->name('settings.theme');
    Route::patch('/parametres/profil',      [SettingsController::class, 'updateProfile'])->name('settings.profile')->middleware('demo.block');
    Route::put('/parametres/mot-de-passe',  [SettingsController::class, 'updatePassword'])->name('settings.password')->middleware('demo.block');

    // ── Clients (accueil + patron) ─────────────────────────────
    Route::middleware('role:patron,accueil')->group(function () {
        Route::resource('clients', ClientController::class);
        Route::get('/clients-search', [ClientController::class, 'search'])->name('clients.search');

        Route::resource('vehicles', VehicleController::class)->except(['destroy']);
        Route::get('/clients/{client}/vehicles', [VehicleController::class, 'byClient'])
             ->name('clients.vehicles');
    });

    // ── Ordres de réparation ───────────────────────────────────
    Route::middleware('role:patron,accueil')->group(function () {
        Route::resource('repairs', RepairOrderController::class)->except(['destroy']);
    });

    // Assigner mécanicien (patron uniquement)
    Route::post('/repairs/{repair}/assign', [RepairOrderController::class, 'assign'])
         ->name('repairs.assign')
         ->middleware('role:patron');

    // Mécanicien : passage de la page d'accueil à l'application mobile
    Route::get('/connexion/atelier', [MecanicienAppController::class, 'handoff'])
         ->name('mecanicien.handoff')
         ->middleware('role:mecanicien');

    // Mécanicien: vue de ses tâches
    Route::get('/mes-taches', [RepairOrderController::class, 'mecanicien'])
         ->name('repairs.mecanicien')
         ->middleware('role:mecanicien');

    // Mécanicien: mettre à jour statut (accueil peut aussi voir)
    Route::post('/repairs/{repair}/statut', [RepairOrderController::class, 'updateStatut'])
         ->name('repairs.updateStatut')
         ->middleware('role:mecanicien');

    // ── Planning (patron) ──────────────────────────────────────
    Route::get('/planning', [RepairOrderController::class, 'planning'])
         ->name('planning.index')
         ->middleware('role:patron');

    // ── Factures ───────────────────────────────────────────────
    Route::middleware('role:patron')->group(function () {
        Route::resource('invoices', InvoiceController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('/invoices/{invoice}/valider',       [InvoiceController::class, 'valider'])->name('invoices.valider');
        Route::post('/invoices/{invoice}/marquer-payee', [InvoiceController::class, 'marquerPayee'])->name('invoices.marquerPayee');
        Route::get('/invoices/{invoice}/print',          [InvoiceController::class, 'print'])->name('invoices.print');
    });

    // ── Gestion utilisateurs (patron uniquement, lecture seule en démo) ──
    Route::middleware(['role:patron', 'demo.block'])->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::post('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])
             ->name('users.toggleActive');
        Route::post('/users/{user}/approuver', [UserController::class, 'approve'])->name('users.approve');
        Route::delete('/users/{user}/refuser', [UserController::class, 'reject'])->name('users.reject');
    });
});

Route::get('/mecanicien', function () {
    return response()->file(public_path('mecanicien/index.html'));
});

Route::get('/mecanicien/', function () {
    return response()->file(public_path('mecanicien/index.html'));
});

