<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Concerns\CreeUnGerantAuthentifie;
use Tests\TestCase;

/**
 * T017 (volet serveur) : cycle complet fiche animal → pesée → incident →
 * distribution, réalisé hors ligne côté mobile puis synchronisé en une
 * fois (rejeu simulé de la file d'actions locale via /sync/push, idempotent).
 */
class CycleOfflineTest extends TestCase
{
    use CreeUnGerantAuthentifie, RefreshDatabase;

    public function test_cycle_creation_pesee_incident_distribution_puis_sync(): void
    {
        $this->gerantAuthentifie();

        $animal = $this->postJson('/api/animaux', [
            'espece' => 'bovin', 'sexe' => 'femelle',
        ])->assertCreated()->json();

        $this->postJson("/api/animaux/{$animal['id']}/pesees", [
            'poids_kg' => 250.5,
            'date_pesee' => now()->subDays(29)->toDateString(),
        ])->assertCreated();

        $this->postJson("/api/animaux/{$animal['id']}/incidents", [
            'description' => 'Boiterie légère observée',
        ])->assertCreated()->assertJsonPath('statut', 'ouvert');

        $this->postJson('/api/alimentation', [
            'animal_id' => $animal['id'],
            'aliment' => 'Fourrage',
            'quantite_kg' => 4.5,
            'date_distribution' => now()->toDateString(),
        ])->assertCreated();

        // Simule la synchronisation différée (30 jours offline) de la file
        // d'actions locale : idempotente, même hors ordre.
        $actionId = (string) Str::uuid();
        $this->postJson('/api/sync/push', ['actions' => [[
            'id' => $actionId, 'entity_type' => 'animal', 'entity_id' => $animal['id'],
            'operation' => 'update', 'payload' => ['note' => 'suivi terrain'],
            'occurred_at' => now()->subDays(30)->toIso8601String(),
        ]]])->assertOk()->assertJsonPath('resultats.0.deja_recue', false);

        $this->postJson('/api/sync/push', ['actions' => [[
            'id' => $actionId, 'entity_type' => 'animal', 'entity_id' => $animal['id'],
            'operation' => 'update', 'payload' => ['note' => 'suivi terrain'],
            'occurred_at' => now()->subDays(30)->toIso8601String(),
        ]]])->assertOk()->assertJsonPath('resultats.0.deja_recue', true);

        $fiche = $this->getJson("/api/animaux/{$animal['id']}")->json();
        $this->assertCount(1, $fiche['pesees']);
        $this->assertCount(1, $fiche['incidents']);
    }
}
