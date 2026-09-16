<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Depense;
use App\Services\Paiement\MtnMomoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Saisie dépense simplifiée (T030) : l'exploitant choisit un compte OHADA
 * dans une liste courte (T029) plutôt que de connaître le plan comptable.
 */
class DepenseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $depenses = Depense::query()
            ->with('compteOhada:id,code,libelle')
            ->when($request->query('debut'), fn ($q, $d) => $q->where('date_operation', '>=', $d))
            ->when($request->query('fin'), fn ($q, $d) => $q->where('date_operation', '<=', $d))
            ->when($request->query('animal_id'), fn ($q, $id) => $q->where('animal_id', $id))
            ->orderByDesc('date_operation')
            ->paginate($request->integer('per_page', 20));

        return response()->json($depenses);
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

        $depense = Depense::create([
            ...$data,
            'mode_paiement' => $data['mode_paiement'] ?? 'especes',
            'exploitation_id' => $request->user()->exploitation_id,
            'saisi_par' => $request->user()->id,
        ]);

        return response()->json($depense->load('compteOhada'), 201);
    }

    /**
     * Déclenche le paiement MTN MoMo d'une dépense fournisseur déjà saisie
     * (T033) et enregistre la référence de transaction retournée.
     */
    public function payerMtnMomo(Request $request, Depense $depense, MtnMomoService $mtnMomo): JsonResponse
    {
        $data = $request->validate([
            'numero_destinataire' => ['required', 'string', 'regex:/^2507[0-9]{8}$/'],
        ]);

        if (! $mtnMomo->estConfigure()) {
            throw ValidationException::withMessages([
                'mtn_momo' => ['Intégration MTN MoMo non configurée sur ce serveur.'],
            ]);
        }

        $reference = $mtnMomo->initierPaiement(
            $data['numero_destinataire'],
            (float) $depense->montant,
            $depense->libelle,
        );

        $depense->update(['mode_paiement' => 'mtn_momo', 'reference_paiement' => $reference]);

        return response()->json($depense->fresh());
    }
}
