<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    public const GERANT = 'gerant';

    public const AGENT_TERRAIN = 'agent_terrain';

    public const COMPTABLE = 'comptable';

    public const GESTIONNAIRE_COOPERATIVE = 'gestionnaire_cooperative';

    public const VETERINAIRE_EXTERNE = 'veterinaire_externe';

    protected $fillable = ['slug', 'libelle'];

    public function utilisateurs(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
