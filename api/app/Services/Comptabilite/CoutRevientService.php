<?php

namespace App\Services\Comptabilite;

use App\Models\Animal;
use App\Models\Depense;

/**
 * Coût de revient par animal et seuil de rentabilité (T032). Le coût inclut
 * toutes les dépenses explicitement rattachées à l'animal (achat, vétérinaire,
 * etc. via `depenses.animal_id`) — l'alimentation groupée par lot (distributions
 * sans animal_id) n'est pas répartie automatiquement tant que le module stocks
 * (US9) ne fournit pas de valorisation FIFO/PMP fiable ; elle peut être saisie
 * comme dépense individuelle en attendant.
 */
class CoutRevientService
{
    public function coutRevient(Animal $animal): array
    {
        $coutTotal = (float) Depense::where('animal_id', $animal->id)->sum('montant');

        // Carbon 3 : diffInDays retourne un float par défaut, on tronque à l'entier.
        $ageJours = $animal->date_naissance
            ? (int) $animal->date_naissance->diffInDays(now())
            : null;

        return [
            'animal_id' => $animal->id,
            'cout_total' => round($coutTotal, 2),
            'cout_journalier' => $ageJours && $ageJours > 0
                ? round($coutTotal / $ageJours, 2)
                : null,
            'nombre_depenses' => Depense::where('animal_id', $animal->id)->count(),
        ];
    }

    /**
     * Prix de vente minimum pour ne pas vendre à perte, avec une marge cible
     * optionnelle (par défaut 0 = seuil de rentabilité strict).
     */
    public function seuilRentabilite(Animal $animal, float $margeCiblePourcent = 0.0): array
    {
        $cout = $this->coutRevient($animal);
        $seuil = $cout['cout_total'] * (1 + $margeCiblePourcent / 100);

        return [
            ...$cout,
            'marge_cible_pourcent' => $margeCiblePourcent,
            'prix_vente_minimum' => round($seuil, 2),
        ];
    }
}
