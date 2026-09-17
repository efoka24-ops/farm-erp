<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToExploitation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vente extends Model
{
    use Auditable, BelongsToExploitation, HasFactory, HasUuids;

    protected $fillable = [
        'exploitation_id', 'animal_id', 'client_id', 'client_nom_libre', 'montant',
        'tva_applicable', 'taux_tva_pourcent', 'mode_paiement', 'date_vente',
        'bon_pdf_path', 'certificat_tru_trace_reference', 'vendu_par',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'taux_tva_pourcent' => 'decimal:2',
        'tva_applicable' => 'boolean',
        'date_vente' => 'date',
    ];

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function montantTtc(): float
    {
        if (! $this->tva_applicable) {
            return (float) $this->montant;
        }

        return round((float) $this->montant * (1 + (float) $this->taux_tva_pourcent / 100), 2);
    }
}
