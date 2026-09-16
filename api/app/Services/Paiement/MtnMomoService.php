<?php

namespace App\Services\Paiement;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Intégration paiement fournisseurs MTN MoMo Rwanda (T033).
 *
 * État : structure fonctionnelle contre l'API Collections/Disbursements MTN
 * MoMo (sandbox proxy.momoapi.com), mais NON activée — aucune credential
 * MTN (subscription key, API user/key) n'a été fournie. Tant que
 * MTN_MOMO_* n'est pas renseigné dans .env, `initierPaiement()` lève une
 * exception explicite plutôt que d'échouer silencieusement en prod.
 *
 * Pour activer : créer un compte développeur MTN MoMo Rwanda
 * (https://momodeveloper.mtn.com), provisionner l'environnement
 * Disbursements (paiement fournisseurs), et renseigner :
 * MTN_MOMO_BASE_URL, MTN_MOMO_SUBSCRIPTION_KEY, MTN_MOMO_API_USER,
 * MTN_MOMO_API_KEY, MTN_MOMO_TARGET_ENVIRONMENT.
 */
class MtnMomoService
{
    private readonly ?string $baseUrl;

    private readonly ?string $subscriptionKey;

    private readonly ?string $apiUser;

    private readonly ?string $apiKey;

    public function __construct(
        ?string $baseUrl = null,
        ?string $subscriptionKey = null,
        ?string $apiUser = null,
        ?string $apiKey = null,
        private readonly string $targetEnvironment = 'sandbox',
    ) {
        $this->baseUrl = $baseUrl ?? config('services.mtn_momo.base_url');
        $this->subscriptionKey = $subscriptionKey ?? config('services.mtn_momo.subscription_key');
        $this->apiUser = $apiUser ?? config('services.mtn_momo.api_user');
        $this->apiKey = $apiKey ?? config('services.mtn_momo.api_key');
    }

    public function estConfigure(): bool
    {
        return filled($this->baseUrl) && filled($this->subscriptionKey)
            && filled($this->apiUser) && filled($this->apiKey);
    }

    /**
     * Déclenche un paiement fournisseur (Disbursements/transfer). Retourne
     * la référence de transaction MTN à enregistrer dans
     * `depenses.reference_paiement`.
     */
    public function initierPaiement(string $numeroDestinataire, float $montant, string $motif): string
    {
        if (! $this->estConfigure()) {
            throw new RuntimeException(
                'MTN MoMo non configuré (MTN_MOMO_* manquant dans .env) — paiement non envoyé.'
            );
        }

        $referenceId = (string) Str::uuid();

        $reponse = Http::withToken($this->obtenirJeton())
            ->withHeaders([
                'X-Reference-Id' => $referenceId,
                'X-Target-Environment' => $this->targetEnvironment,
                'Ocp-Apim-Subscription-Key' => $this->subscriptionKey,
            ])
            ->post("{$this->baseUrl}/disbursement/v1_0/transfer", [
                'amount' => number_format($montant, 0, '', ''),
                'currency' => 'RWF',
                'externalId' => $referenceId,
                'payee' => ['partyIdType' => 'MSISDN', 'partyId' => $numeroDestinataire],
                'payerMessage' => $motif,
                'payeeNote' => $motif,
            ]);

        if ($reponse->failed()) {
            throw new RuntimeException("Échec paiement MTN MoMo : {$reponse->status()} {$reponse->body()}");
        }

        return $referenceId;
    }

    private function obtenirJeton(): string
    {
        $reponse = Http::withBasicAuth($this->apiUser, $this->apiKey)
            ->withHeaders(['Ocp-Apim-Subscription-Key' => $this->subscriptionKey])
            ->post("{$this->baseUrl}/disbursement/token/");

        if ($reponse->failed()) {
            throw new RuntimeException('Échec authentification MTN MoMo.');
        }

        return $reponse->json('access_token');
    }
}
