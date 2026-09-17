<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\MiseBas;
use App\Models\Saillie;
use App\Services\Reproduction\GestationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Module reproduction (T053) : saillies, mises bas, tableau de reproduction.
 */
class ReproductionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $saillies = Saillie::query()
            ->when($request->query('statut'), fn ($q, $s) => $q->where('statut', $s))
            ->with('femelle:id,tru_trace_id,espece', 'reproducteur:id,tru_trace_id', 'miseBas')
            ->orderByDesc('date_saillie')
            ->paginate($request->integer('per_page', 20));

        return response()->json($saillies);
    }

    public function storeSaillie(Request $request, GestationService $gestation): JsonResponse
    {
        $data = $request->validate([
            'femelle_id' => ['required', 'uuid', 'exists:animaux,id'],
            'reproducteur_id' => ['nullable', 'uuid', 'exists:animaux,id'],
            'date_saillie' => ['required', 'date'],
        ]);

        $femelle = Animal::findOrFail($data['femelle_id']);

        $saillie = Saillie::create([
            ...$data,
            'exploitation_id' => $femelle->exploitation_id,
            'date_prevue_mise_bas' => $gestation->datePrevueMiseBas($femelle->espece, Carbon::parse($data['date_saillie'])),
            'statut' => 'en_cours',
            'saisi_par' => $request->user()->id,
        ]);

        return response()->json($saillie, 201);
    }

    /**
     * Mise bas (T055) : crée automatiquement une fiche animal par nouveau-né
     * survivant, avec filiation (mère/père) et ID TRU TRACE généré.
     */
    public function storeMiseBas(Request $request, Saillie $saillie): JsonResponse
    {
        $data = $request->validate([
            'date_mise_bas' => ['required', 'date'],
            'nombre_nes' => ['required', 'integer', 'min:0', 'max:20'],
            'nombre_survivants' => ['required', 'integer', 'min:0', 'lte:nombre_nes'],
            'notes' => ['nullable', 'string'],
            'sexes_nouveau_nes' => ['sometimes', 'array'],
            'sexes_nouveau_nes.*' => ['in:male,femelle'],
        ]);

        $miseBas = MiseBas::create([
            ...collect($data)->except('sexes_nouveau_nes')->all(),
            'exploitation_id' => $saillie->exploitation_id,
            'saillie_id' => $saillie->id,
        ]);

        $sexes = $data['sexes_nouveau_nes'] ?? [];
        $nouveauNes = [];

        for ($i = 0; $i < $data['nombre_survivants']; $i++) {
            $nouveauNes[] = Animal::create([
                'exploitation_id' => $saillie->exploitation_id,
                'espece' => $saillie->femelle->espece,
                'sexe' => $sexes[$i] ?? 'femelle',
                'date_naissance' => $data['date_mise_bas'],
                'statut' => 'actif',
                'mere_id' => $saillie->femelle_id,
                'pere_id' => $saillie->reproducteur_id,
                'mise_bas_id' => $miseBas->id,
            ]);
        }

        $saillie->update(['statut' => 'mise_bas']);

        return response()->json([
            'mise_bas' => $miseBas,
            'nouveau_nes' => $nouveauNes,
        ], 201);
    }

    /** Alertes J-14/J-7 avant mise bas (T054). */
    public function alertes(Request $request): JsonResponse
    {
        $exploitationId = $request->user()->exploitation_id;

        $requeteAlerte = fn (int $jours) => Saillie::where('exploitation_id', $exploitationId)
            ->where('statut', 'en_cours')
            ->whereDate('date_prevue_mise_bas', now()->addDays($jours)->toDateString())
            ->with('femelle:id,tru_trace_id,espece')
            ->get();

        return response()->json([
            'j_14' => $requeteAlerte(14),
            'j_7' => $requeteAlerte(7),
        ]);
    }
}
