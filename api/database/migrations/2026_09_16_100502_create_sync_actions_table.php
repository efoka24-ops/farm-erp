<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_actions', function (Blueprint $table) {
            $table->uuid('id')->primary()->comment('UUID généré côté mobile pour idempotence');
            $table->uuid('exploitation_id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('entity_type');
            $table->string('entity_id');
            $table->enum('operation', ['create', 'update', 'delete']);
            $table->json('payload');
            $table->timestamp('occurred_at')->comment("Horodatage client (peut être antérieur à l'arrivée serveur)");
            $table->enum('statut', ['en_attente', 'applique', 'conflit', 'rejete'])->default('en_attente');
            $table->timestamps();

            $table->foreign('exploitation_id')->references('id')->on('exploitations')->cascadeOnDelete();
            $table->index(['exploitation_id', 'statut']);
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_actions');
    }
};
