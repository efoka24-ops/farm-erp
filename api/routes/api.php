<?php

use App\Http\Controllers\Api\AlimentationController;
use App\Http\Controllers\Api\AnimalController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\IncidentController;
use App\Http\Controllers\Api\PeseeController;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/login/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/auth/login/pin', [AuthController::class, 'loginPin']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/pin', [AuthController::class, 'definirPin']);

    Route::prefix('sync')->group(function () {
        Route::post('/push', [SyncController::class, 'push']);
        Route::get('/pull', [SyncController::class, 'pull']);
        Route::get('/conflits', [SyncController::class, 'listerConflits']);
        Route::post('/conflits/{conflit}/resoudre', [SyncController::class, 'resoudreConflit'])
            ->middleware('role:gerant');
    });

    // Cheptel (US1)
    Route::get('/animaux/trace/{truTraceId}', [AnimalController::class, 'showByTruTrace']);
    Route::apiResource('animaux', AnimalController::class)->parameters(['animaux' => 'animal']);
    Route::get('/animaux/{animal}/qr', [AnimalController::class, 'qr']);
    Route::get('/animaux/{animal}/pesees', [PeseeController::class, 'index']);
    Route::post('/animaux/{animal}/pesees', [PeseeController::class, 'store']);
    Route::post('/animaux/{animal}/incidents', [IncidentController::class, 'store']);

    Route::get('/incidents', [IncidentController::class, 'index']);
    Route::patch('/incidents/{incident}', [IncidentController::class, 'update']);

    Route::get('/alimentation', [AlimentationController::class, 'index']);
    Route::post('/alimentation', [AlimentationController::class, 'store']);

    Route::get('/dashboard', [DashboardController::class, 'index']);
});
