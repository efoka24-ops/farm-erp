<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DossierFinancement;
use App\Services\Financement\DossierFinancementService;
use App\Services\Financement\DossierIncompletException;
use App\Services\Financement\SignatureService;
use App\Services\Financement\VerificationCompletudeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DossierFinancementController extends Controller
{
    public function verifierCompletude(Request $request, VerificationCompletudeService $service): JsonResponse
    {
        return response()->json($service->verifier($request->user()->exploitation_id));
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json(
            DossierFinancement::orderByDesc('created_at')->get()
        );
    }

    public function store(Request $request, DossierFinancementService $service): JsonResponse
    {
        $data = $request->validate([
            'montant_demande' => ['required', 'numeric', 'min:1'],
            'duree_mois' => ['required', 'integer', 'min:1', 'max:120'],
            'objet' => ['required', 'string', 'max:255'],
            'taux_annuel_pourcent' => ['sometimes', 'numeric', 'min:0', 'max:100'],
        ]);

        try {
            $dossier = $service->generer(
                $request->user()->exploitation,
                (float) $data['montant_demande'],
                $data['duree_mois'],
                $data['objet'],
                $request->user()->id,
                (float) ($data['taux_annuel_pourcent'] ?? 18.0),
            );
        } catch (DossierIncompletException $e) {
            throw ValidationException::withMessages(['completude' => $e->manques]);
        }

        return response()->json($dossier, 201);
    }

    public function telecharger(DossierFinancement $dossier): Response
    {
        return Storage::download($dossier->pdf_path, 'dossier-financement.pdf');
    }

    public function verifierSignature(DossierFinancement $dossier, SignatureService $signature): JsonResponse
    {
        $contenu = Storage::get($dossier->pdf_path);
        $valide = $contenu && $signature->verifier($contenu, $dossier->signature_sha256);

        return response()->json([
            'signature_valide' => $valide,
            'signature_sha256' => $dossier->signature_sha256,
            'horodatage_signature' => $dossier->horodatage_signature,
            'horodatage_source' => $dossier->horodatage_source,
        ]);
    }
}
