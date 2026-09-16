<?php

namespace App\Logging;

use Illuminate\Log\Logger;
use Monolog\Formatter\JsonFormatter;

/**
 * Logging structuré (T016) : formate chaque entrée en JSON (un objet par ligne),
 * exploitable par un agrégateur de logs sans parsing regex fragile.
 */
class JsonFormatterTap
{
    public function __invoke(Logger $logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new JsonFormatter);
        }
    }
}
