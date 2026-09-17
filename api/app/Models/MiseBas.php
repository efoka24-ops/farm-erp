<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToExploitation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MiseBas extends Model
{
    use Auditable, BelongsToExploitation, HasFactory, HasUuids;

    protected $table = 'mises_bas';

    protected $fillable = [
        'exploitation_id', 'saillie_id', 'date_mise_bas', 'nombre_nes', 'nombre_survivants', 'notes',
    ];

    protected $casts = [
        'date_mise_bas' => 'date',
    ];

    public function saillie(): BelongsTo
    {
        return $this->belongsTo(Saillie::class);
    }

    public function nouveauNes(): HasMany
    {
        return $this->hasMany(Animal::class, 'mise_bas_id');
    }
}
