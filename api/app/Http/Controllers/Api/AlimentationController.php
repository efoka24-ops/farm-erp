<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\CategorieStock;
use App\Models\DistributionAlimentation;
use App\Services\Stock\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Module alimentation (T023) : enregistrement des distributions. Déduction
 * de stock automatique (T059) si `categorie_stock_id` est renseigné ; sinon
 * simple enregistrement de la quantité (compatibilité US1, exploitations
 * n'ayant pas encore configuré leurs catégories de stock).
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

    public function store(Request $request, StockService $stock): JsonResponse
    {
        $data = $request->validate([
            'animal_id' => ['nullable', 'uuid', 'exists:animaux,id'],
            'categorie_stock_id' => ['nullable', 'uuid', 'exists:categories_stock,id'],
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

        if (! empty($data['categorie_stock_id'])) {
            $categorie = CategorieStock::findOrFail($data['categorie_stock_id']);

            try {
                $stock->sortir($categorie, (float) $data['quantite_kg'], DistributionAlimentation::class, $distribution->id);
            } catch (\RuntimeException $e) {
                throw ValidationException::withMessages(['categorie_stock_id' => [$e->getMessage()]]);
            }
        }

        return response()->json($distribution, 201);
    }
}
