<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_conflicts', function (Blueprint $table) {
            $table->id();
            $table->uuid('sync_action_id');
            $table->uuid('exploitation_id');
            $table->string('entity_type');
            $table->string('entity_id');
            $table->boolean('est_financier')->default(false)
                ->comment('Conflits financiers = arbitrage manuel obligatoire (principe constitutionnel)');
            $table->json('donnees_serveur');
            $table->json('donnees_client');
            $table->enum('statut', ['ouvert', 'resolu_serveur', 'resolu_client', 'resolu_fusion'])->default('ouvert');
            $table->foreignId('resolu_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolu_at')->nullable();
            $table->timestamps();

            $table->foreign('sync_action_id')->references('id')->on('sync_actions')->cascadeOnDelete();
            $table->foreign('exploitation_id')->references('id')->on('exploitations')->cascadeOnDelete();
            $table->index(['exploitation_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_conflicts');
    }
};
