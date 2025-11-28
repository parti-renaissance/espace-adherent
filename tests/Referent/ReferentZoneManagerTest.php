<?php

declare(strict_types=1);

namespace Tests\App\Referent;

use App\Entity\Adherent;
use App\Referent\ReferentZoneManager;
use App\Repository\Geo\ZoneRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReferentZoneManagerTest extends TestCase
{
    #[DataProvider('providesTestIsUpdateNeeded')]
    public function testIsUpdateNeeded(
        bool $isUpdateNeeded,
        string $country,
        ?string $inseeCode,
        array $zoneCodes,
    ): void {
        $referentZoneManager = new ReferentZoneManager($this->createMock(ZoneRepository::class));
        $adherent = $this->createAdherent($country, $inseeCode, $zoneCodes);

        $this->assertSame($isUpdateNeeded, $referentZoneManager->isUpdateNeeded($adherent));
    }

    public static function providesTestIsUpdateNeeded(): iterable
    {
        yield [false, 'CH', null, ['CH']];
        yield [false, 'FR', '75010', ['75010']];
        yield [false, 'FR', '6059', ['06059']];
        yield [true, 'FR', '75010', ['CH']];
        yield [true, 'CH', null, ['FR']];
        yield [true, 'FR', '6059', ['75010']];
        yield [true, 'FR', '92024', ['75010']];
    }

    /**
     * @return Adherent|MockObject
     */
    private function createAdherent(string $country, ?string $inseeCode, array $zoneCodes): Adherent
    {
        return $this->createConfiguredMock(Adherent::class, [
            'getCountry' => $country,
            'getInseeCode' => $inseeCode,
            'getZonesCodes' => $zoneCodes,
        ]);
    }
}
