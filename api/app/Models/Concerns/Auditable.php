<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;

/**
 * Journalise automatiquement create/update/delete d'un modèle dans audit_logs
 * (principe constitutionnel IV — traçabilité complète des mouvements financiers
 * et opérationnels). À combiner avec BelongsToExploitation sur les modèles
 * qui en disposent pour renseigner exploitation_id.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(fn ($model) => $model->journaliser('create', null, $model->getAttributes()));

        static::updated(function ($model) {
            $avant = array_intersect_key($model->getOriginal(), $model->getChanges());
            $apres = $model->getChanges();

            if (! empty($apres)) {
                $model->journaliser('update', $avant, $apres);
            }
        });

        static::deleted(fn ($model) => $model->journaliser('delete', $model->getAttributes(), null));
    }

    protected function journaliser(string $action, ?array $avant, ?array $apres): void
    {
        AuditLog::create([
            'exploitation_id' => $this->exploitation_id ?? null,
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => static::class,
            'auditable_id' => (string) $this->getKey(),
            'donnees_avant' => $avant,
            'donnees_apres' => $apres,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'created_at' => now(),
        ]);
    }
}
