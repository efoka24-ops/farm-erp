<?php

namespace App\Services\Financement;

use Illuminate\Support\Carbon;

/**
 * Signature numérique SHA-256 + horodatage du dossier de financement (T041).
 *
 * État : le hachage SHA-256 est réel et vérifiable (recalculable à partir du
 * PDF stocké, cf. `DossierFinancementController::verifierSignature`).
 * L'horodatage RFC 3161 (Time Stamping Authority tierce, ex: FreeTSA,
 * DigiCert) n'est PAS activé — aucune TSA n'a été configurée
 * (`RFC3161_TSA_URL` absent). En son absence, on utilise l'horloge serveur
 * comme horodatage de secours (`horodatage_source = 'local'`), moins fort
 * juridiquement qu'un vrai jeton RFC 3161 mais suffisant pour tracer la
 * génération. À activer avant un dépôt réel auprès de BPR Rwanda/FIDA.
 */
class SignatureService
{
    public function signer(string $contenuBinaire): array
    {
        $empreinte = hash('sha256', $contenuBinaire);

        $tsaUrl = config('services.rfc3161.tsa_url');

        if ($tsaUrl) {
            // Intégration RFC 3161 réelle à implémenter ici une fois une TSA
            // choisie (requête TimeStampReq/TimeStampResp binaire ASN.1).
            // Non implémenté : aucune TSA n'a été retenue à ce jour.
        }

        return [
            'signature_sha256' => $empreinte,
            'horodatage_signature' => Carbon::now(),
            'horodatage_source' => 'local',
        ];
    }

    public function verifier(string $contenuBinaire, string $empreinteAttendue): bool
    {
        return hash_equals($empreinteAttendue, hash('sha256', $contenuBinaire));
    }
}
