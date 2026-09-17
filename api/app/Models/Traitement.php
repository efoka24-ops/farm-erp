<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToExploitation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Traitement extends Model
{
    use Auditable, BelongsToExploitation, HasFactory, HasUuids;

    protected $fillable = [
        'exploitation_id', 'animal_id', 'medicament', 'categorie_stock_id', 'motif',
        'date_debut', 'delai_attente_jours', 'date_fin_delai_attente', 'administre_par',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin_delai_attente' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Traitement $traitement) {
            if (! $traitement->date_fin_delai_attente) {
                $traitement->date_fin_delai_attente = $traitement->date_debut
                    ->copy()
                    ->addDays($traitement->delai_attente_jours);
            }
        });
    }

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public function estSousDelaiAttente(): bool
    {
        return $this->date_fin_delai_attente->isFuture();
    }
}
