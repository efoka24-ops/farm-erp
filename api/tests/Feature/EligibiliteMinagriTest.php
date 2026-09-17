<?php

namespace Tests\Feature;

use App\Models\DemandeEligibiliteMinagri;
use App\Notifications\NouvelleEligibiliteMinagriNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreeUnGerantAuthentifie;
use Tests\TestCase;

/**
 * T086 : relance automatique du moteur à mise à jour significative
 * (ici : création d'un nouvel animal, cf. T088).
 */
class EligibiliteMinagriTest extends TestCase
{
    use CreeUnGerantAuthentifie, RefreshDatabase;

    public function test_creation_animal_declenche_la_detection_deligibilite(): void
    {
        Notification::fake();
        $gerant = $this->gerantAuthentifie();

        // Girinka (aucun bovin, cheptel réduit) est éligible dès la création
        // du 1er animal non-bovin.
        $this->postJson('/api/animaux', ['espece' => 'caprin', 'sexe' => 'femelle'])->assertCreated();

        $this->assertDatabaseHas('demandes_eligibilite_minagri', [
            'exploitation_id' => $gerant->exploitation_id,
            'programme' => 'girinka',
            'statut' => 'detectee',
        ]);

        Notification::assertSentTo($gerant, NouvelleEligibiliteMinagriNotification::class);
    }

    public function test_detection_est_idempotente_pas_de_doublon(): void
    {
        $gerant = $this->gerantAuthentifie();

        $this->postJson('/api/animaux', ['espece' => 'caprin', 'sexe' => 'femelle'])->assertCreated();
        $this->postJson('/api/animaux', ['espece' => 'ovin', 'sexe' => 'male'])->assertCreated();

        $this->assertSame(
            1,
            DemandeEligibiliteMinagri::where('exploitation_id', $gerant->exploitation_id)
                ->where('programme', 'girinka')->count()
        );
    }

    public function test_formulaire_pdf_genere_et_statut_passe_en_attente(): void
    {
        Storage::fake();
        $gerant = $this->gerantAuthentifie();
        $this->postJson('/api/animaux', ['espece' => 'caprin', 'sexe' => 'femelle'])->assertCreated();

        $demande = DemandeEligibiliteMinagri::where('exploitation_id', $gerant->exploitation_id)->first();

        $this->get("/api/minagri/demandes/{$demande->id}/formulaire")->assertOk();

        $this->assertDatabaseHas('demandes_eligibilite_minagri', [
            'id' => $demande->id, 'statut' => 'attente',
        ]);
    }

    public function test_suivi_statut_demande(): void
    {
        $gerant = $this->gerantAuthentifie();
        $this->postJson('/api/animaux', ['espece' => 'caprin', 'sexe' => 'femelle'])->assertCreated();
        $demande = DemandeEligibiliteMinagri::where('exploitation_id', $gerant->exploitation_id)->first();

        $this->patchJson("/api/minagri/demandes/{$demande->id}", ['statut' => 'approuvee'])
            ->assertOk()
            ->assertJsonPath('statut', 'approuvee');
    }
}
