<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\Incident;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Écran mobile "Signaler un animal malade" (T024) : formulaire minimal côté
 * client (type + gravité pré-remplis à "maladie"/"moyenne"), un seul champ
 * obligatoire (animal_id), pour rester utilisable en une touche sur le terrain.
 */
class IncidentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $incidents = Incident::query()
            ->when($request->query('statut'), fn ($q, $s) => $q->where('statut', $s))
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json($incidents);
    }

    public function store(Request $request, Animal $animal): JsonResponse
    {
        $data = $request->validate([
            'type' => ['sometimes', 'in:maladie,blessure,mortalite,comportement,autre'],
            'gravite' => ['sometimes', 'in:faible,moyenne,critique'],
            'description' => ['nullable', 'string'],
            'photo_path' => ['nullable', 'string'],
        ]);

        $incident = $animal->incidents()->create([
            'type' => $data['type'] ?? 'maladie',
            'gravite' => $data['gravite'] ?? 'moyenne',
            'description' => $data['description'] ?? null,
            'photo_path' => $data['photo_path'] ?? null,
            'statut' => 'ouvert',
            'exploitation_id' => $animal->exploitation_id,
            'signale_par' => $request->user()->id,
        ]);

        if ($incident->type === 'mortalite') {
            $animal->update(['statut' => 'mort']);
        }

        return response()->json($incident, 201);
    }

    public function update(Request $request, Incident $incident): JsonResponse
    {
        $data = $request->validate([
            'statut' => ['required', 'in:ouvert,en_traitement,resolu'],
        ]);

        $incident->update($data);

        return response()->json($incident);
    }
}
