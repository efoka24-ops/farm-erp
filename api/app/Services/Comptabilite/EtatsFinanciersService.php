<?php

namespace App\Services\Comptabilite;

use App\Models\Depense;
use App\Models\Recette;
use Illuminate\Support\Carbon;

/**
 * Génération des états financiers OHADA simplifiés (T031) : à partir de la
 * saisie dépense/recette liée au plan comptable (T029/T030), sans tenue de
 * grand livre à double entrée complet — cohérent avec le principe de saisie
 * simplifiée voulu pour un exploitant non-comptable (spec.md US2).
 */
class EtatsFinanciersService
{
    public function compteDeResultat(string $exploitationId, Carbon $debut, Carbon $fin): array
    {
        $charges = Depense::query()
            ->where('exploitation_id', $exploitationId)
            ->whereBetween('date_operation', [$debut, $fin])
            ->with('compteOhada')
            ->get()
            ->groupBy(fn (Depense $d) => $d->compteOhada->code)
            ->map(fn ($groupe) => [
                'compte' => $groupe->first()->compteOhada->code,
                'libelle' => $groupe->first()->compteOhada->libelle,
                'montant' => (float) $groupe->sum('montant'),
            ])
            ->values();

        $produits = Recette::query()
            ->where('exploitation_id', $exploitationId)
            ->whereBetween('date_operation', [$debut, $fin])
            ->with('compteOhada')
            ->get()
            ->groupBy(fn (Recette $r) => $r->compteOhada->code)
            ->map(fn ($groupe) => [
                'compte' => $groupe->first()->compteOhada->code,
                'libelle' => $groupe->first()->compteOhada->libelle,
                'montant' => (float) $groupe->sum('montant'),
            ])
            ->values();

        $totalCharges = round((float) $charges->sum('montant'), 2);
        $totalProduits = round((float) $produits->sum('montant'), 2);

        return [
            'periode' => ['debut' => $debut->toDateString(), 'fin' => $fin->toDateString()],
            'charges' => $charges,
            'produits' => $produits,
            'total_charges' => $totalCharges,
            'total_produits' => $totalProduits,
            'resultat_net' => round($totalProduits - $totalCharges, 2),
        ];
    }

    public function bilanSimplifie(string $exploitationId, Carbon $auDate): array
    {
        // Bilan simplifié cumulatif depuis l'origine jusqu'à la date donnée
        // (pas de clôture d'exercice formalisée avant T094 §4.17).
        $tresorerie = $this->soldeTresorerie($exploitationId, $auDate);

        $resultatCumule = $this->compteDeResultat(
            $exploitationId,
            Carbon::createFromTimestamp(0),
            $auDate
        )['resultat_net'];

        $actif = round($tresorerie, 2);
        $passif = round($resultatCumule, 2);

        return [
            'au' => $auDate->toDateString(),
            'actif' => ['tresorerie' => $actif, 'total' => $actif],
            'passif' => ['resultat_cumule' => $passif, 'total' => $passif],
            'equilibre' => abs($actif - $passif) < 0.01,
        ];
    }

    public function tableauTresorerie(string $exploitationId, Carbon $debut, Carbon $fin): array
    {
        $encaissements = (float) Recette::where('exploitation_id', $exploitationId)
            ->whereBetween('date_operation', [$debut, $fin])
            ->sum('montant');

        $decaissements = (float) Depense::where('exploitation_id', $exploitationId)
            ->whereBetween('date_operation', [$debut, $fin])
            ->sum('montant');

        $parMode = collect(['especes', 'banque', 'mtn_momo'])->mapWithKeys(function (string $mode) use ($exploitationId, $debut, $fin) {
            $in = (float) Recette::where('exploitation_id', $exploitationId)
                ->where('mode_paiement', $mode)
                ->whereBetween('date_operation', [$debut, $fin])->sum('montant');
            $out = (float) Depense::where('exploitation_id', $exploitationId)
                ->where('mode_paiement', $mode)
                ->whereBetween('date_operation', [$debut, $fin])->sum('montant');

            return [$mode => ['encaissements' => round($in, 2), 'decaissements' => round($out, 2)]];
        });

        return [
            'periode' => ['debut' => $debut->toDateString(), 'fin' => $fin->toDateString()],
            'encaissements' => round($encaissements, 2),
            'decaissements' => round($decaissements, 2),
            'flux_net' => round($encaissements - $decaissements, 2),
            'par_mode_paiement' => $parMode,
        ];
    }

    private function soldeTresorerie(string $exploitationId, Carbon $auDate): float
    {
        $encaissements = (float) Recette::where('exploitation_id', $exploitationId)
            ->where('date_operation', '<=', $auDate)
            ->sum('montant');

        $decaissements = (float) Depense::where('exploitation_id', $exploitationId)
            ->where('date_operation', '<=', $auDate)
            ->sum('montant');

        return $encaissements - $decaissements;
    }
}
