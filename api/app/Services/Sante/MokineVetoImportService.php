<?php

namespace App\Services\Sante;

use App\Models\Animal;
use App\Models\ConsultationVeterinaire;
use App\Models\ImportMokinevotoEchoue;
use Illuminate\Support\Facades\Log;

/**
 * Import automatique des consultations MokineVeto dans le DMA (T047).
 *
 * État : le webhook côté serveur (`MokineVetoWebhookController`) est
 * fonctionnel et prêt à recevoir les push MokineVeto, mais aucun partenariat
 * technique (URL de callback partagée, secret HMAC) n'a encore été établi
 * avec MokineVeto — donc aucun flux réel n'arrive pour l'instant.
 *
 * Mode dégradé : toute consultation reçue mais non rattachable (animal
 * TRU TRACE inconnu localement, ou erreur transitoire) est conservée dans
 * `imports_mokinevoto_echoues` plutôt que perdue, et peut être rejouée via
 * la commande `mokinevoto:rejouer-echecs` une fois la cause résolue
 * (ex: l'animal a été créé entre-temps).
 */
class MokineVetoImportService
{
    /**
     * @param  array{tru_trace_id: string, mokinevoto_consultation_id: string, veterinaire?: string,
     *                date_consultation: string, diagnostic?: string, prescription?: string}  $payload
     */
    public function importer(array $payload): ConsultationVeterinaire
    {
        $animal = Animal::withoutGlobalScopes()
            ->where('tru_trace_id', $payload['tru_trace_id'])
            ->first();

        if (! $animal) {
            $this->mettreEnEchec($payload, "Animal TRU TRACE '{$payload['tru_trace_id']}' introuvable");

            throw new \RuntimeException("Animal introuvable — consultation mise en file d'attente.");
        }

        return ConsultationVeterinaire::updateOrCreate(
            ['mokinevoto_consultation_id' => $payload['mokinevoto_consultation_id']],
            [
                'exploitation_id' => $animal->exploitation_id,
                'animal_id' => $animal->id,
                'veterinaire' => $payload['veterinaire'] ?? null,
                'date_consultation' => $payload['date_consultation'],
                'diagnostic' => $payload['diagnostic'] ?? null,
                'prescription' => $payload['prescription'] ?? null,
                'source' => 'mokinevoto',
                'statut_import' => 'synchronise',
            ]
        );
    }

    private function mettreEnEchec(array $payload, string $raison): void
    {
        Log::warning('MokineVeto : import en échec, mis en file d\'attente', ['raison' => $raison]);

        ImportMokinevotoEchoue::create([
            'payload' => $payload,
            'raison' => $raison,
        ]);
    }

    /**
     * Rejoue les imports en échec (T047, mode dégradé). Retourne le nombre
     * résolu avec succès.
     */
    public function rejouerEchecs(): int
    {
        $resolus = 0;

        foreach (ImportMokinevotoEchoue::whereNull('resolu_at')->get() as $echec) {
            try {
                $this->importer($echec->payload);
                $echec->update(['resolu_at' => now()]);
                $resolus++;
            } catch (\RuntimeException) {
                $echec->increment('tentatives');
            }
        }

        return $resolus;
    }
}
