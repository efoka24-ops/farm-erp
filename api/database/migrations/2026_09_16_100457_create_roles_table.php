<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('libelle');
            $table->timestamps();
        });

        // Rôles définis par la constitution du projet (spec.md §RBAC)
        DB::table('roles')->insert([
            ['slug' => 'gerant', 'libelle' => 'Gérant', 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'agent_terrain', 'libelle' => 'Agent terrain', 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'comptable', 'libelle' => 'Comptable', 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'gestionnaire_cooperative', 'libelle' => 'Gestionnaire coopérative', 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'veterinaire_externe', 'libelle' => 'Vétérinaire externe', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
