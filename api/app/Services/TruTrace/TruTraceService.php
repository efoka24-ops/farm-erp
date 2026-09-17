<?php

namespace App\Services\TruTrace;

use App\Models\Animal;
use App\Models\Vente;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Certificat de traçabilité TRU TRACE + certificat OIE acheteur (T064).
 *
 * État : aucune credential ni URL d'API TRU TRACE externe n'a été fournie
 * (`TRU_TRACE_API_URL` absent). L'ID TRU TRACE de l'animal (`animaux.tru_trace_id`)
 * est généré et géré localement depuis la Phase 3 (US1) — c'est le socle
 * d'identification. Ce qui manque ici est la certification/publication
 * externe (tampon OIE, registre national) auprès d'un service tiers.
 *
 * En son absence, un certificat *local* est généré (référence + horodatage +
 * empreinte SHA-256 du contenu), clairement marqué comme non certifié
 * officiellement, pour ne pas induire en erreur l'acheteur.
 */
class TruTraceService
{
    public function estConfigure(): bool
    {
        return filled(config('services.tru_trace.api_url'));
    }

    public function genererCertificat(Vente $vente, Animal $animal): array
    {
        if ($this->estConfigure()) {
            // Intégration réelle à implémenter une fois l'API TRU TRACE
            // externe et ses credentials fournis (non disponible à ce jour).
            $reponse = Http::withToken(config('services.tru_trace.api_key'))
                ->post(config('services.tru_trace.api_url').'/certificats', [
                    'tru_trace_id' => $animal->tru_trace_id,
                    'vente_id' => $vente->id,
                ]);

            return $reponse->json();
        }

        $reference = 'CERT-LOCAL-'.strtoupper(Str::random(12));
        $contenu = "{$animal->tru_trace_id}|{$vente->id}|{$vente->date_vente}";

        return [
            'reference' => $reference,
            'tru_trace_id' => $animal->tru_trace_id,
            'empreinte_sha256' => hash('sha256', $contenu),
            'genere_le' => now()->toIso8601String(),
            'certifie_officiellement' => false,
            'note' => 'Certificat généré localement — intégration TRU TRACE externe non activée (aucune API/credential fournie). Non valable comme certificat OIE officiel.',
        ];
    }
}
