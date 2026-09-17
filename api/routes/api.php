<?php

use App\Http\Controllers\Api\AlimentationController;
use App\Http\Controllers\Api\AnimalController;
use App\Http\Controllers\Api\BonCommandeController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ComptabiliteController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DepenseController;
use App\Http\Controllers\Api\DossierFinancementController;
use App\Http\Controllers\Api\IncidentController;
use App\Http\Controllers\Api\PeseeController;
use App\Http\Controllers\Api\PlanificateurVentesController;
use App\Http\Controllers\Api\RecetteController;
use App\Http\Controllers\Api\ReproductionController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Api\TraitementController;
use App\Http\Controllers\Api\VaccinationController;
use App\Http\Controllers\Api\VenteController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Webhooks\MokineVetoWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Porte d'entrée publique : création de compte et démo restreinte (5 jours)
Route::post('/auth/register', [RegisterController::class, 'inscrire']);
Route::post('/auth/register-demo', [RegisterController::class, 'creerDemo']);

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/login/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/auth/login/pin', [AuthController::class, 'loginPin']);

// Webhook public (sécurisé par secret partagé, pas de session utilisateur)
Route::post('/webhooks/mokinevoto/consultations', [MokineVetoWebhookController::class, 'consultation']);

Route::middleware(['auth:sanctum', 'exploitation.active'])->group(function () {
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

    // Comptabilité OHADA (US2)
    Route::get('/depenses', [DepenseController::class, 'index']);
    Route::post('/depenses', [DepenseController::class, 'store']);
    Route::post('/depenses/{depense}/payer-mtn-momo', [DepenseController::class, 'payerMtnMomo'])
        ->middleware('role:gerant,comptable');

    Route::get('/recettes', [RecetteController::class, 'index']);
    Route::post('/recettes', [RecetteController::class, 'store']);

    Route::get('/comptabilite/plan-comptable', [ComptabiliteController::class, 'planComptable']);
    Route::get('/comptabilite/compte-resultat', [ComptabiliteController::class, 'compteDeResultat']);
    Route::get('/comptabilite/compte-resultat/pdf', [ComptabiliteController::class, 'compteDeResultatPdf']);
    Route::get('/comptabilite/bilan', [ComptabiliteController::class, 'bilan']);
    Route::get('/comptabilite/bilan/pdf', [ComptabiliteController::class, 'bilanPdf']);
    Route::get('/comptabilite/tresorerie', [ComptabiliteController::class, 'tresorerie']);
    Route::get('/comptabilite/mouvements/export-csv', [ComptabiliteController::class, 'exportMouvementsCsv']);
    Route::get('/animaux/{animal}/cout-revient', [ComptabiliteController::class, 'coutRevient']);
    Route::get('/animaux/{animal}/seuil-rentabilite', [ComptabiliteController::class, 'seuilRentabilite']);

    // Santé vétérinaire (US4)
    Route::get('/animaux/{animal}/vaccinations', [VaccinationController::class, 'index']);
    Route::post('/vaccinations/{vaccination}/administrer', [VaccinationController::class, 'administrer']);
    Route::get('/vaccinations/alertes', [VaccinationController::class, 'alertes']);
    Route::post('/vaccinations/campagne', [VaccinationController::class, 'campagne']);

    Route::post('/animaux/{animal}/traitements', [TraitementController::class, 'store']);
    Route::get('/animaux/{animal}/dma', [TraitementController::class, 'dma']);

    // Dossier de financement (US3)
    Route::get('/financement/completude', [DossierFinancementController::class, 'verifierCompletude']);
    Route::get('/financement/dossiers', [DossierFinancementController::class, 'index']);
    Route::post('/financement/dossiers', [DossierFinancementController::class, 'store']);
    Route::get('/financement/dossiers/{dossier}/telecharger', [DossierFinancementController::class, 'telecharger']);
    Route::get('/financement/dossiers/{dossier}/verifier', [DossierFinancementController::class, 'verifierSignature']);

    // Reproduction (US8)
    Route::get('/saillies', [ReproductionController::class, 'index']);
    Route::post('/saillies', [ReproductionController::class, 'storeSaillie']);
    Route::post('/saillies/{saillie}/mise-bas', [ReproductionController::class, 'storeMiseBas']);
    Route::get('/saillies/alertes', [ReproductionController::class, 'alertes']);

    // Stocks & réapprovisionnement (US9)
    Route::get('/stock/categories', [StockController::class, 'indexCategories']);
    Route::post('/stock/categories', [StockController::class, 'storeCategorie']);
    Route::post('/stock/categories/{categorie}/entrees', [StockController::class, 'entrer']);
    Route::get('/stock/alertes', [StockController::class, 'alertes']);
    Route::post('/stock/categories/{categorie}/bons-commande', [BonCommandeController::class, 'store']);
    Route::get('/stock/bons-commande/{bonCommande}/telecharger', [BonCommandeController::class, 'telecharger']);

    // Ventes & traçabilité TRU TRACE (US10)
    Route::get('/clients', [ClientController::class, 'index']);
    Route::post('/clients', [ClientController::class, 'store']);
    Route::get('/ventes', [VenteController::class, 'index']);
    Route::post('/ventes', [VenteController::class, 'store']);
    Route::get('/ventes/{vente}/bon', [VenteController::class, 'telechargerBon']);

    // Planificateur de ventes intelligent (US5)
    Route::get('/planificateur-ventes/recommandations', [PlanificateurVentesController::class, 'recommandations']);
});
