<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exploitation;
use App\Services\Cooperative\ConsentementRequisException;
use App\Services\Cooperative\CooperativeAggregationService;
use App\Services\Minagri\DetectionEligibiliteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Module coopérative Girinka (T078) : une coopérative est une exploitation
 * de type `cooperative` ; les membres sont des exploitations indépendantes
 * (leurs propres utilisateurs, leurs propres données) qui pointent vers
 * elle via `cooperative_id`. Aucun accès individuel sans consentement
 * explicite (T079), même pour le gestionnaire de la coopérative.
 */
class CooperativeController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:150'],
            'region' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'max_membres' => ['required', 'integer', 'min:1', 'max:200'],
        ]);

        $cooperative = Exploitation::create([...$data, 'type' => 'cooperative']);

        return response()->json($cooperative, 201);
    }

    /** Un gérant fait adhérer son exploitation à une coopérative existante. */
    public function rejoindre(Request $request, DetectionEligibiliteService $detectionEligibilite): JsonResponse
    {
        $data = $request->validate(['cooperative_id' => ['required', 'uuid', 'exists:exploitations,id']]);

        $cooperative = Exploitation::findOrFail($data['cooperative_id']);

        if ($cooperative->type !== 'cooperative') {
            abort(422, "Cette exploitation n'est pas une coopérative.");
        }

        $nombreMembresActuels = $cooperative->membres()->count();
        if ($nombreMembresActuels >= $cooperative->max_membres) {
            abort(422, 'Cette coopérative a atteint son nombre maximum de membres.');
        }

        $exploitation = $request->user()->exploitation;
        $exploitation->update(['cooperative_id' => $cooperative->id]);

        $detectionEligibilite->relancerPour($exploitation->fresh());

        return response()->json($exploitation->fresh());
    }

    /** Consentement explicite du membre au partage de ses données (T079). */
    public function consentement(Request $request): JsonResponse
    {
        $data = $request->validate(['consentement' => ['required', 'boolean']]);

        $exploitation = $request->user()->exploitation;
        $exploitation->update([
            'consentement_cooperative_donne_le' => $data['consentement'] ? now() : null,
        ]);

        return response()->json($exploitation->fresh());
    }

    public function rapport(Request $request, Exploitation $cooperative, CooperativeAggregationService $service): JsonResponse
    {
        $this->autoriserAccesCooperative($request, $cooperative);

        return response()->json($service->rapportAgrege($cooperative));
    }

    public function detailMembre(Request $request, Exploitation $cooperative, Exploitation $membre, CooperativeAggregationService $service): JsonResponse
    {
        $this->autoriserAccesCooperative($request, $cooperative);

        try {
            return response()->json($service->detailMembre($cooperative, $membre));
        } catch (ConsentementRequisException $e) {
            abort(403, $e->getMessage());
        }
    }

    private function autoriserAccesCooperative(Request $request, Exploitation $cooperative): void
    {
        if ($request->user()->exploitation_id !== $cooperative->id) {
            abort(403, "Vous n'appartenez pas à cette coopérative.");
        }
    }
}
