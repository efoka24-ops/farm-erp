<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BonCommande;
use App\Models\CategorieStock;
use App\Services\Stock\StockService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Bon de commande PDF auto-généré fournisseur (T060), déclenché manuellement
 * ou à partir d'une alerte de seuil (`StockController::alertes`).
 */
class BonCommandeController extends Controller
{
    public function store(Request $request, CategorieStock $categorie, StockService $stock): JsonResponse
    {
        $data = $request->validate([
            'quantite_commandee' => ['required', 'numeric', 'min:0.01'],
        ]);

        $bonCommande = BonCommande::create([
            'exploitation_id' => $categorie->exploitation_id,
            'categorie_stock_id' => $categorie->id,
            'quantite_commandee' => $data['quantite_commandee'],
            'genere_par' => $request->user()->id,
        ]);

        $pdf = Pdf::loadView('pdf.bon-commande', [
            'exploitation' => $request->user()->exploitation,
            'categorie' => $categorie,
            'bonCommande' => $bonCommande,
            'niveauActuel' => $stock->niveauActuel($categorie),
            'genereLe' => now(),
        ]);

        $chemin = "bons-commande/{$bonCommande->id}.pdf";
        Storage::put($chemin, $pdf->output());
        $bonCommande->update(['pdf_path' => $chemin]);

        return response()->json($bonCommande, 201);
    }

    public function telecharger(BonCommande $bonCommande): StreamedResponse
    {
        return Storage::download($bonCommande->pdf_path, 'bon-commande.pdf');
    }
}
