<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\DemandeEligibiliteMinagri;
use App\Services\Minagri\MinagriEligibiliteService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EligibiliteMinagriController extends Controller
{
    public function evaluer(Request $request, MinagriEligibiliteService $moteur): JsonResponse
    {
        return response()->json([
            'programmes' => $moteur->evaluerProgrammes($request->user()->exploitation),
        ]);
    }

    public function demandes(Request $request): JsonResponse
    {
        return response()->json(
            DemandeEligibiliteMinagri::orderByDesc('eligible_depuis')->get()
        );
    }

    /** T091 : suivi statut demande. */
    public function mettreAJourStatut(Request $request, DemandeEligibiliteMinagri $demande): JsonResponse
    {
        $data = $request->validate([
            'statut' => ['required', 'in:detectee,attente,approuvee,rejetee'],
        ]);

        $demande->update($data);

        return response()->json($demande);
    }

    /** T090 : formulaire PDF pré-rempli + pièces justificatives TRU TRACE. */
    public function genererFormulaire(DemandeEligibiliteMinagri $demande): StreamedResponse
    {
        $exploitation = $demande->exploitation;
        $evaluation = collect((new MinagriEligibiliteService)->evaluerProgrammes($exploitation))
            ->firstWhere('programme', $demande->programme);

        $pdf = Pdf::loadView('pdf.formulaire-minagri', [
            'exploitation' => $exploitation,
            'demande' => $demande,
            'libelleProgramme' => $evaluation['libelle'] ?? $demande->programme,
            'criteres' => $evaluation['criteres'] ?? '',
            'animaux' => Animal::where('exploitation_id', $exploitation->id)->where('statut', 'actif')->get(),
        ]);

        $chemin = "formulaires-minagri/{$demande->id}.pdf";
        Storage::put($chemin, $pdf->output());
        $demande->update(['pdf_path' => $chemin, 'statut' => $demande->statut === 'detectee' ? 'attente' : $demande->statut]);

        return Storage::download($chemin, 'formulaire-minagri.pdf');
    }
}
