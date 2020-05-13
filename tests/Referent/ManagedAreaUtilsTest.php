<?php

namespace Tests\App\Referent;

use App\Entity\Adherent;
use App\Referent\ManagedAreaUtils;
use PHPUnit\Framework\TestCase;

class ManagedAreaUtilsTest extends TestCase
{
    /**
     * @dataProvider provideLocationsAndTags
     */
    public function testGetLocalCodes(string $country, ?string $postalCode, array $expectedCodes): void
    {
        $adherent = $this->createMock(Adherent::class);
        $adherent->expects(self::any())->method('getCountry')->willReturn($country);
        $adherent->expects(self::any())->method('getPostalCode')->willReturn($postalCode);

        $this->assertSame($expectedCodes, ManagedAreaUtils::getLocalCodes($adherent));
    }

    public function provideLocationsAndTags(): \Generator
    {
        yield ['CH', null, ['CH']];
        yield ['DE', null, ['DE']];
        yield ['FR', '59000', ['59']];
        yield ['FR', '06200', ['06']];
        yield ['FR', '75002', ['75002', '75']];
        yield ['FR', '75013', ['75013', '75']];
        yield ['FR', '20100', ['2A', '20']];
        yield ['FR', '20200', ['2B', '20']];
        yield ['FR', '97120', ['971']];
        yield ['FR', '97133', ['97133']];
        yield ['FR', '97150', ['97150']];
        yield ['FR', '97240', ['972']];
        yield ['FR', '98820', ['988']];
        yield ['MC', '98000', ['MC']];
    }
}
