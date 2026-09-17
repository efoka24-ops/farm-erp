<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\Saillie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreeUnGerantAuthentifie;
use Tests\TestCase;

/**
 * T052 : création automatique des fiches nouveau-nés à la mise bas.
 */
class ReproductionTest extends TestCase
{
    use CreeUnGerantAuthentifie, RefreshDatabase;

    public function test_saillie_calcule_la_date_prevue_de_mise_bas(): void
    {
        $gerant = $this->gerantAuthentifie();
        $femelle = Animal::create(['exploitation_id' => $gerant->exploitation_id, 'espece' => 'bovin', 'sexe' => 'femelle']);

        $reponse = $this->postJson('/api/saillies', [
            'femelle_id' => $femelle->id,
            'date_saillie' => '2026-01-01',
        ])->assertCreated()->json();

        $this->assertStringStartsWith('2026-10-11', $reponse['date_prevue_mise_bas']);
        $this->assertSame('en_cours', $reponse['statut']);
    }

    public function test_mise_bas_cree_les_fiches_nouveau_nes_avec_filiation(): void
    {
        $gerant = $this->gerantAuthentifie();
        $femelle = Animal::create(['exploitation_id' => $gerant->exploitation_id, 'espece' => 'caprin', 'sexe' => 'femelle']);
        $male = Animal::create(['exploitation_id' => $gerant->exploitation_id, 'espece' => 'caprin', 'sexe' => 'male']);

        $saillie = $this->postJson('/api/saillies', [
            'femelle_id' => $femelle->id, 'reproducteur_id' => $male->id, 'date_saillie' => '2026-01-01',
        ])->json();

        $reponse = $this->postJson("/api/saillies/{$saillie['id']}/mise-bas", [
            'date_mise_bas' => '2026-06-01',
            'nombre_nes' => 3,
            'nombre_survivants' => 2,
            'sexes_nouveau_nes' => ['male', 'femelle'],
        ])->assertCreated()->json();

        $this->assertCount(2, $reponse['nouveau_nes']);

        foreach ($reponse['nouveau_nes'] as $ne) {
            $this->assertSame($femelle->id, $ne['mere_id']);
            $this->assertSame($male->id, $ne['pere_id']);
            $this->assertStringStartsWith('TRU-', $ne['tru_trace_id']);
            $this->assertSame('caprin', $ne['espece']);
        }

        $this->assertDatabaseHas('saillies', ['id' => $saillie['id'], 'statut' => 'mise_bas']);
    }

    public function test_alertes_j14_et_j7_avant_mise_bas(): void
    {
        $gerant = $this->gerantAuthentifie();
        $femelle = Animal::create(['exploitation_id' => $gerant->exploitation_id, 'espece' => 'ovin', 'sexe' => 'femelle']);

        Saillie::create([
            'exploitation_id' => $gerant->exploitation_id,
            'femelle_id' => $femelle->id,
            'date_saillie' => now()->subDays(10),
            'date_prevue_mise_bas' => now()->addDays(14),
            'statut' => 'en_cours',
        ]);

        $alertes = $this->getJson('/api/saillies/alertes')->assertOk()->json();

        $this->assertCount(1, $alertes['j_14']);
        $this->assertCount(0, $alertes['j_7']);
    }
}
