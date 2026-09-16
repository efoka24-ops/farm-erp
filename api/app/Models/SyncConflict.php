<?php

namespace App\Models;

use App\Models\Concerns\BelongsToExploitation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncConflict extends Model
{
    use BelongsToExploitation;

    protected $fillable = [
        'sync_action_id', 'exploitation_id', 'entity_type', 'entity_id',
        'est_financier', 'donnees_serveur', 'donnees_client', 'statut',
        'resolu_par', 'resolu_at',
    ];

    protected $casts = [
        'donnees_serveur' => 'array',
        'donnees_client' => 'array',
        'est_financier' => 'boolean',
        'resolu_at' => 'datetime',
    ];

    public function syncAction(): BelongsTo
    {
        return $this->belongsTo(SyncAction::class);
    }

    public function resolveur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolu_par');
    }
}
