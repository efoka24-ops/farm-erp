<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\CategorieStock;
use App\Models\Traitement;
use App\Services\Stock\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TraitementController extends Controller
{
    public function store(Request $request, Animal $animal, StockService $stock): JsonResponse
    {
        $data = $request->validate([
            'medicament' => ['required', 'string', 'max:150'],
            'categorie_stock_id' => ['nullable', 'uuid', 'exists:categories_stock,id'],
            'quantite' => ['required_with:categorie_stock_id', 'nullable', 'numeric', 'min:0.01'],
            'motif' => ['nullable', 'string'],
            'date_debut' => ['required', 'date'],
            'delai_attente_jours' => ['required', 'integer', 'min:0', 'max:365'],
        ]);

        $traitement = $animal->traitements()->create([
            ...collect($data)->except('quantite')->all(),
            'exploitation_id' => $animal->exploitation_id,
            'administre_par' => $request->user()->id,
        ]);

        if (! empty($data['categorie_stock_id'])) {
            $categorie = CategorieStock::findOrFail($data['categorie_stock_id']);

            try {
                $stock->sortir($categorie, (float) $data['quantite'], Traitement::class, $traitement->id);
            } catch (\RuntimeException $e) {
                throw ValidationException::withMessages(['categorie_stock_id' => [$e->getMessage()]]);
            }
        }

        return response()->json($traitement, 201);
    }

    /** DMA (Dossier Médical Animal) : vue consolidée santé d'un animal. */
    public function dma(Animal $animal): JsonResponse
    {
        return response()->json([
            'animal' => $animal->only('id', 'tru_trace_id', 'espece', 'statut'),
            'peut_etre_vendu' => $animal->peutEtreVendu(),
            'vaccinations' => $animal->vaccinations()->orderByDesc('date_prevue')->get(),
            'traitements' => $animal->traitements()->orderByDesc('date_debut')->get(),
            'consultations' => $animal->consultationsVeterinaires()->orderByDesc('date_consultation')->get(),
        ]);
    }
}
