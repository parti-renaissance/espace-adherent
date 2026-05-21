<?php

declare(strict_types=1);

namespace Tests\App\Normalizer\Indexer;

use App\Collection\ZoneCollection;
use App\Entity\Event\Event;
use App\Entity\Geo\Zone;
use App\Normalizer\Indexer\EventNormalizer;
use App\Repository\EventRegistrationRepository;
use App\Scope\ScopeEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class EventNormalizerTest extends TestCase
{
    public function testMilitantEventIsIndexedWithCityCodeOnly(): void
    {
        $event = $this->createStub(Event::class);
        $event->method('getAuthorScope')->willReturn(ScopeEnum::MILITANT);
        $event->method('getZones')->willReturn(new ZoneCollection([$this->cityWithDepartmentParent()]));

        self::assertSame(['city_75056'], $this->exposeZoneCodes($event));
    }

    public function testCadreEventIsIndexedWithParentZoneCodes(): void
    {
        $event = $this->createStub(Event::class);
        $event->method('getAuthorScope')->willReturn(ScopeEnum::DEPUTY);
        $event->method('getZones')->willReturn(new ZoneCollection([$this->cityWithDepartmentParent()]));

        $codes = $this->exposeZoneCodes($event);

        self::assertContains('city_75056', $codes);
        self::assertContains('department_75', $codes);
    }

    private function cityWithDepartmentParent(): Zone
    {
        $city = new Zone(Zone::CITY, '75056', 'Paris');
        $city->addParent(new Zone(Zone::DEPARTMENT, '75', 'Paris'));

        return $city;
    }

    private function exposeZoneCodes(Event $event): ?array
    {
        $normalizer = new class($this->createStub(EventRegistrationRepository::class), $this->createStub(UrlGeneratorInterface::class)) extends EventNormalizer {
            public function exposeZoneCodes(Event $event): ?array
            {
                return $this->getZoneCodes($event);
            }
        };

        return $normalizer->exposeZoneCodes($event);
    }
}
