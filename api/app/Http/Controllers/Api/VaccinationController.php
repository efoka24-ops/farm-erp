<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\Vaccination;
use App\Services\Sante\CalendrierVaccinalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VaccinationController extends Controller
{
    public function index(Animal $animal): JsonResponse
    {
        return response()->json($animal->vaccinations()->orderByDesc('date_prevue')->get());
    }

    public function administrer(Request $request, Vaccination $vaccination): JsonResponse
    {
        $data = $request->validate(['date_administration' => ['sometimes', 'date']]);

        $vaccination->update([
            'statut' => 'administre',
            'date_administration' => $data['date_administration'] ?? now()->toDateString(),
            'administre_par' => $request->user()->id,
        ]);

        return response()->json($vaccination);
    }

    /** Alertes J-14/J-3 (T049). */
    public function alertes(Request $request, CalendrierVaccinalService $service): JsonResponse
    {
        $exploitationId = $request->user()->exploitation_id;

        return response()->json([
            'j_14' => $service->vaccinationsAAlerterDans($exploitationId, 14),
            'j_3' => $service->vaccinationsAAlerterDans($exploitationId, 3),
        ]);
    }

    /**
     * Campagne de vaccination groupée (T050) : enregistre l'administration
     * d'un même vaccin pour plusieurs animaux en un seul appel.
     */
    public function campagne(Request $request): JsonResponse
    {
        $data = $request->validate([
            'animal_ids' => ['required', 'array', 'min:1'],
            'animal_ids.*' => ['uuid', 'exists:animaux,id'],
            'vaccin' => ['required', 'string', 'max:100'],
            'date_administration' => ['required', 'date'],
        ]);

        $campagneId = (string) Str::uuid();
        $user = $request->user();

        $vaccinations = collect($data['animal_ids'])->map(fn ($animalId) => Vaccination::create([
            'exploitation_id' => $user->exploitation_id,
            'animal_id' => $animalId,
            'vaccin' => $data['vaccin'],
            'date_administration' => $data['date_administration'],
            'statut' => 'administre',
            'campagne_id' => $campagneId,
            'administre_par' => $user->id,
        ]));

        return response()->json([
            'campagne_id' => $campagneId,
            'nombre_animaux' => $vaccinations->count(),
            'vaccinations' => $vaccinations,
        ], 201);
    }
}
