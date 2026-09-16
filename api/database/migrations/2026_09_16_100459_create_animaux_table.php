<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('animaux', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('exploitation_id');
            $table->string('tru_trace_id')->unique()->comment('Identifiant QR TRU TRACE');
            $table->string('espece');
            $table->string('race')->nullable();
            $table->enum('sexe', ['male', 'femelle']);
            $table->date('date_naissance')->nullable();
            $table->enum('statut', ['actif', 'vendu', 'mort', 'reforme'])->default('actif');
            $table->uuid('mere_id')->nullable();
            $table->uuid('pere_id')->nullable();
            $table->string('photo_path')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('exploitation_id')->references('id')->on('exploitations')->cascadeOnDelete();
            $table->foreign('mere_id')->references('id')->on('animaux')->nullOnDelete();
            $table->foreign('pere_id')->references('id')->on('animaux')->nullOnDelete();
            $table->index(['exploitation_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('animaux');
    }
};
