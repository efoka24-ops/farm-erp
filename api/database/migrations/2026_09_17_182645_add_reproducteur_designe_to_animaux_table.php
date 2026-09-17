<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('animaux', function (Blueprint $table) {
            // Exclusion du planificateur de ventes (T066) : un reproducteur
            // désigné ne doit jamais être recommandé à la vente, quel que
            // soit son score économique.
            $table->boolean('reproducteur_designe')->default(false)->after('statut');
        });
    }

    public function down(): void
    {
        Schema::table('animaux', function (Blueprint $table) {
            $table->dropColumn('reproducteur_designe');
        });
    }
};
