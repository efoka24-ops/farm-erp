<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandes_eligibilite_minagri', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('exploitation_id');
            $table->string('programme');
            $table->timestamp('eligible_depuis');
            $table->enum('statut', ['detectee', 'attente', 'approuvee', 'rejetee'])->default('detectee');
            $table->string('pdf_path')->nullable();
            $table->timestamps();

            $table->foreign('exploitation_id')->references('id')->on('exploitations')->cascadeOnDelete();
            $table->unique(['exploitation_id', 'programme']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandes_eligibilite_minagri');
    }
};
