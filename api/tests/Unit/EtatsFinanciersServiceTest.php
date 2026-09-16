<?php

namespace Tests\Unit;

use App\Models\CompteOhada;
use App\Models\Depense;
use App\Models\Exploitation;
use App\Models\Recette;
use App\Models\Role;
use App\Models\User;
use App\Services\Comptabilite\EtatsFinanciersService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * T027 : tests unitaires des calculs OHADA (résultat net, bilan, trésorerie).
 */
class EtatsFinanciersServiceTest extends TestCase
{
    use RefreshDatabase;

    private EtatsFinanciersService $service;

    private Exploitation $exploitation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new EtatsFinanciersService;
        $this->exploitation = Exploitation::create(['nom' => 'Test', 'type' => 'individuelle']);

        $gerant = User::factory()->create([
            'exploitation_id' => $this->exploitation->id,
            'role_id' => Role::where('slug', Role::GERANT)->value('id'),
        ]);
        $this->actingAs($gerant, 'sanctum');
    }

    public function test_resultat_net_est_produits_moins_charges(): void
    {
        $compteAchat = CompteOhada::where('code', '601')->first();
        $compteVente = CompteOhada::where('code', '701')->first();

        Depense::create([
            'exploitation_id' => $this->exploitation->id, 'compte_ohada_id' => $compteAchat->id,
            'montant' => 30000, 'date_operation' => '2026-01-10', 'libelle' => 'Fourrage',
        ]);
        Recette::create([
            'exploitation_id' => $this->exploitation->id, 'compte_ohada_id' => $compteVente->id,
            'montant' => 120000, 'date_operation' => '2026-01-15', 'libelle' => 'Vente bovin',
        ]);

        $etat = $this->service->compteDeResultat(
            $this->exploitation->id,
            Carbon::parse('2026-01-01'),
            Carbon::parse('2026-01-31'),
        );

        $this->assertSame(30000.0, $etat['total_charges']);
        $this->assertSame(120000.0, $etat['total_produits']);
        $this->assertSame(90000.0, $etat['resultat_net']);
    }

    public function test_resultat_net_negatif_si_charges_superieures(): void
    {
        $compte = CompteOhada::where('code', '602')->first();

        Depense::create([
            'exploitation_id' => $this->exploitation->id, 'compte_ohada_id' => $compte->id,
            'montant' => 50000, 'date_operation' => '2026-02-01', 'libelle' => 'Vétérinaire',
        ]);

        $etat = $this->service->compteDeResultat(
            $this->exploitation->id, Carbon::parse('2026-02-01'), Carbon::parse('2026-02-28'),
        );

        $this->assertSame(-50000.0, $etat['resultat_net']);
    }

    public function test_operations_hors_periode_sont_exclues(): void
    {
        $compte = CompteOhada::where('code', '601')->first();

        Depense::create([
            'exploitation_id' => $this->exploitation->id, 'compte_ohada_id' => $compte->id,
            'montant' => 10000, 'date_operation' => '2025-12-31', 'libelle' => 'Hors période',
        ]);

        $etat = $this->service->compteDeResultat(
            $this->exploitation->id, Carbon::parse('2026-01-01'), Carbon::parse('2026-01-31'),
        );

        $this->assertSame(0.0, $etat['total_charges']);
    }

    public function test_bilan_est_equilibre_par_construction(): void
    {
        $compteAchat = CompteOhada::where('code', '601')->first();
        $compteVente = CompteOhada::where('code', '701')->first();

        Depense::create([
            'exploitation_id' => $this->exploitation->id, 'compte_ohada_id' => $compteAchat->id,
            'montant' => 20000, 'date_operation' => '2026-01-05', 'libelle' => 'Achat',
        ]);
        Recette::create([
            'exploitation_id' => $this->exploitation->id, 'compte_ohada_id' => $compteVente->id,
            'montant' => 50000, 'date_operation' => '2026-01-06', 'libelle' => 'Vente',
        ]);

        $bilan = $this->service->bilanSimplifie($this->exploitation->id, Carbon::parse('2026-01-31'));

        $this->assertTrue($bilan['equilibre']);
        $this->assertSame(30000.0, $bilan['actif']['total']);
        $this->assertSame(30000.0, $bilan['passif']['total']);
    }

    public function test_tableau_tresorerie_flux_net(): void
    {
        $compteAchat = CompteOhada::where('code', '601')->first();
        $compteVente = CompteOhada::where('code', '701')->first();

        Depense::create([
            'exploitation_id' => $this->exploitation->id, 'compte_ohada_id' => $compteAchat->id,
            'montant' => 15000, 'date_operation' => '2026-03-01', 'libelle' => 'x', 'mode_paiement' => 'especes',
        ]);
        Recette::create([
            'exploitation_id' => $this->exploitation->id, 'compte_ohada_id' => $compteVente->id,
            'montant' => 40000, 'date_operation' => '2026-03-02', 'libelle' => 'y', 'mode_paiement' => 'mtn_momo',
        ]);

        $tresorerie = $this->service->tableauTresorerie(
            $this->exploitation->id, Carbon::parse('2026-03-01'), Carbon::parse('2026-03-31'),
        );

        $this->assertSame(25000.0, $tresorerie['flux_net']);
        $this->assertSame(15000.0, $tresorerie['par_mode_paiement']['especes']['decaissements']);
        $this->assertSame(40000.0, $tresorerie['par_mode_paiement']['mtn_momo']['encaissements']);
    }
}
