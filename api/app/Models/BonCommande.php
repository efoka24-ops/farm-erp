<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToExploitation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BonCommande extends Model
{
    use Auditable, BelongsToExploitation, HasFactory, HasUuids;

    protected $table = 'bons_commande';

    protected $fillable = [
        'exploitation_id', 'categorie_stock_id', 'quantite_commandee', 'pdf_path', 'statut', 'genere_par',
    ];

    protected $casts = [
        'quantite_commandee' => 'decimal:2',
    ];

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(CategorieStock::class, 'categorie_stock_id');
    }
}
