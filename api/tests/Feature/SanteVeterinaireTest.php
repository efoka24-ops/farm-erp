<?php

namespace Tests\Feature;

use App\Models\Animal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreeUnGerantAuthentifie;
use Tests\TestCase;

class SanteVeterinaireTest extends TestCase
{
    use CreeUnGerantAuthentifie, RefreshDatabase;

    public function test_calendrier_vaccinal_planifie_automatiquement_a_la_creation(): void
    {
        $this->gerantAuthentifie();

        $animal = $this->postJson('/api/animaux', [
            'espece' => 'bovin', 'sexe' => 'femelle', 'date_naissance' => now()->subDays(200)->toDateString(),
        ])->json();

        $vaccinations = $this->getJson("/api/animaux/{$animal['id']}/vaccinations")->assertOk()->json();

        // 3 protocoles bovins seedés (fièvre aphteuse, charbon symptomatique, pasteurellose)
        $this->assertCount(3, $vaccinations);
        $this->assertSame('planifie', $vaccinations[0]['statut']);
    }

    public function test_aucune_planification_sans_date_de_naissance(): void
    {
        $this->gerantAuthentifie();

        $animal = $this->postJson('/api/animaux', ['espece' => 'bovin', 'sexe' => 'male'])->json();

        $vaccinations = $this->getJson("/api/animaux/{$animal['id']}/vaccinations")->assertOk()->json();

        $this->assertCount(0, $vaccinations);
    }

    public function test_campagne_groupee_vaccine_plusieurs_animaux(): void
    {
        $gerant = $this->gerantAuthentifie();
        $a1 = Animal::create(['exploitation_id' => $gerant->exploitation_id, 'espece' => 'caprin', 'sexe' => 'femelle']);
        $a2 = Animal::create(['exploitation_id' => $gerant->exploitation_id, 'espece' => 'caprin', 'sexe' => 'male']);

        $reponse = $this->postJson('/api/vaccinations/campagne', [
            'animal_ids' => [$a1->id, $a2->id],
            'vaccin' => 'PPR',
            'date_administration' => now()->toDateString(),
        ])->assertCreated()->json();

        $this->assertSame(2, $reponse['nombre_animaux']);
        $this->assertDatabaseCount('vaccinations', 2);
    }

    public function test_alertes_j14_et_j3_retournent_les_vaccinations_a_echeance(): void
    {
        $gerant = $this->gerantAuthentifie();
        $animal = Animal::create(['exploitation_id' => $gerant->exploitation_id, 'espece' => 'bovin', 'sexe' => 'femelle']);

        $animal->vaccinations()->create([
            'exploitation_id' => $gerant->exploitation_id,
            'vaccin' => 'Fièvre aphteuse',
            'date_prevue' => now()->addDays(14)->toDateString(),
            'statut' => 'planifie',
        ]);
        $animal->vaccinations()->create([
            'exploitation_id' => $gerant->exploitation_id,
            'vaccin' => 'Charbon',
            'date_prevue' => now()->addDays(3)->toDateString(),
            'statut' => 'planifie',
        ]);

        $alertes = $this->getJson('/api/vaccinations/alertes')->assertOk()->json();

        $this->assertCount(1, $alertes['j_14']);
        $this->assertCount(1, $alertes['j_3']);
    }
}
