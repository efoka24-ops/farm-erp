<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Planificateur\PlanificateurVentesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class PlanificateurVentesController extends Controller
{
    public function recommandations(Request $request, PlanificateurVentesService $service): JsonResponse
    {
        if (! $service->estConfigure()) {
            return response()->json([
                'message' => "Le microservice de recommandation IA n'est pas configuré sur ce serveur.",
                'recommandations' => [],
            ], 503);
        }

        try {
            $resultat = $service->recommander($request->user()->exploitation_id);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage(), 'recommandations' => []], 503);
        }

        return response()->json($resultat);
    }
}
