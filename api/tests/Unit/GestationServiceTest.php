<?php

namespace Tests\Unit;

use App\Services\Reproduction\GestationService;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * T051 : calcul de la durée de gestation par espèce.
 */
class GestationServiceTest extends TestCase
{
    private GestationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GestationService;
    }

    #[DataProvider('especes')]
    public function test_date_prevue_mise_bas_selon_espece(string $espece, int $joursAttendus): void
    {
        $dateSaillie = Carbon::parse('2026-01-01');

        $datePrevue = $this->service->datePrevueMiseBas($espece, $dateSaillie);

        $this->assertSame($joursAttendus, (int) $dateSaillie->diffInDays($datePrevue));
    }

    public static function especes(): array
    {
        return [
            'bovin' => ['bovin', 283],
            'caprin' => ['caprin', 150],
            'ovin' => ['ovin', 152],
            'porcin' => ['porcin', 114],
            'camelin' => ['camelin', 390],
        ];
    }

    public function test_espece_inconnue_leve_une_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->dureeJours('extraterrestre');
    }
}
