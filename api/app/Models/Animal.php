<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToExploitation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Animal extends Model
{
    use Auditable, BelongsToExploitation, HasFactory, HasUuids, SoftDeletes;

    protected $table = 'animaux';

    protected $fillable = [
        'exploitation_id', 'tru_trace_id', 'espece', 'race', 'sexe',
        'date_naissance', 'statut', 'mere_id', 'pere_id', 'photo_path', 'description',
    ];

    protected $casts = [
        'date_naissance' => 'date',
    ];

    public function mere(): BelongsTo
    {
        return $this->belongsTo(Animal::class, 'mere_id');
    }

    public function pere(): BelongsTo
    {
        return $this->belongsTo(Animal::class, 'pere_id');
    }
}
