<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\CompteOhada;
use App\Models\Depense;
use App\Models\Recette;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreeUnGerantAuthentifie;
use Tests\TestCase;

/**
 * T036/T037 : génération < 60s avec un jeu de données conséquent, et
 * vérification de la signature SHA-256 + horodatage.
 *
 * Adaptation : le jeu de données "200 membres" du plan.md suppose le module
 * coopérative (US6, Phase 11) qui n'existe pas encore. On adapte donc le test
 * de performance à une seule exploitation avec un volume de données réaliste
 * pour 3 exercices complets (cheptel + mouvements comptables), le facteur
 * limitant réel (compilation PDF + calculs) étant indépendant du nombre
 * d'exploitations traitées en une fois.
 */
class DossierFinancementGenerationTest extends TestCase
{
    use CreeUnGerantAuthentifie, RefreshDatabase;

    public function test_generation_sous_60_secondes_avec_jeu_de_donnees_consequent(): void
    {
        Storage::fake();
        $gerant = $this->gerantAuthentifie();

        $this->creerTroisExercicesComplets($gerant->exploitation_id);

        // 100 animaux, volume réaliste pour une exploitation individuelle.
        for ($i = 0; $i < 100; $i++) {
            Animal::create([
                'exploitation_id' => $gerant->exploitation_id,
                'espece' => 'bovin',
                'sexe' => $i % 2 === 0 ? 'femelle' : 'male',
            ]);
        }

        $debut = microtime(true);

        $reponse = $this->postJson('/api/financement/dossiers', [
            'montant_demande' => 8000000,
            'duree_mois' => 36,
            'objet' => "Extension de l'étable et achat de reproducteurs",
        ]);

        $duree = microtime(true) - $debut;

        $reponse->assertCreated();
        $this->assertLessThan(60, $duree, "Génération trop lente : {$duree}s");
    }

    public function test_signature_sha256_verifiable_apres_generation(): void
    {
        Storage::fake();
        $gerant = $this->gerantAuthentifie();
        $this->creerTroisExercicesComplets($gerant->exploitation_id);
        Animal::create(['exploitation_id' => $gerant->exploitation_id, 'espece' => 'bovin', 'sexe' => 'femelle']);

        $dossier = $this->postJson('/api/financement/dossiers', [
            'montant_demande' => 2000000, 'duree_mois' => 12, 'objet' => 'Fonds de roulement',
        ])->assertCreated()->json();

        $this->assertNotEmpty($dossier['signature_sha256']);
        $this->assertSame(64, strlen($dossier['signature_sha256']));
        $this->assertNotEmpty($dossier['horodatage_signature']);
        $this->assertSame('local', $dossier['horodatage_source']);

        $verification = $this->getJson("/api/financement/dossiers/{$dossier['id']}/verifier")
            ->assertOk()->json();

        $this->assertTrue($verification['signature_valide']);
    }

    public function test_mensualite_correctement_calculee(): void
    {
        Storage::fake();
        $gerant = $this->gerantAuthentifie();
        $this->creerTroisExercicesComplets($gerant->exploitation_id);
        Animal::create(['exploitation_id' => $gerant->exploitation_id, 'espece' => 'bovin', 'sexe' => 'male']);

        $dossier = $this->postJson('/api/financement/dossiers', [
            'montant_demande' => 1000000, 'duree_mois' => 12, 'objet' => 'x', 'taux_annuel_pourcent' => 0,
        ])->assertCreated()->json();

        // Taux 0% : mensualité = capital / durée, sans intérêt.
        $this->assertEquals(83333.33, (float) $dossier['mensualite']);
    }

    private function creerTroisExercicesComplets(string $exploitationId): void
    {
        $compteCharge = CompteOhada::where('code', '601')->first();
        $compteProduit = CompteOhada::where('code', '701')->first();

        foreach ([2, 1, 0] as $offset) {
            $annee = now()->subYears($offset)->year;

            Depense::create([
                'exploitation_id' => $exploitationId, 'compte_ohada_id' => $compteCharge->id,
                'montant' => 100000, 'date_operation' => "{$annee}-03-01", 'libelle' => 'Achats',
            ]);
            Recette::create([
                'exploitation_id' => $exploitationId, 'compte_ohada_id' => $compteProduit->id,
                'montant' => 300000, 'date_operation' => "{$annee}-06-01", 'libelle' => 'Ventes',
            ]);
        }
    }
}
