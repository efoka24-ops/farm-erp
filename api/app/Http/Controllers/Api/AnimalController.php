<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Module cheptel (T022) : CRUD animal (acquisitions = create, sorties = statut
 * vendu/mort/reforme via update). Toute mutation passe par le trait Auditable
 * et respecte la RLS applicative (ExploitationScope).
 */
class AnimalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $animaux = Animal::query()
            ->when($request->query('statut'), fn ($q, $statut) => $q->where('statut', $statut))
            ->when($request->query('espece'), fn ($q, $espece) => $q->where('espece', $espece))
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json($animaux);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'espece' => ['required', 'string', 'max:100'],
            'race' => ['nullable', 'string', 'max:100'],
            'sexe' => ['required', 'in:male,femelle'],
            'date_naissance' => ['nullable', 'date'],
            'mere_id' => ['nullable', 'uuid', 'exists:animaux,id'],
            'pere_id' => ['nullable', 'uuid', 'exists:animaux,id'],
            'description' => ['nullable', 'string'],
        ]);

        $animal = Animal::create([...$data, 'statut' => 'actif']);

        return response()->json($animal, 201);
    }

    public function show(Animal $animal): JsonResponse
    {
        return response()->json($animal->load('pesees', 'incidents', 'mere', 'pere'));
    }

    public function showByTruTrace(string $truTraceId): JsonResponse
    {
        // Recherche par ID TRU TRACE (scan QR terrain, T025) plutôt que par UUID technique.
        $animal = Animal::where('tru_trace_id', $truTraceId)->firstOrFail();

        return response()->json($animal->load('pesees', 'incidents'));
    }

    public function update(Request $request, Animal $animal): JsonResponse
    {
        $data = $request->validate([
            'race' => ['sometimes', 'string', 'max:100'],
            'statut' => ['sometimes', 'in:actif,vendu,mort,reforme'],
            'description' => ['sometimes', 'nullable', 'string'],
            'photo_path' => ['sometimes', 'nullable', 'string'],
        ]);

        $animal->update($data);

        return response()->json($animal);
    }

    public function destroy(Animal $animal): JsonResponse
    {
        $animal->delete();

        return response()->json(null, 204);
    }

    public function qr(Animal $animal): JsonResponse
    {
        // Le rendu visuel du QR est généré côté mobile ; l'API expose la valeur à encoder.
        return response()->json([
            'tru_trace_id' => $animal->tru_trace_id,
            'valeur_qr' => "trufarm://animal/{$animal->tru_trace_id}",
        ]);
    }
}
