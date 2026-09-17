<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories_stock', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('exploitation_id');
            $table->string('nom');
            $table->enum('type', ['aliment', 'medicament', 'intrant', 'autre'])->default('aliment');
            $table->string('unite', 20)->default('kg');
            $table->decimal('seuil_alerte_quantite', 10, 2)->default(0);
            $table->string('fournisseur_nom')->nullable();
            $table->string('fournisseur_contact')->nullable();
            $table->timestamps();

            $table->foreign('exploitation_id')->references('id')->on('exploitations')->cascadeOnDelete();
            $table->unique(['exploitation_id', 'nom']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories_stock');
    }
};
