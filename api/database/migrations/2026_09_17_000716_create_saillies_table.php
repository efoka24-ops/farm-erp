<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saillies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('exploitation_id');
            $table->uuid('femelle_id');
            $table->uuid('reproducteur_id')->nullable()->comment('Père si connu/enregistré dans le cheptel');
            $table->date('date_saillie');
            $table->date('date_prevue_mise_bas')->comment('Calculée selon la durée de gestation de l\'espèce');
            $table->enum('statut', ['en_cours', 'mise_bas', 'echec'])->default('en_cours');
            $table->foreignId('saisi_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('exploitation_id')->references('id')->on('exploitations')->cascadeOnDelete();
            $table->foreign('femelle_id')->references('id')->on('animaux')->cascadeOnDelete();
            $table->foreign('reproducteur_id')->references('id')->on('animaux')->nullOnDelete();
            $table->index(['exploitation_id', 'statut', 'date_prevue_mise_bas']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saillies');
    }
};
