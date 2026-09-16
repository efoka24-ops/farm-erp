<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vaccinations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('exploitation_id');
            $table->uuid('animal_id');
            $table->foreignId('protocole_vaccinal_id')->nullable()->constrained('protocoles_vaccinaux')->nullOnDelete();
            $table->string('vaccin');
            $table->date('date_prevue')->nullable()->comment('Calculée depuis le protocole, T048');
            $table->date('date_administration')->nullable();
            $table->enum('statut', ['planifie', 'administre', 'manque'])->default('planifie');
            $table->uuid('campagne_id')->nullable()->comment('Regroupement campagne groupée, T050');
            $table->foreignId('administre_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('exploitation_id')->references('id')->on('exploitations')->cascadeOnDelete();
            $table->foreign('animal_id')->references('id')->on('animaux')->cascadeOnDelete();
            $table->index(['exploitation_id', 'statut', 'date_prevue']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vaccinations');
    }
};
