<?php

namespace Tests\Unit;

use App\Models\Animal;
use App\Models\CompteOhada;
use App\Models\Depense;
use App\Models\Exploitation;
use App\Models\Role;
use App\Models\User;
use App\Services\Comptabilite\CoutRevientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * T027 : tests unitaires coût de revient / seuil de rentabilité.
 */
class CoutRevientServiceTest extends TestCase
{
    use RefreshDatabase;

    private CoutRevientService $service;

    private Exploitation $exploitation;

    private Animal $animal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new CoutRevientService;
        $this->exploitation = Exploitation::create(['nom' => 'Test', 'type' => 'individuelle']);

        $gerant = User::factory()->create([
            'exploitation_id' => $this->exploitation->id,
            'role_id' => Role::where('slug', Role::GERANT)->value('id'),
        ]);
        $this->actingAs($gerant, 'sanctum');

        $this->animal = Animal::create([
            'exploitation_id' => $this->exploitation->id,
            'espece' => 'bovin', 'sexe' => 'femelle',
            'date_naissance' => now()->subDays(100),
        ]);
    }

    public function test_cout_total_additionne_les_depenses_liees_a_lanimal(): void
    {
        $compte = CompteOhada::where('code', '602')->first();

        Depense::create([
            'exploitation_id' => $this->exploitation->id, 'compte_ohada_id' => $compte->id,
            'montant' => 5000, 'date_operation' => now(), 'libelle' => 'Vaccin', 'animal_id' => $this->animal->id,
        ]);
        Depense::create([
            'exploitation_id' => $this->exploitation->id, 'compte_ohada_id' => $compte->id,
            'montant' => 15000, 'date_operation' => now(), 'libelle' => 'Traitement', 'animal_id' => $this->animal->id,
        ]);

        $resultat = $this->service->coutRevient($this->animal);

        $this->assertSame(20000.0, $resultat['cout_total']);
        $this->assertSame(2, $resultat['nombre_depenses']);
        $this->assertSame(200.0, $resultat['cout_journalier']); // 20000 / 100 jours
    }

    public function test_depenses_dun_autre_animal_ne_sont_pas_comptees(): void
    {
        $autreAnimal = Animal::create([
            'exploitation_id' => $this->exploitation->id, 'espece' => 'caprin', 'sexe' => 'male',
        ]);
        $compte = CompteOhada::where('code', '601')->first();

        Depense::create([
            'exploitation_id' => $this->exploitation->id, 'compte_ohada_id' => $compte->id,
            'montant' => 9999, 'date_operation' => now(), 'libelle' => 'x', 'animal_id' => $autreAnimal->id,
        ]);

        $resultat = $this->service->coutRevient($this->animal);

        $this->assertSame(0.0, $resultat['cout_total']);
    }

    public function test_seuil_rentabilite_applique_la_marge_ciblee(): void
    {
        $compte = CompteOhada::where('code', '601')->first();

        Depense::create([
            'exploitation_id' => $this->exploitation->id, 'compte_ohada_id' => $compte->id,
            'montant' => 100000, 'date_operation' => now(), 'libelle' => 'x', 'animal_id' => $this->animal->id,
        ]);

        $seuil = $this->service->seuilRentabilite($this->animal, margeCiblePourcent: 20);

        $this->assertSame(120000.0, $seuil['prix_vente_minimum']);
    }

    public function test_seuil_rentabilite_sans_marge_egale_le_cout(): void
    {
        $compte = CompteOhada::where('code', '601')->first();

        Depense::create([
            'exploitation_id' => $this->exploitation->id, 'compte_ohada_id' => $compte->id,
            'montant' => 75000, 'date_operation' => now(), 'libelle' => 'x', 'animal_id' => $this->animal->id,
        ]);

        $seuil = $this->service->seuilRentabilite($this->animal);

        $this->assertSame(75000.0, $seuil['prix_vente_minimum']);
    }
}
