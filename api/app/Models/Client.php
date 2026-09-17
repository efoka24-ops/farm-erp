<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToExploitation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use Auditable, BelongsToExploitation, HasFactory, HasUuids;

    protected $fillable = ['exploitation_id', 'nom', 'telephone', 'adresse'];

    public function ventes(): HasMany
    {
        return $this->hasMany(Vente::class);
    }
}
