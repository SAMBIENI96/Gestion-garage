<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\RepairOrderController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Auth routes (guests only)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
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

    // ── Gestion utilisateurs (patron uniquement) ───────────────
    Route::middleware('role:patron')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::post('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])
             ->name('users.toggleActive');
    });
});
