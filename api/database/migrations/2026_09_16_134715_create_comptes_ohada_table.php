<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Plan comptable OHADA simplifié (T029) : 5 classes retenues pour une
        // petite exploitation (2 immobilisations, 3 stocks, 5 trésorerie,
        // 6 charges, 7 produits) — les classes 1 (capitaux), 4 (tiers hors
        // trésorerie) et 8 (comptes spéciaux) sont hors périmètre du MVP
        // comptable terrain, la saisie restant dépense/recette simplifiée.
        Schema::create('comptes_ohada', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('libelle');
            $table->unsignedTinyInteger('classe');
            $table->enum('nature', ['charge', 'produit', 'actif', 'tresorerie'])->default('charge');
            $table->timestamps();
        });

        DB::table('comptes_ohada')->insert([
            // Classe 6 — Charges
            ['code' => '601', 'libelle' => 'Achats aliments et fourrage', 'classe' => 6, 'nature' => 'charge', 'created_at' => now(), 'updated_at' => now()],
            ['code' => '602', 'libelle' => 'Frais vétérinaires et sanitaires', 'classe' => 6, 'nature' => 'charge', 'created_at' => now(), 'updated_at' => now()],
            ['code' => '603', 'libelle' => 'Achats animaux (acquisitions)', 'classe' => 6, 'nature' => 'charge', 'created_at' => now(), 'updated_at' => now()],
            ['code' => '621', 'libelle' => 'Transport et logistique', 'classe' => 6, 'nature' => 'charge', 'created_at' => now(), 'updated_at' => now()],
            ['code' => '641', 'libelle' => 'Charges de personnel', 'classe' => 6, 'nature' => 'charge', 'created_at' => now(), 'updated_at' => now()],
            ['code' => '658', 'libelle' => 'Charges diverses de gestion', 'classe' => 6, 'nature' => 'charge', 'created_at' => now(), 'updated_at' => now()],
            // Classe 7 — Produits
            ['code' => '701', 'libelle' => 'Ventes d\'animaux', 'classe' => 7, 'nature' => 'produit', 'created_at' => now(), 'updated_at' => now()],
            ['code' => '702', 'libelle' => 'Ventes de produits dérivés (lait, cuir, etc.)', 'classe' => 7, 'nature' => 'produit', 'created_at' => now(), 'updated_at' => now()],
            ['code' => '758', 'libelle' => 'Produits divers de gestion', 'classe' => 7, 'nature' => 'produit', 'created_at' => now(), 'updated_at' => now()],
            // Classe 2 — Immobilisations
            ['code' => '245', 'libelle' => 'Cheptel (immobilisation animale)', 'classe' => 2, 'nature' => 'actif', 'created_at' => now(), 'updated_at' => now()],
            ['code' => '218', 'libelle' => 'Matériel et équipement agricole', 'classe' => 2, 'nature' => 'actif', 'created_at' => now(), 'updated_at' => now()],
            // Classe 3 — Stocks
            ['code' => '335', 'libelle' => 'Stocks d\'aliments et intrants', 'classe' => 3, 'nature' => 'actif', 'created_at' => now(), 'updated_at' => now()],
            // Classe 5 — Trésorerie
            ['code' => '521', 'libelle' => 'Banque', 'classe' => 5, 'nature' => 'tresorerie', 'created_at' => now(), 'updated_at' => now()],
            ['code' => '571', 'libelle' => 'Caisse', 'classe' => 5, 'nature' => 'tresorerie', 'created_at' => now(), 'updated_at' => now()],
            ['code' => '572', 'libelle' => 'Mobile Money (MTN MoMo)', 'classe' => 5, 'nature' => 'tresorerie', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('comptes_ohada');
    }
};
