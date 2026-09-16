<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToExploitation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vaccination extends Model
{
    use Auditable, BelongsToExploitation, HasFactory, HasUuids;

    protected $fillable = [
        'exploitation_id', 'animal_id', 'protocole_vaccinal_id', 'vaccin',
        'date_prevue', 'date_administration', 'statut', 'campagne_id', 'administre_par',
    ];

    protected $casts = [
        'date_prevue' => 'date',
        'date_administration' => 'date',
    ];

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public function protocole(): BelongsTo
    {
        return $this->belongsTo(ProtocoleVaccinal::class, 'protocole_vaccinal_id');
    }
}
