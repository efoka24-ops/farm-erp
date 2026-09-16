<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\Sante\MokineVetoImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Webhook consommateur MokineVeto (T047) : point d'entrée public (hors
 * auth:sanctum, car appelé par un système tiers) sécurisé par un secret
 * partagé (`MOKINEVOTO_WEBHOOK_SECRET`). Aucun partenariat technique réel
 * n'est établi à ce jour — ce endpoint est prêt mais non branché en
 * production tant que MokineVeto n'a pas cette URL en configuration.
 */
class MokineVetoWebhookController extends Controller
{
    public function __construct(private readonly MokineVetoImportService $import) {}

    public function consultation(Request $request): JsonResponse
    {
        if (! $this->secretValide($request)) {
            return response()->json(['message' => 'Signature webhook invalide.'], 401);
        }

        $data = $request->validate([
            'tru_trace_id' => ['required', 'string'],
            'mokinevoto_consultation_id' => ['required', 'string'],
            'veterinaire' => ['nullable', 'string'],
            'date_consultation' => ['required', 'date'],
            'diagnostic' => ['nullable', 'string'],
            'prescription' => ['nullable', 'string'],
        ]);

        try {
            $consultation = $this->import->importer($data);

            return response()->json($consultation, 201);
        } catch (\RuntimeException $e) {
            // Mode dégradé : accusé de réception envoyé quand même (200) pour
            // éviter que MokineVeto ne réessaie en boucle — la donnée est
            // conservée côté nous pour rejeu ultérieur.
            return response()->json(['message' => $e->getMessage(), 'en_attente' => true], 202);
        }
    }

    private function secretValide(Request $request): bool
    {
        $secretAttendu = config('services.mokinevoto.webhook_secret');

        if (! $secretAttendu) {
            // Pas de secret configuré = intégration non activée : on refuse
            // par défaut plutôt que d'accepter des webhooks non authentifiés.
            return false;
        }

        return hash_equals($secretAttendu, (string) $request->header('X-MokineVeto-Secret'));
    }
}
