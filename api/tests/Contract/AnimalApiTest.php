<?php

namespace Tests\Contract;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreeUnGerantAuthentifie;
use Tests\TestCase;

/**
 * T018 : contrat API POST /animaux, GET /animaux/:id.
 */
class AnimalApiTest extends TestCase
{
    use CreeUnGerantAuthentifie, RefreshDatabase;

    public function test_post_animaux_cree_un_animal_avec_id_tru_trace(): void
    {
        $this->gerantAuthentifie();

        $response = $this->postJson('/api/animaux', [
            'espece' => 'bovin',
            'race' => 'Ankole',
            'sexe' => 'femelle',
            'date_naissance' => '2024-01-15',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['id', 'tru_trace_id', 'espece', 'sexe', 'statut'])
            ->assertJsonPath('statut', 'actif')
            ->assertJsonPath('espece', 'bovin');

        $this->assertStringStartsWith('TRU-', $response->json('tru_trace_id'));
    }

    public function test_post_animaux_rejette_un_sexe_invalide(): void
    {
        $this->gerantAuthentifie();

        $this->postJson('/api/animaux', ['espece' => 'bovin', 'sexe' => 'inconnu'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('sexe');
    }

    public function test_get_animal_retourne_les_relations_pesees_et_incidents(): void
    {
        $this->gerantAuthentifie();

        $id = $this->postJson('/api/animaux', ['espece' => 'caprin', 'sexe' => 'male'])
            ->json('id');

        $response = $this->getJson("/api/animaux/{$id}");

        $response->assertOk()
            ->assertJsonStructure(['id', 'pesees', 'incidents']);
    }

    public function test_get_animal_dune_autre_exploitation_est_invisible(): void
    {
        $this->gerantAuthentifie();
        $id = $this->postJson('/api/animaux', ['espece' => 'bovin', 'sexe' => 'male'])->json('id');

        // Bascule sur un autre gérant, autre exploitation (RLS applicative, T009)
        $this->gerantAuthentifie();

        $this->getJson("/api/animaux/{$id}")->assertNotFound();
    }

    public function test_recherche_par_id_tru_trace_scan_qr(): void
    {
        $this->gerantAuthentifie();
        $truTraceId = $this->postJson('/api/animaux', ['espece' => 'ovin', 'sexe' => 'femelle'])
            ->json('tru_trace_id');

        $this->getJson("/api/animaux/trace/{$truTraceId}")
            ->assertOk()
            ->assertJsonPath('tru_trace_id', $truTraceId);
    }
}
