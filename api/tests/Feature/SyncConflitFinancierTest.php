<?php

namespace Tests\Feature;

use App\Models\SyncConflict;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\Concerns\CreeUnGerantAuthentifie;
use Tests\TestCase;

/**
 * T019 : arbitrage manuel des conflits sur données financières à la synchronisation.
 */
class SyncConflitFinancierTest extends TestCase
{
    use CreeUnGerantAuthentifie, RefreshDatabase;

    public function test_deux_actions_concurrentes_sur_une_entite_financiere_creent_un_conflit(): void
    {
        Notification::fake();

        $gerant = $this->gerantAuthentifie();
        $entityId = (string) Str::uuid();

        // Première action (plus ancienne) appliquée sans conflit.
        $this->postJson('/api/sync/push', [
            'actions' => [[
                'id' => (string) Str::uuid(),
                'entity_type' => 'depense',
                'entity_id' => $entityId,
                'operation' => 'create',
                'payload' => ['montant' => 5000],
                'occurred_at' => now()->subHour()->toIso8601String(),
            ]],
        ])->assertOk()->assertJsonPath('resultats.0.statut', 'applique');

        // Seconde action, plus ancienne encore (occurred_at antérieur à une action déjà
        // appliquée) sur la même entité : conflit détecté, arbitrage requis.
        $response = $this->postJson('/api/sync/push', [
            'actions' => [[
                'id' => (string) Str::uuid(),
                'entity_type' => 'depense',
                'entity_id' => $entityId,
                'operation' => 'update',
                'payload' => ['montant' => 4500],
                'occurred_at' => now()->subHours(2)->toIso8601String(),
            ]],
        ]);

        $response->assertOk()->assertJsonPath('resultats.0.statut', 'conflit');
        $this->assertDatabaseHas('sync_conflicts', [
            'entity_id' => $entityId,
            'est_financier' => true,
            'statut' => 'ouvert',
        ]);
    }

    public function test_resolution_dun_conflit_par_le_gerant(): void
    {
        Notification::fake();
        $gerant = $this->gerantAuthentifie();
        $entityId = (string) Str::uuid();

        $this->postJson('/api/sync/push', ['actions' => [[
            'id' => (string) Str::uuid(), 'entity_type' => 'depense', 'entity_id' => $entityId,
            'operation' => 'create', 'payload' => ['montant' => 1000],
            'occurred_at' => now()->subHour()->toIso8601String(),
        ]]]);

        $this->postJson('/api/sync/push', ['actions' => [[
            'id' => (string) Str::uuid(), 'entity_type' => 'depense', 'entity_id' => $entityId,
            'operation' => 'update', 'payload' => ['montant' => 900],
            'occurred_at' => now()->subHours(2)->toIso8601String(),
        ]]]);

        $conflit = SyncConflict::first();

        $this->postJson("/api/sync/conflits/{$conflit->id}/resoudre", [
            'resolution' => 'resolu_serveur',
        ])->assertOk()->assertJsonPath('statut', 'resolu_serveur');

        $this->assertDatabaseHas('sync_conflicts', [
            'id' => $conflit->id,
            'statut' => 'resolu_serveur',
            'resolu_par' => $gerant->id,
        ]);
    }
}
