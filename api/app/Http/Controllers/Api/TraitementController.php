<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TraitementController extends Controller
{
    public function store(Request $request, Animal $animal): JsonResponse
    {
        $data = $request->validate([
            'medicament' => ['required', 'string', 'max:150'],
            'motif' => ['nullable', 'string'],
            'date_debut' => ['required', 'date'],
            'delai_attente_jours' => ['required', 'integer', 'min:0', 'max:365'],
        ]);

        $traitement = $animal->traitements()->create([
            ...$data,
            'exploitation_id' => $animal->exploitation_id,
            'administre_par' => $request->user()->id,
        ]);

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
