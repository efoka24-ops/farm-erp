<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Administration (T094) : invitation d'utilisateurs sur sa propre
 * exploitation — comble la limitation notée depuis la porte d'entrée
 * publique (le gérant inscrit était seul, sans moyen d'ajouter agent
 * terrain/comptable/etc.). Réservé au rôle gérant.
 */
class UtilisateurController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            User::where('exploitation_id', $request->user()->exploitation_id)
                ->with('role')
                ->get()
        );
    }

    public function inviter(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role_slug' => ['required', 'in:agent_terrain,comptable,veterinaire_externe,gestionnaire_cooperative'],
        ]);

        $motDePasseTemporaire = Str::random(12);

        $utilisateur = User::create([
            'exploitation_id' => $request->user()->exploitation_id,
            'role_id' => Role::where('slug', $data['role_slug'])->value('id'),
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($motDePasseTemporaire),
            'actif' => true,
        ]);

        // Pas d'envoi d'email d'invitation configuré (SMTP non renseigné en
        // prod à ce stade, cf. limitation notée en Phase 4) : le mot de passe
        // temporaire est retourné une seule fois dans la réponse, à
        // transmettre manuellement par le gérant.
        return response()->json([
            'utilisateur' => $utilisateur->load('role'),
            'mot_de_passe_temporaire' => $motDePasseTemporaire,
        ], 201);
    }

    public function desactiver(Request $request, User $utilisateur): JsonResponse
    {
        if ($utilisateur->exploitation_id !== $request->user()->exploitation_id) {
            abort(403);
        }

        if ($utilisateur->id === $request->user()->id) {
            abort(422, 'Impossible de désactiver votre propre compte.');
        }

        $utilisateur->update(['actif' => false]);

        return response()->json($utilisateur);
    }
}
