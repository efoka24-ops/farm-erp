<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Exploitation;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Porte d'entrée publique de l'ERP : création de compte pour sa ferme
 * (production) ou démo restreinte de 5 jours, sans intervention d'un
 * administrateur. Le compte créé est un gérant, seul rôle habilité à
 * inviter les autres utilisateurs de son exploitation (via l'écran
 * d'administration, hors périmètre de cette porte d'entrée).
 */
class RegisterController extends Controller
{
    public function inscrire(Request $request): JsonResponse
    {
        $data = $this->validerDonnees($request);

        $resultat = DB::transaction(function () use ($data) {
            $exploitation = Exploitation::create([
                'nom' => $data['nom_exploitation'],
                'type' => 'individuelle',
                'mode' => 'production',
            ]);

            $user = $this->creerGerant($exploitation, $data);

            return [$exploitation, $user];
        });

        [, $user] = $resultat;

        return response()->json([
            'token' => $user->createToken('web')->plainTextToken,
            'user' => $user->load('role', 'exploitation'),
        ], 201);
    }

    public function creerDemo(Request $request): JsonResponse
    {
        $data = $this->validerDonnees($request);

        $resultat = DB::transaction(function () use ($data) {
            $exploitation = Exploitation::create([
                'nom' => $data['nom_exploitation'],
                'type' => 'individuelle',
                'mode' => 'demo',
                'essai_expire_le' => now()->addDays(5),
            ]);

            $user = $this->creerGerant($exploitation, $data, twoFactor: false);

            app(DemoDataSeeder::class)->peupler($exploitation);

            return [$exploitation, $user];
        });

        [$exploitation, $user] = $resultat;

        return response()->json([
            'token' => $user->createToken('demo')->plainTextToken,
            'user' => $user->load('role', 'exploitation'),
            'essai_expire_le' => $exploitation->essai_expire_le,
        ], 201);
    }

    private function validerDonnees(Request $request): array
    {
        return $request->validate([
            'nom_exploitation' => ['required', 'string', 'max:150'],
            'nom' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);
    }

    private function creerGerant(Exploitation $exploitation, array $data, bool $twoFactor = true): User
    {
        return User::create([
            'exploitation_id' => $exploitation->id,
            'role_id' => Role::where('slug', Role::GERANT)->value('id'),
            'name' => $data['nom'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'two_factor_enabled' => $twoFactor,
            'actif' => true,
        ]);
    }
}
