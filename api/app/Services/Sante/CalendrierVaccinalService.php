<?php

namespace App\Services\Sante;

use App\Models\Animal;
use App\Models\ProtocoleVaccinal;
use App\Models\Vaccination;
use Illuminate\Support\Collection;

/**
 * Calendrier vaccinal automatique par espèce (T048) : à la création d'un
 * animal (date de naissance connue), planifie chaque vaccination du
 * protocole correspondant à son espèce. Sans date de naissance connue
 * (animal acquis adulte), aucune planification automatique n'est possible —
 * à saisir manuellement.
 */
class CalendrierVaccinalService
{
    public function planifierPour(Animal $animal): int
    {
        if (! $animal->date_naissance) {
            return 0;
        }

        $protocoles = ProtocoleVaccinal::where('espece', $animal->espece)->get();

        foreach ($protocoles as $protocole) {
            Vaccination::create([
                'exploitation_id' => $animal->exploitation_id,
                'animal_id' => $animal->id,
                'protocole_vaccinal_id' => $protocole->id,
                'vaccin' => $protocole->vaccin,
                'date_prevue' => $animal->date_naissance->copy()->addDays($protocole->jour_apres_naissance),
                'statut' => 'planifie',
            ]);
        }

        return $protocoles->count();
    }

    /**
     * Alertes J-14/J-3 (T049) : vaccinations planifiées dont l'échéance
     * tombe dans exactement `joursAvant` jours (pour éviter les doublons
     * d'alerte si la tâche planifiée tourne plusieurs fois).
     */
    public function vaccinationsAAlerterDans(string $exploitationId, int $joursAvant): Collection
    {
        return Vaccination::where('exploitation_id', $exploitationId)
            ->where('statut', 'planifie')
            ->whereDate('date_prevue', now()->addDays($joursAvant)->toDateString())
            ->with('animal:id,tru_trace_id,espece')
            ->get();
    }
}
