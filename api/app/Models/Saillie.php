<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToExploitation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Saillie extends Model
{
    use Auditable, BelongsToExploitation, HasFactory, HasUuids;

    protected $fillable = [
        'exploitation_id', 'femelle_id', 'reproducteur_id',
        'date_saillie', 'date_prevue_mise_bas', 'statut', 'saisi_par',
    ];

    protected $casts = [
        'date_saillie' => 'date',
        'date_prevue_mise_bas' => 'date',
    ];

    public function femelle(): BelongsTo
    {
        return $this->belongsTo(Animal::class, 'femelle_id');
    }

    public function reproducteur(): BelongsTo
    {
        return $this->belongsTo(Animal::class, 'reproducteur_id');
    }

    public function miseBas(): HasOne
    {
        return $this->hasOne(MiseBas::class);
    }
}
