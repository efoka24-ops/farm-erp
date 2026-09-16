<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Déduction de stock réelle (jointure avec le module stocks, US9) : non
        // disponible avant la Phase 8. En attendant, `quantite_kg` est simplement
        // enregistrée pour permettre le calcul de coût alimentaire une fois US9 livré.
        Schema::create('distributions_alimentation', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('exploitation_id');
            $table->uuid('animal_id')->nullable()->comment('Null = distribution à un lot/groupe');
            $table->string('aliment');
            $table->decimal('quantite_kg', 8, 2);
            $table->date('date_distribution');
            $table->foreignId('saisi_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('exploitation_id')->references('id')->on('exploitations')->cascadeOnDelete();
            $table->foreign('animal_id')->references('id')->on('animaux')->nullOnDelete();
            $table->index(['exploitation_id', 'date_distribution']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distributions_alimentation');
    }
};
