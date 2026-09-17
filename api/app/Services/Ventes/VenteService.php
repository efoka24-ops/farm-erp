<?php

namespace App\Services\Ventes;

use App\Models\Animal;
use App\Models\CompteOhada;
use App\Models\Recette;
use App\Models\Vente;
use App\Services\TruTrace\TruTraceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Module ventes (T062) : enregistre la vente, bloque si l'animal est sous
 * délai d'attente (réutilise la règle de T045), génère le bon PDF + le
 * certificat TRU TRACE (T063/T064), et alimente automatiquement la
 * comptabilité (recette classe 701 — cohérence avec US2).
 */
class VenteService
{
    public function __construct(private readonly TruTraceService $truTrace) {}

    public function vendre(Animal $animal, array $donnees, ?int $venduParUserId): Vente
    {
        if (! $animal->peutEtreVendu()) {
            $blocage = $animal->traitementBloquant();

            throw new RuntimeException(
                "Vente bloquée : délai d'attente du traitement '{$blocage->medicament}' jusqu'au {$blocage->date_fin_delai_attente->toDateString()}."
            );
        }

        $vente = Vente::create([
            ...$donnees,
            'exploitation_id' => $animal->exploitation_id,
            'animal_id' => $animal->id,
            'vendu_par' => $venduParUserId,
        ]);

        $animal->update(['statut' => 'vendu']);

        $certificat = $this->truTrace->genererCertificat($vente, $animal);
        $vente->update(['certificat_tru_trace_reference' => $certificat['reference']]);

        $pdf = Pdf::loadView('pdf.bon-vente', [
            'exploitation' => $animal->exploitation,
            'vente' => $vente->fresh(['client']),
            'animal' => $animal,
            'certificat' => $certificat,
        ]);

        $chemin = "bons-vente/{$vente->id}.pdf";
        Storage::put($chemin, $pdf->output());
        $vente->update(['bon_pdf_path' => $chemin]);

        $this->creerRecetteComptable($vente);

        return $vente->fresh();
    }

    private function creerRecetteComptable(Vente $vente): void
    {
        $compte = CompteOhada::where('code', '701')->first();

        if (! $compte) {
            return;
        }

        Recette::create([
            'exploitation_id' => $vente->exploitation_id,
            'compte_ohada_id' => $compte->id,
            'montant' => $vente->montantTtc(),
            'date_operation' => $vente->date_vente,
            'libelle' => "Vente animal {$vente->animal->tru_trace_id}",
            'animal_id' => $vente->animal_id,
            'mode_paiement' => $vente->mode_paiement === 'differe' ? 'especes' : $vente->mode_paiement,
        ]);
    }
}
