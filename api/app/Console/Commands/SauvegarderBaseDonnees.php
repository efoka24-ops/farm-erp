<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

/**
 * Sauvegarde locale chiffrée (T015 adapté) : l'hébergement mutualisé Camoo ne
 * propose pas de S3 ; on dump MySQL, on chiffre avec openssl (AES-256-CBC,
 * passphrase BACKUP_PASSPHRASE) et on conserve N jours dans storage/app/backups.
 * À planifier via le cron de l'hébergeur (ou Laravel Scheduler si un worker
 * persistant est disponible).
 */
class SauvegarderBaseDonnees extends Command
{
    protected $signature = 'sauvegarde:executer {--conserver=30 : Jours de rétention}';

    protected $description = 'Dump la base de données et la chiffre localement (AES-256)';

    public function handle(): int
    {
        $passphrase = config('backup.passphrase');

        if (! $passphrase) {
            $this->error('BACKUP_PASSPHRASE non configurée dans .env.');

            return self::FAILURE;
        }

        $horodatage = now()->format('Y-m-d_His');
        $dumpPath = storage_path("app/backups/dump_{$horodatage}.sql");
        $chiffrePath = "{$dumpPath}.enc";

        Storage::makeDirectory('backups');

        $config = config('database.connections.'.config('database.default'));

        $dump = new Process([
            'mysqldump',
            '-h', $config['host'],
            '-P', (string) $config['port'],
            '-u', $config['username'],
            '--password='.$config['password'],
            $config['database'],
        ]);
        $dump->setTimeout(600);
        $dump->run(fn ($type, $buffer) => file_put_contents($dumpPath, $buffer, FILE_APPEND));

        if (! $dump->isSuccessful() || ! file_exists($dumpPath)) {
            $this->error('Échec du mysqldump : '.$dump->getErrorOutput());

            return self::FAILURE;
        }

        $chiffrement = new Process([
            'openssl', 'enc', '-aes-256-cbc', '-salt', '-pbkdf2',
            '-in', $dumpPath, '-out', $chiffrePath, '-pass', "pass:{$passphrase}",
        ]);
        $chiffrement->run();
        unlink($dumpPath);

        if (! $chiffrement->isSuccessful()) {
            $this->error('Échec du chiffrement : '.$chiffrement->getErrorOutput());

            return self::FAILURE;
        }

        $this->supprimerAnciennesSauvegardes((int) $this->option('conserver'));

        $this->info("Sauvegarde chiffrée créée : {$chiffrePath}");

        return self::SUCCESS;
    }

    private function supprimerAnciennesSauvegardes(int $joursConservation): void
    {
        $seuil = now()->subDays($joursConservation)->timestamp;

        foreach (Storage::files('backups') as $fichier) {
            if (Storage::lastModified($fichier) < $seuil) {
                Storage::delete($fichier);
            }
        }
    }
}
