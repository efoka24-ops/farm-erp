<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\Incident;
use App\Models\Pesee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Dashboard exécutif mobile (T026) : chiffres clés + 3 alertes prioritaires,
 * conçu pour un écran d'accueil léger (< 2s/3G, cf. T099) — une seule requête,
 * pas de calculs coûteux.
 */
class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $chiffresCles = [
            'total_animaux' => Animal::where('statut', 'actif')->count(),
            'incidents_ouverts' => Incident::where('statut', '!=', 'resolu')->count(),
            'pesees_7_jours' => Pesee::where('date_pesee', '>=', now()->subDays(7))->count(),
        ];

        // orderByRaw portable (MySQL prod + SQLite dev) : évite FIELD(), spécifique MySQL.
        $alertes = Incident::query()
            ->where('statut', '!=', 'resolu')
            ->orderByRaw("CASE gravite WHEN 'critique' THEN 0 WHEN 'moyenne' THEN 1 ELSE 2 END")
            ->orderByDesc('created_at')
            ->limit(3)
            ->with('animal:id,tru_trace_id,espece')
            ->get(['id', 'animal_id', 'type', 'gravite', 'description', 'created_at']);

        return response()->json([
            'chiffres_cles' => $chiffresCles,
            'alertes_prioritaires' => $alertes,
        ]);
    }
}
