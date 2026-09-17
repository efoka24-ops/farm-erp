<?php

namespace Tests\Unit;

use App\Models\Animal;
use App\Models\CompteOhada;
use App\Models\Depense;
use App\Models\Exploitation;
use App\Models\Recette;
use App\Models\Role;
use App\Models\User;
use App\Services\Minagri\MinagriEligibiliteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * T085 : tests unitaires moteur de règles (7 programmes MINAGRI, profils
 * contrastés). 20 profils couvrant les combinaisons clés de critères,
 * conformément à l'Independent Test de la spec.
 *
 * Chaque test authentifie un gérant de l'exploitation évaluée : le moteur
 * interroge Animal/Depense/Recette via la RLS applicative (ExploitationScope),
 * qui exige un utilisateur authentifié ayant accès à l'exploitation — même
 * depuis un service interne (cf. pattern déjà utilisé par
 * DossierFinancementService et CooperativeAggregationService).
 */
class MinagriEligibiliteServiceTest extends TestCase
{
    use RefreshDatabase;

    private MinagriEligibiliteService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MinagriEligibiliteService;
    }

    private function actingAsGerantDe(Exploitation $exploitation): void
    {
        $gerant = User::factory()->create([
            'exploitation_id' => $exploitation->id,
            'role_id' => Role::where('slug', Role::GERANT)->value('id'),
        ]);
        $this->actingAs($gerant, 'sanctum');
    }

    public function test_exploitation_sans_cheptel_est_eligible_girinka(): void
    {
        $exploitation = Exploitation::create(['nom' => 'Sans cheptel', 'type' => 'individuelle']);
        $this->actingAsGerantDe($exploitation);

        $resultats = collect($this->service->evaluerProgrammes($exploitation));

        $this->assertTrue($resultats->firstWhere('programme', 'girinka')['eligible']);
    }

    public function test_exploitation_avec_bovins_nest_pas_eligible_girinka(): void
    {
        $exploitation = Exploitation::create(['nom' => 'Avec bovins', 'type' => 'individuelle']);
        $this->actingAsGerantDe($exploitation);
        Animal::create(['exploitation_id' => $exploitation->id, 'espece' => 'bovin', 'sexe' => 'femelle', 'statut' => 'actif']);

        $resultats = collect($this->service->evaluerProgrammes($exploitation));

        $this->assertFalse($resultats->firstWhere('programme', 'girinka')['eligible']);
    }

    public function test_cheptel_important_eligible_intensification(): void
    {
        $exploitation = Exploitation::create(['nom' => 'Grand cheptel', 'type' => 'individuelle']);
        $this->actingAsGerantDe($exploitation);
        for ($i = 0; $i < 20; $i++) {
            Animal::create(['exploitation_id' => $exploitation->id, 'espece' => 'caprin', 'sexe' => 'femelle', 'statut' => 'actif']);
        }

        $resultats = collect($this->service->evaluerProgrammes($exploitation));

        $this->assertTrue($resultats->firstWhere('programme', 'intensification_elevage')['eligible']);
    }

    public function test_petit_cheptel_nest_pas_eligible_intensification(): void
    {
        $exploitation = Exploitation::create(['nom' => 'Petit cheptel', 'type' => 'individuelle']);
        $this->actingAsGerantDe($exploitation);
        Animal::create(['exploitation_id' => $exploitation->id, 'espece' => 'caprin', 'sexe' => 'femelle', 'statut' => 'actif']);

        $resultats = collect($this->service->evaluerProgrammes($exploitation));

        $this->assertFalse($resultats->firstWhere('programme', 'intensification_elevage')['eligible']);
    }

    public function test_exercice_comptable_complet_eligible_fonds_garantie(): void
    {
        $exploitation = Exploitation::create(['nom' => 'Comptes OK', 'type' => 'individuelle']);
        $this->actingAsGerantDe($exploitation);
        $compteCharge = CompteOhada::where('code', '601')->first();
        $compteProduit = CompteOhada::where('code', '701')->first();
        Depense::create(['exploitation_id' => $exploitation->id, 'compte_ohada_id' => $compteCharge->id, 'montant' => 1000, 'date_operation' => now(), 'libelle' => 'x']);
        Recette::create(['exploitation_id' => $exploitation->id, 'compte_ohada_id' => $compteProduit->id, 'montant' => 2000, 'date_operation' => now(), 'libelle' => 'y']);

        $resultats = collect($this->service->evaluerProgrammes($exploitation));

        $this->assertTrue($resultats->firstWhere('programme', 'fonds_garantie_agricole')['eligible']);
    }

    public function test_sans_mouvement_comptable_non_eligible_fonds_garantie(): void
    {
        $exploitation = Exploitation::create(['nom' => 'Sans comptes', 'type' => 'individuelle']);
        $this->actingAsGerantDe($exploitation);

        $resultats = collect($this->service->evaluerProgrammes($exploitation));

        $this->assertFalse($resultats->firstWhere('programme', 'fonds_garantie_agricole')['eligible']);
    }

    public function test_membre_cooperative_eligible_appui_cooperative(): void
    {
        $coop = Exploitation::create(['nom' => 'Coop', 'type' => 'cooperative', 'max_membres' => 10]);
        $membre = Exploitation::create(['nom' => 'Membre', 'type' => 'individuelle', 'cooperative_id' => $coop->id]);
        $this->actingAsGerantDe($membre);

        $resultats = collect($this->service->evaluerProgrammes($membre));

        $this->assertTrue($resultats->firstWhere('programme', 'appui_cooperative')['eligible']);
    }

    public function test_non_membre_nest_pas_eligible_appui_cooperative(): void
    {
        $exploitation = Exploitation::create(['nom' => 'Indépendant', 'type' => 'individuelle']);
        $this->actingAsGerantDe($exploitation);

        $resultats = collect($this->service->evaluerProgrammes($exploitation));

        $this->assertFalse($resultats->firstWhere('programme', 'appui_cooperative')['eligible']);
    }

    public function test_exploitation_recente_avec_cheptel_eligible_jeune_eleveur(): void
    {
        $exploitation = Exploitation::create(['nom' => 'Jeune', 'type' => 'individuelle', 'created_at' => now()->subDays(30)]);
        $this->actingAsGerantDe($exploitation);
        Animal::create(['exploitation_id' => $exploitation->id, 'espece' => 'ovin', 'sexe' => 'femelle', 'statut' => 'actif']);

        $resultats = collect($this->service->evaluerProgrammes($exploitation));

        $this->assertTrue($resultats->firstWhere('programme', 'jeune_eleveur')['eligible']);
    }

    public function test_exploitation_ancienne_nest_pas_eligible_jeune_eleveur(): void
    {
        $exploitation = Exploitation::create(['nom' => 'Ancienne', 'type' => 'individuelle']);
        $exploitation->forceFill(['created_at' => now()->subYears(3)])->save();
        $this->actingAsGerantDe($exploitation);
        Animal::create(['exploitation_id' => $exploitation->id, 'espece' => 'ovin', 'sexe' => 'femelle', 'statut' => 'actif']);

        $resultats = collect($this->service->evaluerProgrammes($exploitation));

        $this->assertFalse($resultats->firstWhere('programme', 'jeune_eleveur')['eligible']);
    }

    public function test_petits_ruminants_en_petit_nombre_eligible_appui_petits_ruminants(): void
    {
        $exploitation = Exploitation::create(['nom' => 'Petits ruminants', 'type' => 'individuelle']);
        $this->actingAsGerantDe($exploitation);
        Animal::create(['exploitation_id' => $exploitation->id, 'espece' => 'caprin', 'sexe' => 'femelle', 'statut' => 'actif']);

        $resultats = collect($this->service->evaluerProgrammes($exploitation));

        $this->assertTrue($resultats->firstWhere('programme', 'appui_petits_ruminants')['eligible']);
    }

    public function test_evaluer_programmes_retourne_les_7_programmes(): void
    {
        $exploitation = Exploitation::create(['nom' => 'X', 'type' => 'individuelle']);
        $this->actingAsGerantDe($exploitation);

        $resultats = $this->service->evaluerProgrammes($exploitation);

        $this->assertCount(7, $resultats);
    }

    /**
     * 20 profils synthétiques contrastés, vérifiant simplement que le
     * moteur répond sans erreur et retourne une évaluation booléenne pour
     * chacun des 7 programmes, quelle que soit la combinaison de critères.
     */
    public function test_vingt_profils_contrastes_sont_tous_evalues_sans_erreur(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $exploitation = Exploitation::create([
                'nom' => "Profil {$i}",
                'type' => $i % 4 === 0 ? 'cooperative' : 'individuelle',
                'max_membres' => $i % 4 === 0 ? 10 : null,
                'created_at' => now()->subDays($i * 40),
            ]);
            $this->actingAsGerantDe($exploitation);

            for ($a = 0; $a < $i % 25; $a++) {
                Animal::create([
                    'exploitation_id' => $exploitation->id,
                    'espece' => ['bovin', 'caprin', 'ovin'][$a % 3],
                    'sexe' => $a % 2 === 0 ? 'femelle' : 'male',
                    'statut' => 'actif',
                ]);
            }

            $resultats = $this->service->evaluerProgrammes($exploitation);

            $this->assertCount(7, $resultats);
            foreach ($resultats as $resultat) {
                $this->assertIsBool($resultat['eligible']);
            }
        }
    }
}
