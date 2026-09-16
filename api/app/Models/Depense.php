<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToExploitation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Depense extends Model
{
    use Auditable, BelongsToExploitation, HasFactory, HasUuids;

    protected $fillable = [
        'exploitation_id', 'compte_ohada_id', 'montant', 'date_operation',
        'libelle', 'animal_id', 'mode_paiement', 'reference_paiement', 'saisi_par',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_operation' => 'date',
    ];

    public function compteOhada(): BelongsTo
    {
        return $this->belongsTo(CompteOhada::class);
    }

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }
}
