<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bons_commande', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('exploitation_id');
            $table->uuid('categorie_stock_id');
            $table->decimal('quantite_commandee', 10, 2);
            $table->string('pdf_path')->nullable();
            $table->enum('statut', ['genere', 'envoye', 'receptionne'])->default('genere');
            $table->foreignId('genere_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('exploitation_id')->references('id')->on('exploitations')->cascadeOnDelete();
            $table->foreign('categorie_stock_id')->references('id')->on('categories_stock')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bons_commande');
    }
};
