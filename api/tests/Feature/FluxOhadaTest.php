<?php

namespace Tests\Feature;

use App\Models\CompteOhada;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreeUnGerantAuthentifie;
use Tests\TestCase;

/**
 * T028 : intégration flux dépense/recette → état financier, via l'API.
 */
class FluxOhadaTest extends TestCase
{
    use CreeUnGerantAuthentifie, RefreshDatabase;

    public function test_saisie_depense_recette_puis_generation_compte_de_resultat(): void
    {
        $this->gerantAuthentifie();

        $compteAchat = CompteOhada::where('code', '601')->first();
        $compteVente = CompteOhada::where('code', '701')->first();

        $this->postJson('/api/depenses', [
            'compte_ohada_id' => $compteAchat->id,
            'montant' => 45000,
            'date_operation' => now()->startOfMonth()->addDays(2)->toDateString(),
            'libelle' => 'Achat fourrage mensuel',
        ])->assertCreated();

        $this->postJson('/api/recettes', [
            'compte_ohada_id' => $compteVente->id,
            'montant' => 200000,
            'date_operation' => now()->startOfMonth()->addDays(10)->toDateString(),
            'libelle' => 'Vente 2 bovins',
        ])->assertCreated();

        // assertEquals (pas assertSame) : le JSON ne distingue pas int/float
        // pour un nombre entier (45000.0 est sérialisé "45000").
        $compteResultat = $this->getJson('/api/comptabilite/compte-resultat')->assertOk()->json();

        $this->assertEquals(45000.0, $compteResultat['total_charges']);
        $this->assertEquals(200000.0, $compteResultat['total_produits']);
        $this->assertEquals(155000.0, $compteResultat['resultat_net']);

        $bilan = $this->getJson('/api/comptabilite/bilan')->assertOk()->json();
        $this->assertTrue($bilan['equilibre']);

        $tresorerie = $this->getJson('/api/comptabilite/tresorerie')->assertOk()->json();
        $this->assertEquals(155000.0, $tresorerie['flux_net']);
    }

    public function test_plan_comptable_accessible_et_non_vide(): void
    {
        $this->gerantAuthentifie();

        $this->getJson('/api/comptabilite/plan-comptable')
            ->assertOk()
            ->assertJsonCount(15);
    }

    public function test_paiement_mtn_momo_non_configure_retourne_erreur_explicite(): void
    {
        $this->gerantAuthentifie();
        $compte = CompteOhada::where('code', '602')->first();

        $depense = $this->postJson('/api/depenses', [
            'compte_ohada_id' => $compte->id,
            'montant' => 10000,
            'date_operation' => now()->toDateString(),
            'libelle' => 'Facture fournisseur',
        ])->json();

        $this->postJson("/api/depenses/{$depense['id']}/payer-mtn-momo", [
            'numero_destinataire' => '250788123456',
        ])->assertStatus(422)->assertJsonValidationErrors('mtn_momo');
    }
}
