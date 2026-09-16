<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\ImportMokinevotoEchoue;
use App\Services\Sante\MokineVetoImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\Concerns\CreeUnGerantAuthentifie;
use Tests\TestCase;

/**
 * T044 : import automatique MokineVeto → DMA.
 */
class MokineVetoImportTest extends TestCase
{
    use CreeUnGerantAuthentifie, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.mokinevoto.webhook_secret', 'secret-test');
    }

    public function test_consultation_mokinevoto_apparait_dans_le_dma(): void
    {
        $gerant = $this->gerantAuthentifie();
        $animal = Animal::create([
            'exploitation_id' => $gerant->exploitation_id, 'espece' => 'bovin', 'sexe' => 'femelle',
        ]);

        $this->postJson('/api/webhooks/mokinevoto/consultations', [
            'tru_trace_id' => $animal->tru_trace_id,
            'mokinevoto_consultation_id' => 'MKV-001',
            'veterinaire' => 'Dr Uwimana',
            'date_consultation' => now()->toDateString(),
            'diagnostic' => 'RAS',
        ], ['X-MokineVeto-Secret' => 'secret-test'])->assertCreated();

        $dma = $this->getJson("/api/animaux/{$animal->id}/dma")->assertOk()->json();

        $this->assertCount(1, $dma['consultations']);
        $this->assertSame('mokinevoto', $dma['consultations'][0]['source']);
    }

    public function test_webhook_refuse_sans_secret_valide(): void
    {
        $this->postJson('/api/webhooks/mokinevoto/consultations', [
            'tru_trace_id' => 'TRU-XXXX',
            'mokinevoto_consultation_id' => 'MKV-002',
            'date_consultation' => now()->toDateString(),
        ], ['X-MokineVeto-Secret' => 'mauvais-secret'])->assertStatus(401);
    }

    public function test_mode_degrade_conserve_la_consultation_si_animal_inconnu(): void
    {
        $this->postJson('/api/webhooks/mokinevoto/consultations', [
            'tru_trace_id' => 'TRU-INCONNU',
            'mokinevoto_consultation_id' => 'MKV-003',
            'date_consultation' => now()->toDateString(),
        ], ['X-MokineVeto-Secret' => 'secret-test'])
            ->assertStatus(202)
            ->assertJsonPath('en_attente', true);

        $this->assertDatabaseHas('imports_mokinevoto_echoues', [
            'raison' => "Animal TRU TRACE 'TRU-INCONNU' introuvable",
        ]);
    }

    public function test_rejeu_des_echecs_resout_une_fois_lanimal_cree(): void
    {
        $gerant = $this->gerantAuthentifie();

        ImportMokinevotoEchoue::create([
            'payload' => [
                'tru_trace_id' => 'TRU-REJEU',
                'mokinevoto_consultation_id' => 'MKV-004',
                'date_consultation' => now()->toDateString(),
            ],
            'raison' => 'Animal introuvable',
        ]);

        Animal::create([
            'exploitation_id' => $gerant->exploitation_id, 'espece' => 'caprin', 'sexe' => 'male',
            'tru_trace_id' => 'TRU-REJEU',
        ]);

        $resolus = app(MokineVetoImportService::class)->rejouerEchecs();

        $this->assertSame(1, $resolus);
        $this->assertDatabaseHas('consultations_veterinaires', ['mokinevoto_consultation_id' => 'MKV-004']);
    }
}
