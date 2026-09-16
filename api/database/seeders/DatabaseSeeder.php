<?php

namespace Database\Seeders;

use App\Models\Exploitation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Jeu de données minimal pour le développement local : une exploitation
     * et un gérant, nécessaires pour tester l'auth et la RLS applicative.
     */
    public function run(): void
    {
        $exploitation = Exploitation::create([
            'nom' => 'Exploitation Démo TRU FARM',
            'type' => 'individuelle',
            'region' => 'Kigali',
        ]);

        $roleGerant = Role::where('slug', Role::GERANT)->first();

        User::factory()->create([
            'name' => 'Gérant Démo',
            'email' => 'gerant@trufarm.test',
            'password' => bcrypt('password'),
            'exploitation_id' => $exploitation->id,
            'role_id' => $roleGerant->id,
            'two_factor_enabled' => false,
        ]);
    }
}
