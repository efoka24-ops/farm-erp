<?php

namespace App\Models;

use App\Models\Concerns\BelongsToExploitation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MouvementStock extends Model
{
    use BelongsToExploitation, HasUuids;

    protected $table = 'mouvements_stock';

    protected $fillable = [
        'exploitation_id', 'lot_stock_id', 'type', 'quantite', 'motif', 'lie_a_type', 'lie_a_id',
    ];

    protected $casts = [
        'quantite' => 'decimal:2',
    ];

    public function lot(): BelongsTo
    {
        return $this->belongsTo(LotStock::class, 'lot_stock_id');
    }
}
