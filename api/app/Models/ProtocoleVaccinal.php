<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProtocoleVaccinal extends Model
{
    protected $table = 'protocoles_vaccinaux';

    protected $fillable = ['espece', 'vaccin', 'jour_apres_naissance', 'rappel_annuel'];

    protected $casts = ['rappel_annuel' => 'boolean'];
}
