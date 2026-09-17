<?php

namespace Tests\Feature;

use App\Models\Exploitation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreeUnGerantAuthentifie;
use Tests\TestCase;

/**
 * T094 : le gérant peut inviter d'autres utilisateurs sur son exploitation
 * (limitation notée depuis la porte d'entrée publique — le gérant inscrit
 * était seul jusqu'ici).
 */
class UtilisateurAdministrationTest extends TestCase
{
    use CreeUnGerantAuthentifie, RefreshDatabase;

    public function test_gerant_peut_inviter_un_agent_terrain(): void
    {
        $gerant = $this->gerantAuthentifie();

        $reponse = $this->postJson('/api/utilisateurs/inviter', [
            'name' => 'Agent Terrain',
            'email' => 'agent@ferme.test',
            'role_slug' => 'agent_terrain',
        ])->assertCreated()->json();

        $this->assertNotEmpty($reponse['mot_de_passe_temporaire']);
        $this->assertDatabaseHas('users', [
            'email' => 'agent@ferme.test',
            'exploitation_id' => $gerant->exploitation_id,
        ]);

        $nouvelUtilisateur = User::where('email', 'agent@ferme.test')->first();
        $this->assertTrue($nouvelUtilisateur->hasRole('agent_terrain'));
    }

    public function test_role_non_gerant_ne_peut_pas_inviter(): void
    {
        $exploitation = $this->gerantAuthentifie()->exploitation;
        $agent = User::factory()->create([
            'exploitation_id' => $exploitation->id,
            'role_id' => Role::where('slug', Role::AGENT_TERRAIN)->value('id'),
            'two_factor_enabled' => false,
        ]);
        $this->actingAs($agent, 'sanctum');

        $this->postJson('/api/utilisateurs/inviter', [
            'name' => 'X', 'email' => 'x@x.test', 'role_slug' => 'comptable',
        ])->assertStatus(403);
    }

    public function test_gerant_peut_desactiver_un_utilisateur_de_son_exploitation(): void
    {
        $gerant = $this->gerantAuthentifie();
        $agent = User::factory()->create([
            'exploitation_id' => $gerant->exploitation_id,
            'role_id' => Role::where('slug', Role::AGENT_TERRAIN)->value('id'),
        ]);

        $this->postJson("/api/utilisateurs/{$agent->id}/desactiver")->assertOk();

        $this->assertDatabaseHas('users', ['id' => $agent->id, 'actif' => false]);
    }

    public function test_gerant_ne_peut_pas_desactiver_son_propre_compte(): void
    {
        $this->gerantAuthentifie();
        $moi = auth('sanctum')->user();

        $this->postJson("/api/utilisateurs/{$moi->id}/desactiver")->assertStatus(422);
    }

    public function test_gerant_ne_peut_pas_desactiver_un_utilisateur_dune_autre_exploitation(): void
    {
        $this->gerantAuthentifie();
        $autreExploitationUser = User::factory()->create([
            'exploitation_id' => Exploitation::create(['nom' => 'Autre', 'type' => 'individuelle'])->id,
            'role_id' => Role::where('slug', Role::AGENT_TERRAIN)->value('id'),
        ]);

        $this->postJson("/api/utilisateurs/{$autreExploitationUser->id}/desactiver")->assertStatus(403);
    }
}
