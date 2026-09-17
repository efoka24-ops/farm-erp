<?php

namespace Tests\Feature;

use App\Models\Animal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreeUnGerantAuthentifie;
use Tests\TestCase;

/**
 * T061 : génération du certificat TRU TRACE à la vente.
 */
class VenteTruTraceTest extends TestCase
{
    use CreeUnGerantAuthentifie, RefreshDatabase;

    public function test_vente_genere_bon_pdf_et_certificat_tru_trace(): void
    {
        Storage::fake();
        $gerant = $this->gerantAuthentifie();
        $animal = Animal::create([
            'exploitation_id' => $gerant->exploitation_id, 'espece' => 'bovin', 'sexe' => 'male', 'statut' => 'actif',
        ]);

        $vente = $this->postJson('/api/ventes', [
            'animal_id' => $animal->id,
            'client_nom_libre' => 'Marché de Kigali',
            'montant' => 250000,
            'date_vente' => now()->toDateString(),
        ])->assertCreated()->json();

        $this->assertNotEmpty($vente['certificat_tru_trace_reference']);
        $this->assertStringStartsWith('CERT-LOCAL-', $vente['certificat_tru_trace_reference']);
        $this->assertNotEmpty($vente['bon_pdf_path']);

        $this->assertDatabaseHas('animaux', ['id' => $animal->id, 'statut' => 'vendu']);

        $this->get("/api/ventes/{$vente['id']}/bon")->assertOk();
    }

    public function test_vente_cree_automatiquement_une_recette_comptable(): void
    {
        Storage::fake();
        $gerant = $this->gerantAuthentifie();
        $animal = Animal::create([
            'exploitation_id' => $gerant->exploitation_id, 'espece' => 'caprin', 'sexe' => 'femelle', 'statut' => 'actif',
        ]);

        $this->postJson('/api/ventes', [
            'animal_id' => $animal->id, 'montant' => 80000, 'date_vente' => now()->toDateString(),
        ])->assertCreated();

        $this->assertDatabaseHas('recettes', [
            'animal_id' => $animal->id,
            'montant' => 80000.00,
        ]);
    }

    public function test_vente_bloquee_sous_delai_dattente(): void
    {
        Storage::fake();
        $gerant = $this->gerantAuthentifie();
        $animal = Animal::create([
            'exploitation_id' => $gerant->exploitation_id, 'espece' => 'bovin', 'sexe' => 'femelle', 'statut' => 'actif',
        ]);

        $this->postJson("/api/animaux/{$animal->id}/traitements", [
            'medicament' => 'Antibiotique', 'date_debut' => now()->toDateString(), 'delai_attente_jours' => 10,
        ])->assertCreated();

        $this->postJson('/api/ventes', [
            'animal_id' => $animal->id, 'montant' => 100000, 'date_vente' => now()->toDateString(),
        ])->assertStatus(422)->assertJsonValidationErrors('animal_id');

        $this->assertDatabaseHas('animaux', ['id' => $animal->id, 'statut' => 'actif']);
    }

    public function test_vente_avec_tva_calcule_le_montant_ttc(): void
    {
        Storage::fake();
        $gerant = $this->gerantAuthentifie();
        $animal = Animal::create([
            'exploitation_id' => $gerant->exploitation_id, 'espece' => 'bovin', 'sexe' => 'male', 'statut' => 'actif',
        ]);

        $this->postJson('/api/ventes', [
            'animal_id' => $animal->id, 'montant' => 100000, 'date_vente' => now()->toDateString(),
            'tva_applicable' => true, 'taux_tva_pourcent' => 18,
        ])->assertCreated();

        $this->assertDatabaseHas('recettes', ['animal_id' => $animal->id, 'montant' => 118000.00]);
    }
}
