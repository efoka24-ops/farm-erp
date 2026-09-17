<?php

namespace App\Services\Reproduction;

use Illuminate\Support\Carbon;
use InvalidArgumentException;

/**
 * Durées de gestation par espèce (T051), en jours — valeurs moyennes
 * usuelles en élevage, à ajuster si une source vétérinaire locale plus
 * précise devient disponible.
 */
class GestationService
{
    private const DUREE_JOURS = [
        'bovin' => 283,
        'caprin' => 150,
        'ovin' => 152,
        'porcin' => 114,
        'camelin' => 390,
    ];

    public function dureeJours(string $espece): int
    {
        if (! isset(self::DUREE_JOURS[$espece])) {
            throw new InvalidArgumentException("Durée de gestation inconnue pour l'espèce '{$espece}'.");
        }

        return self::DUREE_JOURS[$espece];
    }

    public function datePrevueMiseBas(string $espece, Carbon $dateSaillie): Carbon
    {
        return $dateSaillie->copy()->addDays($this->dureeJours($espece));
    }
}
