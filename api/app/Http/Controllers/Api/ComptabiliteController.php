<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\CompteOhada;
use App\Models\Depense;
use App\Models\Recette;
use App\Services\Comptabilite\CoutRevientService;
use App\Services\Comptabilite\EtatsFinanciersService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class ComptabiliteController extends Controller
{
    public function __construct(
        private readonly EtatsFinanciersService $etatsFinanciers,
        private readonly CoutRevientService $coutRevient,
    ) {}

    public function planComptable(): JsonResponse
    {
        return response()->json(CompteOhada::orderBy('code')->get());
    }

    public function compteDeResultat(Request $request): JsonResponse
    {
        [$debut, $fin] = $this->periode($request);

        return response()->json(
            $this->etatsFinanciers->compteDeResultat($request->user()->exploitation_id, $debut, $fin)
        );
    }

    public function compteDeResultatPdf(Request $request): Response
    {
        [$debut, $fin] = $this->periode($request);
        $etat = $this->etatsFinanciers->compteDeResultat($request->user()->exploitation_id, $debut, $fin);

        $pdf = Pdf::loadView('pdf.compte-resultat', [
            'etat' => $etat,
            'exploitation' => $request->user()->exploitation,
        ]);

        return $pdf->download("compte-resultat-{$debut->format('Y-m')}.pdf");
    }

    public function bilanPdf(Request $request): Response
    {
        $auDate = $request->query('au') ? Carbon::parse($request->query('au')) : now();
        $etat = $this->etatsFinanciers->bilanSimplifie($request->user()->exploitation_id, $auDate);

        $pdf = Pdf::loadView('pdf.bilan', [
            'etat' => $etat,
            'exploitation' => $request->user()->exploitation,
        ]);

        return $pdf->download("bilan-{$auDate->format('Y-m-d')}.pdf");
    }

    public function bilan(Request $request): JsonResponse
    {
        $auDate = $request->query('au') ? Carbon::parse($request->query('au')) : now();

        return response()->json(
            $this->etatsFinanciers->bilanSimplifie($request->user()->exploitation_id, $auDate)
        );
    }

    public function tresorerie(Request $request): JsonResponse
    {
        [$debut, $fin] = $this->periode($request);

        return response()->json(
            $this->etatsFinanciers->tableauTresorerie($request->user()->exploitation_id, $debut, $fin)
        );
    }

    /**
     * Export CSV des mouvements (dépenses + recettes) de la période, ouvrable
     * directement dans Excel — alternative légère à un vrai .xlsx (PhpSpreadsheet
     * ajouterait une dépendance lourde pour un besoin couvert par le CSV).
     */
    public function exportMouvementsCsv(Request $request): Response
    {
        [$debut, $fin] = $this->periode($request);
        $exploitationId = $request->user()->exploitation_id;

        $depenses = Depense::where('exploitation_id', $exploitationId)
            ->whereBetween('date_operation', [$debut, $fin])
            ->with('compteOhada')->get()
            ->map(fn ($d) => [$d->date_operation->toDateString(), 'Dépense', $d->compteOhada->code, $d->libelle, -$d->montant, $d->mode_paiement]);

        $recettes = Recette::where('exploitation_id', $exploitationId)
            ->whereBetween('date_operation', [$debut, $fin])
            ->with('compteOhada')->get()
            ->map(fn ($r) => [$r->date_operation->toDateString(), 'Recette', $r->compteOhada->code, $r->libelle, $r->montant, $r->mode_paiement]);

        $lignes = $depenses->concat($recettes)->sortBy(0);

        $csv = "Date;Type;Compte;Libelle;Montant;Mode de paiement\n";
        foreach ($lignes as $ligne) {
            $csv .= implode(';', $ligne)."\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=mouvements-{$debut->format('Y-m')}.csv",
        ]);
    }

    public function coutRevient(Animal $animal): JsonResponse
    {
        return response()->json($this->coutRevient->coutRevient($animal));
    }

    public function seuilRentabilite(Request $request, Animal $animal): JsonResponse
    {
        $marge = (float) $request->query('marge_pourcent', 0);

        return response()->json($this->coutRevient->seuilRentabilite($animal, $marge));
    }

    private function periode(Request $request): array
    {
        $debut = $request->query('debut')
            ? Carbon::parse($request->query('debut'))
            : now()->startOfMonth();

        $fin = $request->query('fin')
            ? Carbon::parse($request->query('fin'))
            : now()->endOfMonth();

        return [$debut, $fin];
    }
}
