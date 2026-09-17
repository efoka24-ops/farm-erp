<?php

namespace Database\Seeders;

use App\Models\Animal;
use App\Models\CompteOhada;
use App\Models\Depense;
use App\Models\Exploitation;
use App\Models\Recette;
use App\Services\Sante\CalendrierVaccinalService;

/**
 * Peuple un compte démo (5 jours d'accès) avec des données d'exemple pour que
 * la démo soit immédiatement parlante, sans que le visiteur doive tout
 * saisir lui-même.
 */
class DemoDataSeeder
{
    public function peupler(Exploitation $exploitation): void
    {
        $calendrierVaccinal = app(CalendrierVaccinalService::class);

        $especes = ['bovin', 'caprin', 'ovin'];
        foreach (range(1, 6) as $i) {
            $animal = Animal::create([
                'exploitation_id' => $exploitation->id,
                'espece' => $especes[$i % 3],
                'sexe' => $i % 2 === 0 ? 'femelle' : 'male',
                'date_naissance' => now()->subDays(30 * $i + 60),
                'statut' => 'actif',
            ]);
            $calendrierVaccinal->planifierPour($animal);
        }

        $compteAchat = CompteOhada::where('code', '601')->first();
        $compteVente = CompteOhada::where('code', '701')->first();

        foreach ([2, 1, 0] as $moisOffset) {
            Depense::create([
                'exploitation_id' => $exploitation->id,
                'compte_ohada_id' => $compteAchat->id,
                'montant' => 45000 + $moisOffset * 5000,
                'date_operation' => now()->subMonths($moisOffset)->startOfMonth()->addDays(3),
                'libelle' => 'Achat aliments (démo)',
            ]);
            Recette::create([
                'exploitation_id' => $exploitation->id,
                'compte_ohada_id' => $compteVente->id,
                'montant' => 150000 + $moisOffset * 10000,
                'date_operation' => now()->subMonths($moisOffset)->startOfMonth()->addDays(15),
                'libelle' => 'Vente animaux (démo)',
            ]);
        }
    }
}
