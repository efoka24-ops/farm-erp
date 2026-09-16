<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToExploitation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultationVeterinaire extends Model
{
    use Auditable, BelongsToExploitation, HasFactory, HasUuids;

    protected $table = 'consultations_veterinaires';

    protected $fillable = [
        'exploitation_id', 'animal_id', 'mokinevoto_consultation_id', 'veterinaire',
        'date_consultation', 'diagnostic', 'prescription', 'source', 'statut_import',
    ];

    protected $casts = [
        'date_consultation' => 'date',
    ];

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }
}
