<?php

namespace App\Services\Financement;

/**
 * Calcul du plan de remboursement (T043). Taux configurable par dossier
 * (`taux_annuel_pourcent`, défaut 18%/an — taux indicatif de crédit agricole
 * BPR Rwanda, à confirmer avec la banque lors de l'instruction réelle du
 * dossier ; aucune API BPR n'est intégrée, le taux est saisi/ajusté
 * manuellement).
 */
class PlanRemboursementService
{
    public function mensualite(float $montant, int $dureeMois, float $tauxAnnuelPourcent): float
    {
        $tauxMensuel = $tauxAnnuelPourcent / 100 / 12;

        if ($tauxMensuel == 0.0) {
            return round($montant / $dureeMois, 2);
        }

        $mensualite = $montant * $tauxMensuel / (1 - (1 + $tauxMensuel) ** -$dureeMois);

        return round($mensualite, 2);
    }

    /** @return array<int, array{mois: int, mensualite: float, capital: float, interet: float, solde_restant: float}> */
    public function echeancier(float $montant, int $dureeMois, float $tauxAnnuelPourcent): array
    {
        $tauxMensuel = $tauxAnnuelPourcent / 100 / 12;
        $mensualite = $this->mensualite($montant, $dureeMois, $tauxAnnuelPourcent);
        $solde = $montant;
        $echeancier = [];

        for ($mois = 1; $mois <= $dureeMois; $mois++) {
            $interet = round($solde * $tauxMensuel, 2);
            $capital = round($mensualite - $interet, 2);
            $solde = max(0, round($solde - $capital, 2));

            $echeancier[] = [
                'mois' => $mois,
                'mensualite' => $mensualite,
                'capital' => $capital,
                'interet' => $interet,
                'solde_restant' => $solde,
            ];
        }

        return $echeancier;
    }
}
