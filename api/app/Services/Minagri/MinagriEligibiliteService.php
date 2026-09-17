<?php

namespace App\Services\Minagri;

use App\Models\Animal;
use App\Models\Depense;
use App\Models\Exploitation;
use App\Models\Incident;
use App\Models\Recette;
use App\Models\Vaccination;

/**
 * Moteur de règles d'éligibilité aux 7 programmes MINAGRI (T085/T087).
 *
 * État — ADAPTÉ : aucun référentiel officiel des critères d'éligibilité
 * MINAGRI n'a été fourni. Les 7 programmes et leurs critères ci-dessous
 * sont des exemples plausibles construits à partir des données déjà
 * disponibles dans TRU FARM ERP (taille du cheptel, exercice comptable,
 * bilan sanitaire, appartenance coopérative). **Chaque critère doit être
 * validé par un agent MINAGRI avant mise en production** (cf. Independent
 * Test de la spec — validation humaine hors périmètre agent, même limite
 * que T035 pour l'expert-comptable OHADA).
 */
class MinagriEligibiliteService
{
    public const PROGRAMMES = [
        'girinka' => 'Girinka (une vache par famille) — cheptel bovin réduit',
        'appui_petits_ruminants' => "Appui à l'élevage caprin/ovin",
        'subvention_veterinaire' => 'Subvention vétérinaire — bilan sanitaire à jour',
        'fonds_garantie_agricole' => 'Fonds de garantie agricole — exercice comptable complet',
        'intensification_elevage' => "Programme d'intensification de l'élevage — cheptel conséquent",
        'appui_cooperative' => "Appui aux coopératives — membre d'une coopérative active",
        'jeune_eleveur' => 'Programme jeune éleveur — exploitation récente en croissance',
    ];

    /** @return array<array{programme: string, libelle: string, eligible: bool, raisons: array<string>}> */
    public function evaluerProgrammes(Exploitation $exploitation): array
    {
        $contexte = $this->collecterContexte($exploitation);

        return [
            $this->evaluer('girinka', $contexte, fn ($c) => $c['nombre_bovins'] === 0 && $c['nombre_animaux'] < 3,
                'Éligible si aucun bovin possédé et cheptel total réduit.'),
            $this->evaluer('appui_petits_ruminants', $contexte, fn ($c) => $c['nombre_petits_ruminants'] > 0 && $c['nombre_petits_ruminants'] < 10,
                "Éligible si l'exploitation élève déjà quelques petits ruminants (1 à 9)."),
            $this->evaluer('subvention_veterinaire', $contexte, fn ($c) => $c['bilan_sanitaire_a_jour'],
                'Éligible si aucun incident critique ouvert et vaccinations à jour.'),
            $this->evaluer('fonds_garantie_agricole', $contexte, fn ($c) => $c['exercice_comptable_complet'],
                'Éligible avec au moins un exercice comptable complet (dépenses et recettes saisies).'),
            $this->evaluer('intensification_elevage', $contexte, fn ($c) => $c['nombre_animaux'] >= 20,
                'Éligible à partir de 20 animaux actifs.'),
            $this->evaluer('appui_cooperative', $contexte, fn ($c) => $c['membre_cooperative'],
                "Éligible si l'exploitation est membre d'une coopérative."),
            $this->evaluer('jeune_eleveur', $contexte, fn ($c) => $c['anciennete_jours'] <= 365 && $c['nombre_animaux'] > 0,
                'Éligible si exploitation créée depuis moins d\'un an avec un début de cheptel.'),
        ];
    }

    private function evaluer(string $slug, array $contexte, callable $regle, string $criteres): array
    {
        return [
            'programme' => $slug,
            'libelle' => self::PROGRAMMES[$slug],
            'eligible' => $regle($contexte),
            'criteres' => $criteres,
        ];
    }

    private function collecterContexte(Exploitation $exploitation): array
    {
        $animaux = Animal::where('exploitation_id', $exploitation->id)->where('statut', 'actif')->get();

        $incidentsCritiquesOuverts = Incident::where('exploitation_id', $exploitation->id)
            ->where('gravite', 'critique')->where('statut', '!=', 'resolu')->exists();

        $vaccinationsManquees = Vaccination::where('exploitation_id', $exploitation->id)
            ->where('statut', 'planifie')->where('date_prevue', '<', now())->exists();

        $anneeCourante = (int) now()->year;
        $exerciceComplet = Depense::where('exploitation_id', $exploitation->id)->whereYear('date_operation', $anneeCourante)->exists()
            && Recette::where('exploitation_id', $exploitation->id)->whereYear('date_operation', $anneeCourante)->exists();

        return [
            'nombre_animaux' => $animaux->count(),
            'nombre_bovins' => $animaux->where('espece', 'bovin')->count(),
            'nombre_petits_ruminants' => $animaux->whereIn('espece', ['caprin', 'ovin'])->count(),
            'bilan_sanitaire_a_jour' => ! $incidentsCritiquesOuverts && ! $vaccinationsManquees,
            'exercice_comptable_complet' => $exerciceComplet,
            'membre_cooperative' => $exploitation->cooperative_id !== null,
            'anciennete_jours' => $exploitation->created_at->diffInDays(now()),
        ];
    }
}
