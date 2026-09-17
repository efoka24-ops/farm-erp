<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToExploitation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandeEligibiliteMinagri extends Model
{
    use Auditable, BelongsToExploitation, HasFactory, HasUuids;

    protected $table = 'demandes_eligibilite_minagri';

    protected $fillable = ['exploitation_id', 'programme', 'eligible_depuis', 'statut', 'pdf_path'];

    protected $casts = [
        'eligible_depuis' => 'datetime',
    ];
}
