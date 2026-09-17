<?php

namespace App\Services\Planificateur;

use App\Models\Animal;
use App\Models\Saillie;
use App\Services\Comptabilite\CoutRevientService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Module planificateur de ventes (T071) : applique les contraintes métier
 * (exclusion gestation/traitement/reproducteurs désignés) puis délègue le
 * scoring au microservice `ia-planificateur` (T070).
 *
 * État : le microservice est fonctionnel (voir ia-planificateur/) mais son
 * modèle est une heuristique documentée, pas un Random Forest entraîné sur
 * données RAB/ILRI (aucune des deux sources n'est accessible — cf.
 * ia-planificateur/src/model/scoring.py). Le déploiement du microservice
 * lui-même (URL configurée dans IA_PLANIFICATEUR_URL) reste à faire.
 */
class PlanificateurVentesService
{
    public function __construct(private readonly CoutRevientService $coutRevient) {}

    public function estConfigure(): bool
    {
        return filled(config('services.ia_planificateur.url'));
    }

    /** Animaux éligibles à la recommandation de vente (T066). */
    public function animauxEligibles(string $exploitationId): Collection
    {
        $femellesGestantes = Saillie::where('exploitation_id', $exploitationId)
            ->where('statut', 'en_cours')
            ->pluck('femelle_id');

        return Animal::where('exploitation_id', $exploitationId)
            ->where('statut', 'actif')
            ->where('reproducteur_designe', false)
            ->whereNotIn('id', $femellesGestantes)
            ->get()
            ->filter(fn (Animal $a) => $a->peutEtreVendu())
            ->values();
    }

    /**
     * @return array{recommandations: array}
     */
    public function recommander(string $exploitationId): array
    {
        if (! $this->estConfigure()) {
            throw new RuntimeException(
                'Microservice IA planificateur non configuré (IA_PLANIFICATEUR_URL manquant).'
            );
        }

        $animaux = $this->animauxEligibles($exploitationId);

        if ($animaux->isEmpty()) {
            return ['recommandations' => []];
        }

        $payload = $animaux->map(function (Animal $animal) {
            $cout = $this->coutRevient->coutRevient($animal);

            return [
                'id' => $animal->id,
                'espece' => $animal->espece,
                'poids_actuel_kg' => (float) ($animal->dernierPoids() ?? 0),
                'cout_total' => $cout['cout_total'],
                'age_jours' => $animal->date_naissance
                    ? (int) $animal->date_naissance->diffInDays(now())
                    : 0,
            ];
        })->values()->all();

        $reponse = Http::timeout(10)
            ->post(config('services.ia_planificateur.url').'/recommandations-ventes', [
                'animaux' => $payload,
            ]);

        if ($reponse->failed()) {
            Log::warning('Planificateur IA : échec appel microservice', ['status' => $reponse->status()]);

            throw new RuntimeException('Le service de recommandation est momentanément indisponible.');
        }

        $recommandations = collect($reponse->json('recommandations', []))
            ->map(function (array $reco) use ($animaux) {
                $animal = $animaux->firstWhere('id', $reco['id']);

                return [
                    ...$reco,
                    'animal' => $animal?->only('id', 'tru_trace_id', 'espece', 'race'),
                ];
            })
            ->values()
            ->all();

        return ['recommandations' => $recommandations];
    }
}
