<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportMokinevotoEchoue extends Model
{
    protected $table = 'imports_mokinevoto_echoues';

    protected $fillable = ['payload', 'raison', 'tentatives', 'resolu_at'];

    protected $casts = [
        'payload' => 'array',
        'resolu_at' => 'datetime',
    ];
}
