<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToExploitation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DossierFinancement extends Model
{
    use Auditable, BelongsToExploitation, HasFactory, HasUuids;

    protected $table = 'dossiers_financement';

    protected $fillable = [
        'exploitation_id', 'montant_demande', 'duree_mois', 'objet', 'taux_annuel_pourcent',
        'mensualite', 'pdf_path', 'signature_sha256', 'horodatage_signature',
        'horodatage_source', 'genere_par',
    ];

    protected $casts = [
        'montant_demande' => 'decimal:2',
        'taux_annuel_pourcent' => 'decimal:2',
        'mensualite' => 'decimal:2',
        'horodatage_signature' => 'datetime',
    ];

    public function genere(): BelongsTo
    {
        return $this->belongsTo(User::class, 'genere_par');
    }
}
