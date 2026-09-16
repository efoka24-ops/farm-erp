<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use App\Notifications\CodeOtpNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Authentification web : email/mot de passe + OTP 2FA (T010).
 * L'authentification mobile hors-ligne par PIN 4 chiffres vit côté client
 * (SQLite/Drift, bcrypt local) ; les endpoints PIN ici ne servent qu'à
 * définir/vérifier le PIN en ligne au provisioning d'un appareil.
 */
class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Identifiants invalides.'],
            ]);
        }

        if (! $user->actif) {
            throw ValidationException::withMessages([
                'email' => ['Compte désactivé.'],
            ]);
        }

        if (! $user->two_factor_enabled) {
            return response()->json([
                'token' => $user->createToken('web')->plainTextToken,
                'user' => $user,
            ]);
        }

        [, $code] = OtpCode::generatePour($user);
        $user->notify(new CodeOtpNotification($code));

        return response()->json([
            'otp_required' => true,
            'user_id' => $user->id,
        ]);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = User::findOrFail($data['user_id']);

        $otp = OtpCode::where('user_id', $user->id)
            ->whereNull('consomme_at')
            ->latest('id')
            ->first();

        if (! $otp) {
            throw ValidationException::withMessages(['code' => ['Aucun code actif, redemandez-en un.']]);
        }

        if (! $otp->estValide($data['code'])) {
            $otp->increment('tentatives');

            throw ValidationException::withMessages(['code' => ['Code invalide ou expiré.']]);
        }

        $otp->update(['consomme_at' => now()]);

        return response()->json([
            'token' => $user->createToken('web')->plainTextToken,
            'user' => $user,
        ]);
    }

    public function definirPin(Request $request): JsonResponse
    {
        $data = $request->validate([
            'pin' => ['required', 'digits:4'],
        ]);

        $request->user()->definirPin($data['pin']);

        return response()->json(['message' => 'PIN défini.']);
    }

    public function loginPin(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'pin' => ['required', 'digits:4'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! $user->pin_hash) {
            throw ValidationException::withMessages(['pin' => ['PIN non configuré.']]);
        }

        if ($user->pin_locked_until && $user->pin_locked_until->isFuture()) {
            throw ValidationException::withMessages(['pin' => ['Compte temporairement verrouillé.']]);
        }

        if (! $user->verifierPin($data['pin'])) {
            $user->increment('pin_attempts');

            if ($user->pin_attempts >= 5) {
                $user->update(['pin_locked_until' => now()->addMinutes(15), 'pin_attempts' => 0]);
            }

            throw ValidationException::withMessages(['pin' => ['PIN invalide.']]);
        }

        $user->update(['pin_attempts' => 0]);

        return response()->json([
            'token' => $user->createToken('mobile')->plainTextToken,
            'user' => $user,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user()->load('role', 'exploitation'));
    }
}
