<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Journal append-only (principe constitutionnel IV) : pas de $fillable pour update,
 * pas de route/controller n'expose de PUT/PATCH/DELETE sur ce modèle.
 */
class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'exploitation_id', 'user_id', 'action', 'auditable_type', 'auditable_id',
        'donnees_avant', 'donnees_apres', 'ip_address', 'user_agent', 'created_at',
    ];

    protected $casts = [
        'donnees_avant' => 'array',
        'donnees_apres' => 'array',
        'created_at' => 'datetime',
    ];
}
