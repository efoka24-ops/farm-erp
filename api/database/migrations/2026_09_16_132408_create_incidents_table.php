<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('exploitation_id');
            $table->uuid('animal_id');
            $table->enum('type', ['maladie', 'blessure', 'mortalite', 'comportement', 'autre'])->default('maladie');
            $table->enum('gravite', ['faible', 'moyenne', 'critique'])->default('moyenne');
            $table->text('description')->nullable();
            $table->string('photo_path')->nullable();
            $table->enum('statut', ['ouvert', 'en_traitement', 'resolu'])->default('ouvert');
            $table->foreignId('signale_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('exploitation_id')->references('id')->on('exploitations')->cascadeOnDelete();
            $table->foreign('animal_id')->references('id')->on('animaux')->cascadeOnDelete();
            $table->index(['exploitation_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
