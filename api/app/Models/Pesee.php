<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToExploitation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pesee extends Model
{
    use Auditable, BelongsToExploitation, HasFactory, HasUuids;

    protected $fillable = [
        'exploitation_id', 'animal_id', 'poids_kg', 'date_pesee', 'saisi_par',
    ];

    protected $casts = [
        'poids_kg' => 'decimal:2',
        'date_pesee' => 'date',
    ];

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public function saisisseur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'saisi_par');
    }
}
