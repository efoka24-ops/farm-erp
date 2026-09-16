<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Import automatique des consultations MokineVeto (T047). `source`
        // distingue une saisie locale ('manuelle') d'un import externe
        // ('mokinevoto') ; `statut_import` trace le mode dégradé (file
        // d'attente si l'API MokineVeto est indisponible au moment de l'appel).
        Schema::create('consultations_veterinaires', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('exploitation_id');
            $table->uuid('animal_id');
            $table->string('mokinevoto_consultation_id')->nullable()->unique();
            $table->string('veterinaire')->nullable();
            $table->date('date_consultation');
            $table->text('diagnostic')->nullable();
            $table->text('prescription')->nullable();
            $table->enum('source', ['manuelle', 'mokinevoto'])->default('manuelle');
            $table->enum('statut_import', ['synchronise', 'en_attente', 'echec'])->default('synchronise');
            $table->timestamps();

            $table->foreign('exploitation_id')->references('id')->on('exploitations')->cascadeOnDelete();
            $table->foreign('animal_id')->references('id')->on('animaux')->cascadeOnDelete();
            $table->index(['exploitation_id', 'date_consultation']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultations_veterinaires');
    }
};
