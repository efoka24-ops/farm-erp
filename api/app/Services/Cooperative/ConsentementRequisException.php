<?php

namespace App\Services\Cooperative;

use App\Models\Exploitation;
use RuntimeException;

class ConsentementRequisException extends RuntimeException
{
    public function __construct(public readonly Exploitation $membre)
    {
        parent::__construct("Le membre '{$membre->nom}' n'a pas consenti au partage de ses données individuelles.");
    }
}
