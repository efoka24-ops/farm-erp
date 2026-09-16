<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recettes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('exploitation_id');
            $table->foreignId('compte_ohada_id')->constrained('comptes_ohada');
            $table->decimal('montant', 12, 2);
            $table->date('date_operation');
            $table->string('libelle');
            $table->uuid('animal_id')->nullable()->comment('Lien vente/coût de revient (T032)');
            $table->enum('mode_paiement', ['especes', 'banque', 'mtn_momo'])->default('especes');
            $table->string('reference_paiement')->nullable();
            $table->foreignId('saisi_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('exploitation_id')->references('id')->on('exploitations')->cascadeOnDelete();
            $table->foreign('animal_id')->references('id')->on('animaux')->nullOnDelete();
            $table->index(['exploitation_id', 'date_operation']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recettes');
    }
};
