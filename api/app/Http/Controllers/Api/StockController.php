<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CategorieStock;
use App\Services\Stock\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function indexCategories(Request $request, StockService $stock): JsonResponse
    {
        $categories = CategorieStock::where('exploitation_id', $request->user()->exploitation_id)
            ->get()
            ->map(fn (CategorieStock $c) => [
                ...$c->toArray(),
                'niveau_actuel' => $stock->niveauActuel($c),
            ]);

        return response()->json($categories);
    }

    public function storeCategorie(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:aliment,medicament,intrant,autre'],
            'unite' => ['sometimes', 'string', 'max:20'],
            'seuil_alerte_quantite' => ['required', 'numeric', 'min:0'],
            'fournisseur_nom' => ['nullable', 'string', 'max:150'],
            'fournisseur_contact' => ['nullable', 'string', 'max:100'],
        ]);

        $categorie = CategorieStock::create([
            ...$data,
            'exploitation_id' => $request->user()->exploitation_id,
        ]);

        return response()->json($categorie, 201);
    }

    public function entrer(Request $request, CategorieStock $categorie, StockService $stock): JsonResponse
    {
        $data = $request->validate([
            'quantite' => ['required', 'numeric', 'min:0.01'],
            'cout_unitaire' => ['required', 'numeric', 'min:0'],
            'date_reception' => ['required', 'date'],
            'date_peremption' => ['nullable', 'date', 'after:date_reception'],
        ]);

        $lot = $stock->entrer(
            $categorie,
            $data['quantite'],
            $data['cout_unitaire'],
            $data['date_reception'],
            $data['date_peremption'] ?? null,
        );

        return response()->json($lot, 201);
    }

    /** Alertes seuil de commande (T056) + péremption (T057). */
    public function alertes(Request $request, StockService $stock): JsonResponse
    {
        $exploitationId = $request->user()->exploitation_id;

        return response()->json([
            'sous_seuil' => $stock->categoriesSousSeuil($exploitationId),
            'peremption_proche' => $stock->lotsPerissablesSous($exploitationId, $request->integer('jours', 30)),
        ]);
    }
}
