<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mode dégradé (T047) : une consultation MokineVeto reçue par webhook
        // mais non rattachable immédiatement (animal TRU TRACE inconnu, ou
        // panne DB transitoire) est conservée ici plutôt que perdue, pour
        // rejeu via `mokinevoto:rejouer-echecs`.
        Schema::create('imports_mokinevoto_echoues', function (Blueprint $table) {
            $table->id();
            $table->json('payload');
            $table->string('raison');
            $table->unsignedTinyInteger('tentatives')->default(1);
            $table->timestamp('resolu_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imports_mokinevoto_echoues');
    }
};
