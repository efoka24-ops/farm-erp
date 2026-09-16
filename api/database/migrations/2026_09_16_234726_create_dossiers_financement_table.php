<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dossiers_financement', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('exploitation_id');
            $table->decimal('montant_demande', 14, 2);
            $table->unsignedSmallInteger('duree_mois');
            $table->string('objet');
            $table->decimal('taux_annuel_pourcent', 5, 2)->default(18.00)
                ->comment('Taux indicatif BPR Rwanda — à ajuster à la validation avec la banque');
            $table->decimal('mensualite', 14, 2)->nullable();
            $table->string('pdf_path')->nullable();
            $table->char('signature_sha256', 64)->nullable();
            $table->timestamp('horodatage_signature')->nullable();
            $table->string('horodatage_source')->nullable()
                ->comment("'local' (horloge serveur) ou 'rfc3161' si une TSA externe est configurée");
            $table->foreignId('genere_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('exploitation_id')->references('id')->on('exploitations')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dossiers_financement');
    }
};
