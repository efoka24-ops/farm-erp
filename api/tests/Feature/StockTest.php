<?php

namespace Tests\Feature;

use App\Models\CategorieStock;
use App\Services\Stock\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\Concerns\CreeUnGerantAuthentifie;
use Tests\TestCase;

class StockTest extends TestCase
{
    use CreeUnGerantAuthentifie, RefreshDatabase;

    public function test_sortie_consomme_le_lot_le_plus_ancien_dabord_fifo(): void
    {
        $gerant = $this->gerantAuthentifie();
        $categorie = CategorieStock::create([
            'exploitation_id' => $gerant->exploitation_id, 'nom' => 'Fourrage', 'type' => 'aliment',
            'seuil_alerte_quantite' => 10,
        ]);
        $stock = app(StockService::class);

        $stock->entrer($categorie, 50, 100, now()->subDays(10)->toDateString());
        $stock->entrer($categorie, 50, 150, now()->subDays(2)->toDateString());

        $resultat = $stock->sortir($categorie, 60);

        // 50 du premier lot (cout 100) + 10 du second (cout 150) = 5000 + 1500
        $this->assertSame(6500.0, $resultat['cout_total']);
        $this->assertSame(40.0, $stock->niveauActuel($categorie));
    }

    public function test_sortie_insuffisante_leve_une_exception(): void
    {
        $gerant = $this->gerantAuthentifie();
        $categorie = CategorieStock::create([
            'exploitation_id' => $gerant->exploitation_id, 'nom' => 'Vaccin X', 'type' => 'medicament',
            'seuil_alerte_quantite' => 5,
        ]);
        $stock = app(StockService::class);
        $stock->entrer($categorie, 10, 500, now()->toDateString());

        $this->expectException(RuntimeException::class);
        $stock->sortir($categorie, 20);
    }

    /** T056 : alerte seuil de commande. */
    public function test_alerte_seuil_de_commande(): void
    {
        $gerant = $this->gerantAuthentifie();
        $categorie = CategorieStock::create([
            'exploitation_id' => $gerant->exploitation_id, 'nom' => 'Aliment Y', 'type' => 'aliment',
            'seuil_alerte_quantite' => 20,
        ]);
        app(StockService::class)->entrer($categorie, 15, 100, now()->toDateString());

        $alertes = $this->getJson('/api/stock/alertes')->assertOk()->json();

        $this->assertCount(1, $alertes['sous_seuil']);
        $this->assertSame('Aliment Y', $alertes['sous_seuil'][0]['nom']);
    }

    /** T057 : alerte péremption médicament < 30 jours. */
    public function test_alerte_peremption_sous_30_jours(): void
    {
        $gerant = $this->gerantAuthentifie();
        $categorie = CategorieStock::create([
            'exploitation_id' => $gerant->exploitation_id, 'nom' => 'Antibiotique Z', 'type' => 'medicament',
            'seuil_alerte_quantite' => 1,
        ]);
        $stockService = app(StockService::class);
        $stockService->entrer($categorie, 10, 200, now()->toDateString(), now()->addDays(20));
        $stockService->entrer($categorie, 10, 200, now()->toDateString(), now()->addDays(90));

        $alertes = $this->getJson('/api/stock/alertes')->assertOk()->json();

        $this->assertCount(1, $alertes['peremption_proche']);
    }

    public function test_distribution_alimentation_deduit_le_stock_automatiquement(): void
    {
        $gerant = $this->gerantAuthentifie();
        $categorie = CategorieStock::create([
            'exploitation_id' => $gerant->exploitation_id, 'nom' => 'Fourrage', 'type' => 'aliment',
            'seuil_alerte_quantite' => 5,
        ]);
        app(StockService::class)->entrer($categorie, 100, 50, now()->toDateString());

        $this->postJson('/api/alimentation', [
            'categorie_stock_id' => $categorie->id,
            'aliment' => 'Fourrage',
            'quantite_kg' => 30,
            'date_distribution' => now()->toDateString(),
        ])->assertCreated();

        $this->assertSame(70.0, app(StockService::class)->niveauActuel($categorie->fresh()));
    }

    public function test_bon_de_commande_pdf_genere_et_telechargeable(): void
    {
        Storage::fake();
        $gerant = $this->gerantAuthentifie();
        $categorie = CategorieStock::create([
            'exploitation_id' => $gerant->exploitation_id, 'nom' => 'Fourrage', 'type' => 'aliment',
            'seuil_alerte_quantite' => 10, 'fournisseur_nom' => 'Coopérative Locale',
        ]);

        $bon = $this->postJson("/api/stock/categories/{$categorie->id}/bons-commande", [
            'quantite_commandee' => 200,
        ])->assertCreated()->json();

        $this->get("/api/stock/bons-commande/{$bon['id']}/telecharger")->assertOk();
    }
}
