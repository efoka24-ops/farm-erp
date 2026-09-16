<?php

namespace App\Services\Financement;

use App\Models\Animal;
use App\Models\DossierFinancement;
use App\Models\Exploitation;
use App\Models\Incident;
use App\Models\Traitement;
use App\Models\Vaccination;
use App\Services\Comptabilite\EtatsFinanciersService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Compilation et génération du dossier de financement (T039/T040) : rejette
 * avec la liste précise des manques (T038) si les données ne sont pas
 * complètes, sinon compile PDF multi-sections (comptes OHADA 3 ans, registre
 * cheptel TRU TRACE, bilan sanitaire, plan d'investissement, attestation
 * TRU GROUP), signe (T041) et calcule le plan de remboursement (T043).
 */
class DossierFinancementService
{
    public function __construct(
        private readonly VerificationCompletudeService $completude,
        private readonly EtatsFinanciersService $etatsFinanciers,
        private readonly PlanRemboursementService $planRemboursement,
        private readonly SignatureService $signature,
    ) {}

    public function generer(
        Exploitation $exploitation,
        float $montantDemande,
        int $dureeMois,
        string $objet,
        ?int $genereParUserId,
        float $tauxAnnuelPourcent = 18.0,
    ): DossierFinancement {
        $verification = $this->completude->verifier($exploitation->id);

        if (! $verification['complet']) {
            throw new DossierIncompletException($verification['manques']);
        }

        $comptesTroisAns = collect([2, 1, 0])->map(function (int $offset) use ($exploitation) {
            $annee = now()->subYears($offset)->year;

            return [
                'annee' => $annee,
                ...$this->etatsFinanciers->compteDeResultat(
                    $exploitation->id,
                    Carbon::create($annee, 1, 1),
                    Carbon::create($annee, 12, 31),
                ),
            ];
        });

        $animaux = Animal::where('exploitation_id', $exploitation->id)->get();

        $bilanSanitaire = [
            'vaccinations_administrees' => Vaccination::where('exploitation_id', $exploitation->id)
                ->where('statut', 'administre')->count(),
            'traitements_en_cours' => Traitement::where('exploitation_id', $exploitation->id)
                ->where('date_fin_delai_attente', '>=', now()->toDateString())->count(),
            'incidents_critiques_ouverts' => Incident::where('exploitation_id', $exploitation->id)
                ->where('statut', '!=', 'resolu')->where('gravite', 'critique')->count(),
        ];

        $mensualite = $this->planRemboursement->mensualite($montantDemande, $dureeMois, $tauxAnnuelPourcent);
        $echeancier = $this->planRemboursement->echeancier($montantDemande, $dureeMois, $tauxAnnuelPourcent);

        $pdf = Pdf::loadView('pdf.dossier-financement', [
            'exploitation' => $exploitation,
            'comptesTroisAns' => $comptesTroisAns,
            'animaux' => $animaux,
            'bilanSanitaire' => $bilanSanitaire,
            'montantDemande' => $montantDemande,
            'dureeMois' => $dureeMois,
            'objet' => $objet,
            'tauxAnnuelPourcent' => $tauxAnnuelPourcent,
            'mensualite' => $mensualite,
            'echeancier' => $echeancier,
            'genereLe' => now(),
        ]);

        $contenuPdf = $pdf->output();
        $signature = $this->signature->signer($contenuPdf);

        $nomFichier = 'dossiers-financement/'.$exploitation->id.'-'.now()->format('YmdHis').'.pdf';
        Storage::put($nomFichier, $contenuPdf);

        return DossierFinancement::create([
            'exploitation_id' => $exploitation->id,
            'montant_demande' => $montantDemande,
            'duree_mois' => $dureeMois,
            'objet' => $objet,
            'taux_annuel_pourcent' => $tauxAnnuelPourcent,
            'mensualite' => $mensualite,
            'pdf_path' => $nomFichier,
            'signature_sha256' => $signature['signature_sha256'],
            'horodatage_signature' => $signature['horodatage_signature'],
            'horodatage_source' => $signature['horodatage_source'],
            'genere_par' => $genereParUserId,
        ]);
    }
}
