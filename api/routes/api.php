<?php

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
});
