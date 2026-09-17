<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToExploitation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LotStock extends Model
{
    use Auditable, BelongsToExploitation, HasFactory, HasUuids;

    protected $table = 'lots_stock';

    protected $fillable = [
        'exploitation_id', 'categorie_stock_id', 'quantite_initiale', 'quantite_restante',
        'cout_unitaire', 'date_reception', 'date_peremption',
    ];

    protected $casts = [
        'quantite_initiale' => 'decimal:2',
        'quantite_restante' => 'decimal:2',
        'cout_unitaire' => 'decimal:2',
        'date_reception' => 'date',
        'date_peremption' => 'date',
    ];

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(CategorieStock::class, 'categorie_stock_id');
    }
}
