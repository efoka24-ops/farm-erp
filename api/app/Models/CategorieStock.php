<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToExploitation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategorieStock extends Model
{
    use Auditable, BelongsToExploitation, HasFactory, HasUuids;

    protected $table = 'categories_stock';

    protected $fillable = [
        'exploitation_id', 'nom', 'type', 'unite', 'seuil_alerte_quantite',
        'fournisseur_nom', 'fournisseur_contact',
    ];

    protected $casts = [
        'seuil_alerte_quantite' => 'decimal:2',
    ];

    public function lots(): HasMany
    {
        return $this->hasMany(LotStock::class);
    }
}
