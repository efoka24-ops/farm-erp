<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToExploitation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributionAlimentation extends Model
{
    use Auditable, BelongsToExploitation, HasFactory, HasUuids;

    protected $table = 'distributions_alimentation';

    protected $fillable = [
        'exploitation_id', 'animal_id', 'aliment', 'quantite_kg', 'date_distribution', 'saisi_par',
    ];

    protected $casts = [
        'quantite_kg' => 'decimal:2',
        'date_distribution' => 'date',
    ];

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }
}
