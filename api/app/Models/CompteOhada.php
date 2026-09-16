<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompteOhada extends Model
{
    protected $table = 'comptes_ohada';

    protected $fillable = ['code', 'libelle', 'classe', 'nature'];
}
