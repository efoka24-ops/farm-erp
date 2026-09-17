<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('exploitation_id');
            $table->uuid('animal_id');
            $table->uuid('client_id')->nullable();
            $table->string('client_nom_libre')->nullable()->comment('Vente ponctuelle sans fiche client');
            $table->decimal('montant', 12, 2);
            $table->boolean('tva_applicable')->default(false);
            $table->decimal('taux_tva_pourcent', 5, 2)->default(18.00);
            $table->enum('mode_paiement', ['especes', 'banque', 'mtn_momo', 'differe'])->default('especes');
            $table->date('date_vente');
            $table->string('bon_pdf_path')->nullable();
            $table->string('certificat_tru_trace_reference')->nullable();
            $table->foreignId('vendu_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('exploitation_id')->references('id')->on('exploitations')->cascadeOnDelete();
            $table->foreign('animal_id')->references('id')->on('animaux')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
            $table->index(['exploitation_id', 'date_vente']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventes');
    }
};
