<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\SyncAction;
use App\Models\SyncConflict;
use App\Models\User;
use App\Notifications\ConflitFinancierNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Moteur de synchronisation offline (T013) : file d'actions locale (mobile) →
 * delta sync serveur. L'application effective de chaque action sur son entité
 * métier (animaux, dépenses, etc.) est déléguée aux modules concernés via
 * SyncAction::applicateurPour() au fur et à mesure de leur construction —
 * ici on garantit l'idempotence, la file d'attente et la détection de conflit.
 *
 * Financiers : tout conflit sur une entité marquée financière déclenche un
 * arbitrage manuel (T014), jamais de résolution automatique côté serveur.
 */
class SyncController extends Controller
{
    private const ENTITES_FINANCIERES = ['depense', 'recette', 'vente', 'paiement'];

    public function push(Request $request): JsonResponse
    {
        $data = $request->validate([
            'actions' => ['required', 'array', 'min:1'],
            'actions.*.id' => ['required', 'uuid'],
            'actions.*.entity_type' => ['required', 'string'],
            'actions.*.entity_id' => ['required', 'string'],
            'actions.*.operation' => ['required', 'in:create,update,delete'],
            'actions.*.payload' => ['required', 'array'],
            'actions.*.occurred_at' => ['required', 'date'],
        ]);

        $user = $request->user();
        $resultats = [];

        foreach ($data['actions'] as $action) {
            // Idempotence : une action déjà reçue (même UUID client) n'est jamais rejouée.
            $existante = SyncAction::withoutGlobalScopes()->find($action['id']);

            if ($existante) {
                $resultats[] = ['id' => $action['id'], 'statut' => $existante->statut, 'deja_recue' => true];

                continue;
            }

            $syncAction = SyncAction::create([
                'id' => $action['id'],
                'exploitation_id' => $user->exploitation_id,
                'user_id' => $user->id,
                'entity_type' => $action['entity_type'],
                'entity_id' => $action['entity_id'],
                'operation' => $action['operation'],
                'payload' => $action['payload'],
                'occurred_at' => $action['occurred_at'],
                'statut' => 'en_attente',
            ]);

            $estFinanciere = in_array($action['entity_type'], self::ENTITES_FINANCIERES, true);
            $conflitDetecte = $estFinanciere && $this->aConflitPotentiel($syncAction);

            if ($conflitDetecte) {
                $conflit = SyncConflict::create([
                    'sync_action_id' => $syncAction->id,
                    'exploitation_id' => $user->exploitation_id,
                    'entity_type' => $action['entity_type'],
                    'entity_id' => $action['entity_id'],
                    'est_financier' => true,
                    'donnees_serveur' => $conflitDetecte,
                    'donnees_client' => $action['payload'],
                    'statut' => 'ouvert',
                ]);
                $syncAction->update(['statut' => 'conflit']);

                User::withoutGlobalScopes()
                    ->where('exploitation_id', $user->exploitation_id)
                    ->whereHas('role', fn ($q) => $q->where('slug', Role::GERANT))
                    ->get()
                    ->each(fn (User $gerant) => $gerant->notify(new ConflitFinancierNotification($conflit)));
            } else {
                $syncAction->update(['statut' => 'applique']);
            }

            $resultats[] = ['id' => $action['id'], 'statut' => $syncAction->statut, 'deja_recue' => false];
        }

        return response()->json(['resultats' => $resultats]);
    }

    public function pull(Request $request): JsonResponse
    {
        $since = $request->query('since');

        $query = SyncAction::query();

        if ($since) {
            $query->where('updated_at', '>', $since);
        }

        return response()->json([
            'actions' => $query->orderBy('updated_at')->limit(500)->get(),
            'horodatage_serveur' => now()->toIso8601String(),
        ]);
    }

    public function listerConflits(Request $request): JsonResponse
    {
        return response()->json(
            SyncConflict::where('statut', 'ouvert')->latest()->get()
        );
    }

    public function resoudreConflit(Request $request, SyncConflict $conflit): JsonResponse
    {
        $data = $request->validate([
            'resolution' => ['required', 'in:resolu_serveur,resolu_client,resolu_fusion'],
        ]);

        if (! $request->user()->hasRole(Role::GERANT)) {
            abort(403, 'Seul le gérant peut arbitrer un conflit financier.');
        }

        $conflit->update([
            'statut' => $data['resolution'],
            'resolu_par' => $request->user()->id,
            'resolu_at' => now(),
        ]);

        return response()->json($conflit);
    }

    /**
     * Détection simplifiée : une autre action serveur plus récente existe déjà
     * sur la même entité et n'a pas été vue par le client (occurred_at antérieur).
     * Retourne les données serveur en conflit, ou null.
     */
    private function aConflitPotentiel(SyncAction $action): ?array
    {
        $concurrente = SyncAction::query()
            ->where('entity_type', $action->entity_type)
            ->where('entity_id', $action->entity_id)
            ->where('id', '!=', $action->id)
            ->where('occurred_at', '>', $action->occurred_at)
            ->where('statut', 'applique')
            ->latest('occurred_at')
            ->first();

        return $concurrente?->payload;
    }
}
