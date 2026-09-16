<?php

namespace Tests\Feature;

use App\Models\Animal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreeUnGerantAuthentifie;
use Tests\TestCase;

/**
 * T045 : blocage de la vente d'un animal sous délai d'attente de traitement.
 */
class BlocageVenteDelaiAttenteTest extends TestCase
{
    use CreeUnGerantAuthentifie, RefreshDatabase;

    public function test_vente_bloquee_si_delai_attente_non_ecoule(): void
    {
        $gerant = $this->gerantAuthentifie();
        $animal = Animal::create([
            'exploitation_id' => $gerant->exploitation_id, 'espece' => 'bovin', 'sexe' => 'femelle',
        ]);

        $this->postJson("/api/animaux/{$animal->id}/traitements", [
            'medicament' => 'Antibiotique X',
            'date_debut' => now()->toDateString(),
            'delai_attente_jours' => 10,
        ])->assertCreated();

        $this->putJson("/api/animaux/{$animal->id}", ['statut' => 'vendu'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('statut');

        $this->assertDatabaseHas('animaux', ['id' => $animal->id, 'statut' => 'actif']);
    }

    public function test_vente_autorisee_apres_delai_attente_ecoule(): void
    {
        $gerant = $this->gerantAuthentifie();
        $animal = Animal::create([
            'exploitation_id' => $gerant->exploitation_id, 'espece' => 'bovin', 'sexe' => 'male',
        ]);

        $this->postJson("/api/animaux/{$animal->id}/traitements", [
            'medicament' => 'Vermifuge',
            'date_debut' => now()->subDays(20)->toDateString(),
            'delai_attente_jours' => 5,
        ])->assertCreated();

        $this->putJson("/api/animaux/{$animal->id}", ['statut' => 'vendu'])->assertOk();
    }

    public function test_vente_autorisee_sans_traitement(): void
    {
        $gerant = $this->gerantAuthentifie();
        $animal = Animal::create([
            'exploitation_id' => $gerant->exploitation_id, 'espece' => 'ovin', 'sexe' => 'femelle',
        ]);

        $this->putJson("/api/animaux/{$animal->id}", ['statut' => 'vendu'])->assertOk();
    }
}
