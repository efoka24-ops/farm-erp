<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\Vente;
use App\Services\Ventes\VenteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VenteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            Vente::with('animal:id,tru_trace_id,espece', 'client:id,nom')
                ->orderByDesc('date_vente')
                ->paginate($request->integer('per_page', 20))
        );
    }

    public function store(Request $request, VenteService $venteService): JsonResponse
    {
        $data = $request->validate([
            'animal_id' => ['required', 'uuid', 'exists:animaux,id'],
            'client_id' => ['nullable', 'uuid', 'exists:clients,id'],
            'client_nom_libre' => ['nullable', 'string', 'max:150'],
            'montant' => ['required', 'numeric', 'min:0.01'],
            'tva_applicable' => ['sometimes', 'boolean'],
            'taux_tva_pourcent' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'mode_paiement' => ['sometimes', 'in:especes,banque,mtn_momo,differe'],
            'date_vente' => ['required', 'date'],
        ]);

        $animal = Animal::findOrFail($data['animal_id']);
        $data['mode_paiement'] ??= 'especes';

        try {
            $vente = $venteService->vendre($animal, $data, $request->user()->id);
        } catch (\RuntimeException $e) {
            throw ValidationException::withMessages(['animal_id' => [$e->getMessage()]]);
        }

        return response()->json($vente, 201);
    }

    public function telechargerBon(Vente $vente): StreamedResponse
    {
        return Storage::download($vente->bon_pdf_path, 'bon-vente.pdf');
    }
}
