<?php

declare(strict_types=1);

namespace Tests\App\Normalizer\Indexer;

use App\Entity\Action\Action;
use App\Entity\Geo\Zone;
use App\Normalizer\Indexer\ActionNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ActionNormalizerTest extends TestCase
{
    public function testActionIsIndexedWithItsCityCodeOnly(): void
    {
        $action = $this->createMock(Action::class);
        $action
            ->expects(self::atLeastOnce())
            ->method('getZonesOfType')
            ->with(Zone::CITY)
            ->willReturn([$this->cityWithDepartmentParent()])
        ;

        // Only the city code, never its parents (department/region/country) -> no national leak.
        self::assertSame(['city_75056'], $this->exposeZoneCodes($action));
    }

    public function testActionWithoutCityZoneIsNotZoned(): void
    {
        $action = $this->createMock(Action::class);
        $action
            ->expects(self::atLeastOnce())
            ->method('getZonesOfType')
            ->with(Zone::CITY)
            ->willReturn([])
        ;

        self::assertNull($this->exposeZoneCodes($action));
    }

    private function cityWithDepartmentParent(): Zone
    {
        $city = new Zone(Zone::CITY, '75056', 'Paris');
        $city->addParent(new Zone(Zone::DEPARTMENT, '75', 'Paris'));

        return $city;
    }

    private function exposeZoneCodes(Action $action): ?array
    {
        $normalizer = new class($this->createStub(UrlGeneratorInterface::class)) extends ActionNormalizer {
            public function exposeZoneCodes(Action $action): ?array
            {
                return $this->getZoneCodes($action);
            }
        };

        return $normalizer->exposeZoneCodes($action);
    }
}
