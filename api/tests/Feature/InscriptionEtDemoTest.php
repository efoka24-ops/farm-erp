<?php

namespace Tests\Feature;

use App\Models\Exploitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Porte d'entrée publique : création de compte pour sa ferme, et démo
 * restreinte à 5 jours qui bascule en accès bloqué à l'expiration.
 */
class InscriptionEtDemoTest extends TestCase
{
    use RefreshDatabase;

    public function test_inscription_cree_une_exploitation_et_un_gerant_actif(): void
    {
        $reponse = $this->postJson('/api/auth/register', [
            'nom_exploitation' => 'Ferme Uwimana',
            'nom' => 'Jean Uwimana',
            'email' => 'jean@uwimana.test',
            'password' => 'motdepasse123',
        ]);

        $reponse->assertCreated()->assertJsonStructure(['token', 'user']);

        $exploitation = Exploitation::where('nom', 'Ferme Uwimana')->first();
        $this->assertSame('production', $exploitation->mode);
        $this->assertNull($exploitation->essai_expire_le);

        $user = User::where('email', 'jean@uwimana.test')->first();
        $this->assertTrue($user->hasRole('gerant'));
        $this->assertTrue($user->actif);
    }

    public function test_inscription_refuse_un_email_deja_utilise(): void
    {
        $this->postJson('/api/auth/register', [
            'nom_exploitation' => 'Ferme A', 'nom' => 'A', 'email' => 'x@x.test', 'password' => 'password1',
        ])->assertCreated();

        $this->postJson('/api/auth/register', [
            'nom_exploitation' => 'Ferme B', 'nom' => 'B', 'email' => 'x@x.test', 'password' => 'password2',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_demo_est_immediatement_utilisable_et_peuplee(): void
    {
        $reponse = $this->postJson('/api/auth/register-demo', [
            'nom_exploitation' => 'Ferme Démo', 'nom' => 'Visiteur', 'email' => 'demo@visiteur.test', 'password' => 'password1',
        ])->assertCreated();

        $token = $reponse->json('token');
        $exploitation = Exploitation::where('nom', 'Ferme Démo')->first();

        $this->assertSame('demo', $exploitation->mode);
        $this->assertNotNull($exploitation->essai_expire_le);
        $this->assertTrue($exploitation->essai_expire_le->isAfter(now()->addDays(4)));

        // La démo est immédiatement utilisable : peuplée de données, accès non bloqué.
        $this->withToken($token)->getJson('/api/dashboard')->assertOk();
        $this->withToken($token)->getJson('/api/animaux')->assertOk()
            ->assertJsonCount(6, 'data');
    }

    public function test_acces_bloque_apres_expiration_de_la_demo(): void
    {
        $reponse = $this->postJson('/api/auth/register-demo', [
            'nom_exploitation' => 'Ferme Expirée', 'nom' => 'Visiteur', 'email' => 'expire@visiteur.test', 'password' => 'password1',
        ])->assertCreated();

        $token = $reponse->json('token');

        Exploitation::where('nom', 'Ferme Expirée')->first()
            ->update(['essai_expire_le' => now()->subDay()]);

        $this->withToken($token)->getJson('/api/dashboard')
            ->assertStatus(403)
            ->assertJsonFragment(['message' => "Votre période d'essai de 5 jours est terminée. Contactez-nous pour activer un compte complet."]);

        $this->assertSame('expire', Exploitation::where('nom', 'Ferme Expirée')->first()->statut_compte);
    }

    public function test_compte_production_nexpire_jamais(): void
    {
        $reponse = $this->postJson('/api/auth/register', [
            'nom_exploitation' => 'Ferme Pérenne', 'nom' => 'Gérant', 'email' => 'perenne@test.test', 'password' => 'password1',
        ])->assertCreated();

        $this->withToken($reponse->json('token'))->getJson('/api/dashboard')->assertOk();
    }
}
