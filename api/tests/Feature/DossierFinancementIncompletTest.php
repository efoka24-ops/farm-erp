<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreeUnGerantAuthentifie;
use Tests\TestCase;

/**
 * T038 : blocage de la génération si les données des 3 exercices sont
 * incomplètes, avec la liste précise des manques.
 */
class DossierFinancementIncompletTest extends TestCase
{
    use CreeUnGerantAuthentifie, RefreshDatabase;

    public function test_generation_refusee_avec_liste_precise_des_manques(): void
    {
        $this->gerantAuthentifie();

        $reponse = $this->postJson('/api/financement/dossiers', [
            'montant_demande' => 5000000,
            'duree_mois' => 24,
            'objet' => "Achat d'une étable",
        ]);

        $reponse->assertStatus(422);
        $manques = $reponse->json('errors.completude');

        $this->assertNotEmpty($manques);
        $this->assertStringContainsString('comptables incomplètes', $manques[0]);
        $this->assertTrue(collect($manques)->contains(fn ($m) => str_contains($m, 'cheptel')));
    }

    public function test_endpoint_completude_liste_les_manques_sans_generer(): void
    {
        $this->gerantAuthentifie();

        $reponse = $this->getJson('/api/financement/completude')->assertOk()->json();

        $this->assertFalse($reponse['complet']);
        $this->assertNotEmpty($reponse['manques']);
        $this->assertDatabaseCount('dossiers_financement', 0);
    }
}
