<?php

namespace App\Services\Minagri;

use App\Models\DemandeEligibiliteMinagri;
use App\Models\Exploitation;
use App\Notifications\NouvelleEligibiliteMinagriNotification;

/**
 * Déclenchement auto du moteur de règles sur événements (T088) : nouvel
 * animal, pesée, adhésion coopérative, etc. Crée une `DemandeEligibiliteMinagri`
 * (statut `detectee`) pour chaque programme nouvellement éligible et notifie
 * l'exploitation (T089) — sans jamais recréer/renotifier un programme déjà
 * détecté (idempotent par contrainte unique exploitation+programme).
 */
class DetectionEligibiliteService
{
    public function __construct(private readonly MinagriEligibiliteService $moteur) {}

    /** @return array<DemandeEligibiliteMinagri> Nouvelles détections. */
    public function relancerPour(Exploitation $exploitation): array
    {
        $evaluations = $this->moteur->evaluerProgrammes($exploitation);
        $nouvelles = [];

        foreach ($evaluations as $evaluation) {
            if (! $evaluation['eligible']) {
                continue;
            }

            $existe = DemandeEligibiliteMinagri::where('exploitation_id', $exploitation->id)
                ->where('programme', $evaluation['programme'])
                ->exists();

            if ($existe) {
                continue;
            }

            $demande = DemandeEligibiliteMinagri::create([
                'exploitation_id' => $exploitation->id,
                'programme' => $evaluation['programme'],
                'eligible_depuis' => now(),
                'statut' => 'detectee',
            ]);

            $exploitation->utilisateurs->each(
                fn ($u) => $u->notify(new NouvelleEligibiliteMinagriNotification($evaluation['programme']))
            );

            $nouvelles[] = $demande;
        }

        return $nouvelles;
    }
}
