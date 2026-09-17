<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\CompteOhada;
use App\Models\Exploitation;
use App\Models\Incident;
use App\Models\Recette;
use App\Models\Role;
use App\Models\User;
use App\Notifications\AlerteSolidariteNotification;
use App\Services\Cooperative\CooperativeAggregationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\CreeUnGerantAuthentifie;
use Tests\TestCase;

class CooperativeTest extends TestCase
{
    use CreeUnGerantAuthentifie, RefreshDatabase;

    /** T076 : refus d'accès individuel sans consentement. */
    public function test_detail_membre_refuse_sans_consentement(): void
    {
        $cooperative = Exploitation::create(['nom' => 'Coop Test', 'type' => 'cooperative', 'max_membres' => 50]);
        $gerantCoop = User::factory()->create([
            'exploitation_id' => $cooperative->id,
            'role_id' => Role::where('slug', Role::GESTIONNAIRE_COOPERATIVE)->value('id'),
            'two_factor_enabled' => false,
        ]);

        $membre = Exploitation::create([
            'nom' => 'Membre A', 'type' => 'individuelle', 'cooperative_id' => $cooperative->id,
        ]);

        $this->actingAs($gerantCoop, 'sanctum');

        $this->getJson("/api/cooperatives/{$cooperative->id}/membres/{$membre->id}/detail")
            ->assertStatus(403);
    }

    public function test_detail_membre_accessible_apres_consentement(): void
    {
        $cooperative = Exploitation::create(['nom' => 'Coop Test', 'type' => 'cooperative', 'max_membres' => 50]);
        $gerantCoop = User::factory()->create([
            'exploitation_id' => $cooperative->id,
            'role_id' => Role::where('slug', Role::GESTIONNAIRE_COOPERATIVE)->value('id'),
            'two_factor_enabled' => false,
        ]);

        $membre = Exploitation::create([
            'nom' => 'Membre A', 'type' => 'individuelle', 'cooperative_id' => $cooperative->id,
            'consentement_cooperative_donne_le' => now(),
        ]);

        $this->actingAs($gerantCoop, 'sanctum');

        $this->getJson("/api/cooperatives/{$cooperative->id}/membres/{$membre->id}/detail")
            ->assertOk()
            ->assertJsonPath('nom', 'Membre A');
    }

    /** T075 : rapport agrégé, testé ici avec un nombre de membres conséquent. */
    public function test_rapport_agrege_calcule_sous_10_secondes_avec_beaucoup_de_membres(): void
    {
        $cooperative = Exploitation::create(['nom' => 'Coop Test', 'type' => 'cooperative', 'max_membres' => 200]);
        $gerantCoop = User::factory()->create([
            'exploitation_id' => $cooperative->id,
            'role_id' => Role::where('slug', Role::GESTIONNAIRE_COOPERATIVE)->value('id'),
            'two_factor_enabled' => false,
        ]);

        $compteVente = CompteOhada::where('code', '701')->first();

        for ($i = 0; $i < 50; $i++) {
            $membre = Exploitation::create([
                'nom' => "Membre {$i}", 'type' => 'individuelle', 'cooperative_id' => $cooperative->id,
                'consentement_cooperative_donne_le' => now(),
            ]);
            Animal::create(['exploitation_id' => $membre->id, 'espece' => 'bovin', 'sexe' => 'femelle', 'statut' => 'actif']);
            Recette::create([
                'exploitation_id' => $membre->id, 'compte_ohada_id' => $compteVente->id,
                'montant' => 10000, 'date_operation' => now(), 'libelle' => 'x',
            ]);
        }

        $this->actingAs($gerantCoop, 'sanctum');

        $debut = microtime(true);
        $reponse = $this->getJson("/api/cooperatives/{$cooperative->id}/rapport")->assertOk()->json();
        $duree = microtime(true) - $debut;

        $this->assertLessThan(10, $duree);
        $this->assertSame(50, $reponse['nombre_membres_consentants']);
        $this->assertSame(50, $reponse['total_animaux_actifs']);
        $this->assertEquals(500000.0, $reponse['total_produits']);
    }

    public function test_rapport_exclut_les_membres_non_consentants(): void
    {
        $cooperative = Exploitation::create(['nom' => 'Coop Test', 'type' => 'cooperative', 'max_membres' => 50]);
        Exploitation::create([
            'nom' => 'Consentant', 'type' => 'individuelle', 'cooperative_id' => $cooperative->id,
            'consentement_cooperative_donne_le' => now(),
        ]);
        Exploitation::create([
            'nom' => 'Non consentant', 'type' => 'individuelle', 'cooperative_id' => $cooperative->id,
        ]);

        $service = app(CooperativeAggregationService::class);
        $rapport = $service->rapportAgrege($cooperative);

        $this->assertSame(2, $rapport['nombre_membres_total']);
        $this->assertSame(1, $rapport['nombre_membres_consentants']);
    }

    /** T077 : alerte solidarité déclenchée par un incident critique. */
    public function test_incident_critique_declenche_alerte_solidarite(): void
    {
        Notification::fake();

        $cooperative = Exploitation::create(['nom' => 'Coop Test', 'type' => 'cooperative', 'max_membres' => 50]);
        $gerantCoop = User::factory()->create([
            'exploitation_id' => $cooperative->id,
            'role_id' => Role::where('slug', Role::GESTIONNAIRE_COOPERATIVE)->value('id'),
        ]);

        $membre = Exploitation::create(['nom' => 'Membre A', 'type' => 'individuelle', 'cooperative_id' => $cooperative->id]);
        $gerantMembre = User::factory()->create([
            'exploitation_id' => $membre->id,
            'role_id' => Role::where('slug', Role::GERANT)->value('id'),
            'two_factor_enabled' => false,
        ]);
        $animal = Animal::create(['exploitation_id' => $membre->id, 'espece' => 'bovin', 'sexe' => 'femelle', 'statut' => 'actif']);

        $this->actingAs($gerantMembre, 'sanctum');

        $this->postJson("/api/animaux/{$animal->id}/incidents", [
            'gravite' => 'critique',
            'description' => 'Suspicion de fièvre aphteuse',
        ])->assertCreated();

        Notification::assertSentTo($gerantCoop, AlerteSolidariteNotification::class);
    }

    public function test_incident_critique_sans_cooperative_ne_notifie_personne(): void
    {
        Notification::fake();

        $gerant = $this->gerantAuthentifie();
        $animal = Animal::create(['exploitation_id' => $gerant->exploitation_id, 'espece' => 'bovin', 'sexe' => 'femelle', 'statut' => 'actif']);

        $this->postJson("/api/animaux/{$animal->id}/incidents", ['gravite' => 'critique'])->assertCreated();

        Notification::assertNothingSent();
    }

    public function test_adhesion_refusee_si_coop_pleine(): void
    {
        $cooperative = Exploitation::create(['nom' => 'Coop Pleine', 'type' => 'cooperative', 'max_membres' => 1]);
        Exploitation::create(['nom' => 'Deja membre', 'type' => 'individuelle', 'cooperative_id' => $cooperative->id]);

        $gerant = $this->gerantAuthentifie();

        $this->postJson('/api/cooperatives/rejoindre', ['cooperative_id' => $cooperative->id])
            ->assertStatus(422);
    }
}
