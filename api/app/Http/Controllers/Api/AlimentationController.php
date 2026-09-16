<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\DistributionAlimentation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Module alimentation (T023) : enregistrement des distributions. La déduction
 * de stock réelle arrive avec le module stocks (US9, Phase 8) — ici on trace
 * la quantité distribuée pour permettre le calcul de coût alimentaire (US2/US5)
 * dès que le stock existera, sans bloquer le MVP terrain.
 */
class AlimentationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $distributions = DistributionAlimentation::query()
            ->when($request->query('animal_id'), fn ($q, $id) => $q->where('animal_id', $id))
            ->orderByDesc('date_distribution')
            ->paginate($request->integer('per_page', 20));

        return response()->json($distributions);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'animal_id' => ['nullable', 'uuid', 'exists:animaux,id'],
            'aliment' => ['required', 'string', 'max:100'],
            'quantite_kg' => ['required', 'numeric', 'min:0.01', 'max:99999'],
            'date_distribution' => ['required', 'date'],
        ]);

        if (! empty($data['animal_id'])) {
            Animal::findOrFail($data['animal_id']); // vérifie l'appartenance via la RLS applicative
        }

        $distribution = DistributionAlimentation::create([
            ...$data,
            'exploitation_id' => $request->user()->exploitation_id,
            'saisi_par' => $request->user()->id,
        ]);

        return response()->json($distribution, 201);
    }
}
