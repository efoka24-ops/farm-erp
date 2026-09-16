<?php

namespace App\Services\Financement;

use App\Models\Animal;
use App\Models\Depense;
use App\Models\Incident;
use App\Models\Recette;

/**
 * Vérification de complétude avant génération du dossier de financement
 * (T038/T039). Un dossier incomplet doit lister précisément ce qui manque —
 * jamais un simple refus générique — pour que l'exploitant sache quoi
 * compléter avant de réessayer.
 */
class VerificationCompletudeService
{
    /** @return array{complet: bool, manques: array<string>} */
    public function verifier(string $exploitationId): array
    {
        $manques = [];

        $anneesIncompletes = $this->anneesComptablesIncompletes($exploitationId);
        if (! empty($anneesIncompletes)) {
            $manques[] = 'Données comptables incomplètes pour l\'exercice '.implode(', ', $anneesIncompletes)
                .' (au moins une dépense et une recette requises par exercice).';
        }

        if (Animal::where('exploitation_id', $exploitationId)->count() === 0) {
            $manques[] = 'Aucun animal enregistré dans le registre cheptel.';
        }

        $incidentsCritiquesOuverts = Incident::where('exploitation_id', $exploitationId)
            ->where('statut', '!=', 'resolu')
            ->where('gravite', 'critique')
            ->where('created_at', '<=', now()->subDays(7))
            ->count();

        if ($incidentsCritiquesOuverts > 0) {
            $manques[] = "{$incidentsCritiquesOuverts} incident(s) sanitaire(s) critique(s) non résolu(s) depuis plus de 7 jours.";
        }

        return ['complet' => empty($manques), 'manques' => $manques];
    }

    /** @return array<int> Années (dans les 3 derniers exercices) sans dépense ET recette. */
    private function anneesComptablesIncompletes(string $exploitationId): array
    {
        $anneeCourante = (int) now()->year;
        $manquantes = [];

        foreach ([$anneeCourante - 2, $anneeCourante - 1, $anneeCourante] as $annee) {
            $aDepense = Depense::where('exploitation_id', $exploitationId)
                ->whereYear('date_operation', $annee)->exists();
            $aRecette = Recette::where('exploitation_id', $exploitationId)
                ->whereYear('date_operation', $annee)->exists();

            if (! $aDepense || ! $aRecette) {
                $manquantes[] = $annee;
            }
        }

        return $manquantes;
    }
}
