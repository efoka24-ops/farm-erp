<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // DMA (Document Médical d'Administration) : trace tout traitement et
        // son délai d'attente (withdrawal period), condition du blocage de
        // vente tant que ce délai n'est pas écoulé (T045).
        Schema::create('traitements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('exploitation_id');
            $table->uuid('animal_id');
            $table->string('medicament');
            $table->text('motif')->nullable();
            $table->date('date_debut');
            $table->unsignedSmallInteger('delai_attente_jours')->default(0);
            $table->date('date_fin_delai_attente');
            $table->foreignId('administre_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('exploitation_id')->references('id')->on('exploitations')->cascadeOnDelete();
            $table->foreign('animal_id')->references('id')->on('animaux')->cascadeOnDelete();
            $table->index(['animal_id', 'date_fin_delai_attente']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traitements');
    }
};
