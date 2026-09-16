<?php

namespace App\Services\Financement;

use RuntimeException;

class DossierIncompletException extends RuntimeException
{
    /** @param array<string> $manques */
    public function __construct(public readonly array $manques)
    {
        parent::__construct('Dossier de financement incomplet : '.implode(' ', $manques));
    }
}
