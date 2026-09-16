<?php

namespace App\Models;

use App\Models\Concerns\BelongsToExploitation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SyncAction extends Model
{
    use BelongsToExploitation, HasUuids;

    protected $fillable = [
        'id', 'exploitation_id', 'user_id', 'entity_type', 'entity_id',
        'operation', 'payload', 'occurred_at', 'statut',
    ];

    protected $casts = [
        'payload' => 'array',
        'occurred_at' => 'datetime',
    ];

    public $incrementing = false;

    protected $keyType = 'string';
}
