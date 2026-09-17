<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\CompteOhada;
use App\Models\Depense;
use App\Models\Saillie;
use App\Services\Comptabilite\CoutRevientService;
use App\Services\Planificateur\PlanificateurVentesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\CreeUnGerantAuthentifie;
use Tests\TestCase;

/**
 * T066 : exclusion des animaux gestants/sous traitement/reproducteurs désignés.
 */
class PlanificateurVentesTest extends TestCase
{
    use CreeUnGerantAuthentifie, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.ia_planificateur.url', 'http://ia-planificateur.test');
    }

    public function test_animaux_eligibles_exclut_gestantes_traitement_et_reproducteurs(): void
    {
        $gerant = $this->gerantAuthentifie();
        $service = new PlanificateurVentesService(new CoutRevientService);

        $eligible = Animal::create([
            'exploitation_id' => $gerant->exploitation_id, 'espece' => 'bovin', 'sexe' => 'male', 'statut' => 'actif',
        ]);

        $femelleGestante = Animal::create([
            'exploitation_id' => $gerant->exploitation_id, 'espece' => 'bovin', 'sexe' => 'femelle', 'statut' => 'actif',
        ]);
        Saillie::create([
            'exploitation_id' => $gerant->exploitation_id, 'femelle_id' => $femelleGestante->id,
            'date_saillie' => now()->subDays(30), 'date_prevue_mise_bas' => now()->addDays(253), 'statut' => 'en_cours',
        ]);

        $sousTraitement = Animal::create([
            'exploitation_id' => $gerant->exploitation_id, 'espece' => 'bovin', 'sexe' => 'male', 'statut' => 'actif',
        ]);
        $sousTraitement->traitements()->create([
            'exploitation_id' => $gerant->exploitation_id, 'medicament' => 'x',
            'date_debut' => now(), 'delai_attente_jours' => 10,
            'date_fin_delai_attente' => now()->addDays(10),
        ]);

        $reproducteur = Animal::create([
            'exploitation_id' => $gerant->exploitation_id, 'espece' => 'bovin', 'sexe' => 'male',
            'statut' => 'actif', 'reproducteur_designe' => true,
        ]);

        $eligibles = $service->animauxEligibles($gerant->exploitation_id);

        $this->assertCount(1, $eligibles);
        $this->assertSame($eligible->id, $eligibles->first()->id);
    }

    public function test_recommandations_appelle_le_microservice_avec_les_animaux_eligibles(): void
    {
        Http::fake([
            'ia-planificateur.test/*' => Http::response([
                'recommandations' => [
                    ['id' => 'sera-remplace', 'score' => 42, 'marge_journaliere' => 100, 'prix_estime' => 300000, 'ca_potentiel' => 200000],
                ],
            ]),
        ]);

        $gerant = $this->gerantAuthentifie();
        $compte = CompteOhada::where('code', '601')->first();
        $animal = Animal::create([
            'exploitation_id' => $gerant->exploitation_id, 'espece' => 'bovin', 'sexe' => 'male',
            'statut' => 'actif', 'date_naissance' => now()->subDays(200),
        ]);
        Depense::create([
            'exploitation_id' => $gerant->exploitation_id, 'compte_ohada_id' => $compte->id,
            'montant' => 50000, 'date_operation' => now(), 'libelle' => 'x', 'animal_id' => $animal->id,
        ]);

        $reponse = $this->getJson('/api/planificateur-ventes/recommandations')->assertOk()->json();

        $this->assertCount(1, $reponse['recommandations']);

        Http::assertSent(function ($request) use ($animal) {
            $animaux = $request->data()['animaux'];

            return $animaux[0]['id'] === $animal->id
                && $animaux[0]['cout_total'] === 50000.0
                && $animaux[0]['age_jours'] === 200;
        });
    }

    public function test_recommandations_503_si_microservice_non_configure(): void
    {
        Config::set('services.ia_planificateur.url', null);
        $this->gerantAuthentifie();

        $this->getJson('/api/planificateur-ventes/recommandations')->assertStatus(503);
    }

    public function test_recommandations_vide_si_aucun_animal_eligible(): void
    {
        Http::fake();
        $this->gerantAuthentifie();

        $reponse = $this->getJson('/api/planificateur-ventes/recommandations')->assertOk()->json();

        $this->assertSame([], $reponse['recommandations']);
        Http::assertNothingSent();
    }
}
