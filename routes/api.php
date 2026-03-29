<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MecanicienApiController;

Route::post('/login',  [MecanicienApiController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout',                    [MecanicienApiController::class, 'logout']);
    Route::get('/mes-taches',                 [MecanicienApiController::class, 'mesTaches']);
    Route::get('/mes-taches-terminees',       [MecanicienApiController::class, 'mesTachesTerminees']);
    Route::get('/taches/{repair}',            [MecanicienApiController::class, 'tacheDetail']);
    Route::post('/taches/{repair}/statut',    [MecanicienApiController::class, 'updateStatut']);
    Route::post('/taches/{repair}/note',      [MecanicienApiController::class, 'addNote']);
});

