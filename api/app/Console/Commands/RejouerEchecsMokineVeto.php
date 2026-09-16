<?php

namespace App\Console\Commands;

use App\Services\Sante\MokineVetoImportService;
use Illuminate\Console\Command;

class RejouerEchecsMokineVeto extends Command
{
    protected $signature = 'mokinevoto:rejouer-echecs';

    protected $description = 'Rejoue les imports de consultations MokineVeto en attente (mode dégradé, T047)';

    public function handle(MokineVetoImportService $service): int
    {
        $resolus = $service->rejouerEchecs();

        $this->info("{$resolus} import(s) résolu(s).");

        return self::SUCCESS;
    }
}
