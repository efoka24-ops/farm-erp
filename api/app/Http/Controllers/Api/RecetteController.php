<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recette;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecetteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $recettes = Recette::query()
            ->with('compteOhada:id,code,libelle')
            ->when($request->query('debut'), fn ($q, $d) => $q->where('date_operation', '>=', $d))
            ->when($request->query('fin'), fn ($q, $d) => $q->where('date_operation', '<=', $d))
            ->when($request->query('animal_id'), fn ($q, $id) => $q->where('animal_id', $id))
            ->orderByDesc('date_operation')
            ->paginate($request->integer('per_page', 20));

        return response()->json($recettes);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'compte_ohada_id' => ['required', 'exists:comptes_ohada,id'],
            'montant' => ['required', 'numeric', 'min:0.01', 'max:999999999'],
            'date_operation' => ['required', 'date'],
            'libelle' => ['required', 'string', 'max:255'],
            'animal_id' => ['nullable', 'uuid', 'exists:animaux,id'],
            'mode_paiement' => ['sometimes', 'in:especes,banque,mtn_momo'],
            'reference_paiement' => ['nullable', 'string', 'max:100'],
        ]);

        $recette = Recette::create([
            ...$data,
            'mode_paiement' => $data['mode_paiement'] ?? 'especes',
            'exploitation_id' => $request->user()->exploitation_id,
            'saisi_par' => $request->user()->id,
        ]);

        return response()->json($recette->load('compteOhada'), 201);
    }
}
