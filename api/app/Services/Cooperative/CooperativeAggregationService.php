<?php

namespace App\Services\Cooperative;

use App\Models\Animal;
use App\Models\Depense;
use App\Models\Exploitation;
use App\Models\Incident;
use App\Models\Recette;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

/**
 * Agrégation coopérative (T078/T080) : tableau de bord consolidé jusqu'à
 * 200 membres, limité aux membres ayant explicitement consenti au partage
 * (T079) — jamais de bypass, même pour le gestionnaire de coopérative.
 *
 * Cache 6h (T080) — ADAPTÉ : pas de Redis disponible sur l'hébergement
 * mutualisé (cf. adaptations Phase 2), le cache utilise le driver
 * configuré (`file` par défaut) via la façade `Cache`, portable sans
 * changement si Redis devient disponible plus tard.
 */
class CooperativeAggregationService
{
    public function membresConsentants(Exploitation $cooperative): Collection
    {
        return $cooperative->membres()
            ->whereNotNull('consentement_cooperative_donne_le')
            ->get();
    }

    public function rapportAgrege(Exploitation $cooperative, bool $forcerRecalcul = false): array
    {
        $cle = "cooperative:{$cooperative->id}:rapport-agrege";

        if ($forcerRecalcul) {
            Cache::forget($cle);
        }

        return Cache::remember($cle, now()->addHours(6), function () use ($cooperative) {
            $membresConsentants = $this->membresConsentants($cooperative);
            $ids = $membresConsentants->pluck('id');

            $totalProduits = (float) Recette::whereIn('exploitation_id', $ids)->sum('montant');
            $totalCharges = (float) Depense::whereIn('exploitation_id', $ids)->sum('montant');

            return [
                'cooperative_id' => $cooperative->id,
                'nombre_membres_total' => $cooperative->membres()->count(),
                'nombre_membres_consentants' => $membresConsentants->count(),
                'total_animaux_actifs' => Animal::whereIn('exploitation_id', $ids)->where('statut', 'actif')->count(),
                'total_produits' => round($totalProduits, 2),
                'total_charges' => round($totalCharges, 2),
                'resultat_net_consolide' => round($totalProduits - $totalCharges, 2),
                'incidents_critiques_ouverts' => Incident::whereIn('exploitation_id', $ids)
                    ->where('gravite', 'critique')->where('statut', '!=', 'resolu')->count(),
                'calcule_le' => now()->toIso8601String(),
            ];
        });
    }

    /**
     * Détail d'un membre (T076) : refusé sans consentement explicite, même
     * au gestionnaire de la coopérative dont il dépend.
     */
    public function detailMembre(Exploitation $cooperative, Exploitation $membre): array
    {
        if ($membre->cooperative_id !== $cooperative->id) {
            throw new RuntimeException("Cette exploitation n'appartient pas à la coopérative.");
        }

        if (! $membre->aConsentiPartageCooperative()) {
            throw new ConsentementRequisException($membre);
        }

        $totalProduits = (float) Recette::where('exploitation_id', $membre->id)->sum('montant');
        $totalCharges = (float) Depense::where('exploitation_id', $membre->id)->sum('montant');

        return [
            'exploitation_id' => $membre->id,
            'nom' => $membre->nom,
            'total_animaux_actifs' => Animal::where('exploitation_id', $membre->id)->where('statut', 'actif')->count(),
            'total_produits' => round($totalProduits, 2),
            'total_charges' => round($totalCharges, 2),
            'resultat_net' => round($totalProduits - $totalCharges, 2),
        ];
    }
}
