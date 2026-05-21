<?php

declare(strict_types=1);

namespace Tests\App\Geo\Subscriber;

use App\Collection\ZoneCollection;
use App\Entity\Adherent;
use App\Entity\Event\Event;
use App\Entity\Geo\Zone;
use App\Entity\NullablePostAddress;
use App\Event\EventEvent;
use App\Geo\Subscriber\ZoneAssignerSubscriber;
use App\Geo\ZoneMatcher;
use App\Repository\Geo\ZoneRepository;
use App\Scope\Scope;
use App\Scope\ScopeGeneratorResolver;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class ZoneAssignerSubscriberTest extends TestCase
{
    public function testMilitantEventWithAddressGetsCommuneZone(): void
    {
        $cityZone = new Zone(Zone::CITY, '75056', 'Paris');

        $address = $this->createStub(NullablePostAddress::class);
        $address->method('getInseeCode')->willReturn('75056');

        $event = $this->createMock(Event::class);
        $event->method('getZones')->willReturn(new ZoneCollection());
        $event->method('getCommittee')->willReturn(null);
        $event->method('isOnline')->willReturn(false);
        $event->method('getPostAddress')->willReturn($address);
        $event->expects(self::once())->method('setZones')->with([$cityZone]);

        $zoneMatcher = $this->createStub(ZoneMatcher::class);
        $zoneMatcher->method('match')->willReturn([$cityZone]);

        $this
            ->subscriber($this->militantScope([new Zone(Zone::CITY, '69123', 'Lyon')]), $zoneMatcher)
            ->assignZoneToEvent(new EventEvent(null, $event))
        ;
    }

    public function testMilitantEventWithAddressButNoInseeFallsBackToAdherentCityZone(): void
    {
        $adherentCityZone = new Zone(Zone::CITY, '69123', 'Lyon');

        $address = $this->createStub(NullablePostAddress::class);
        $address->method('getInseeCode')->willReturn(null);

        $event = $this->createMock(Event::class);
        $event->method('getZones')->willReturn(new ZoneCollection());
        $event->method('getCommittee')->willReturn(null);
        $event->method('isOnline')->willReturn(false);
        $event->method('getPostAddress')->willReturn($address);
        $event->expects(self::once())->method('setZones')->with([$adherentCityZone]);

        $this
            ->subscriber($this->militantScope([$adherentCityZone]), $this->createStub(ZoneMatcher::class))
            ->assignZoneToEvent(new EventEvent(null, $event))
        ;
    }

    public function testMilitantEventWithoutCityMatchFallsBackToAdherentCityZone(): void
    {
        $adherentCityZone = new Zone(Zone::CITY, '69123', 'Lyon');

        $address = $this->createStub(NullablePostAddress::class);
        $address->method('getInseeCode')->willReturn('99999');

        $event = $this->createMock(Event::class);
        $event->method('getZones')->willReturn(new ZoneCollection());
        $event->method('getCommittee')->willReturn(null);
        $event->method('isOnline')->willReturn(false);
        $event->method('getPostAddress')->willReturn($address);
        $event->expects(self::once())->method('setZones')->with([$adherentCityZone]);

        // ZoneMatcher returns no CITY zone → fallback to the adherent's city.
        $zoneMatcher = $this->createStub(ZoneMatcher::class);
        $zoneMatcher->method('match')->willReturn([new Zone(Zone::DEPARTMENT, '69', 'Rhône')]);

        $this
            ->subscriber($this->militantScope([$adherentCityZone]), $zoneMatcher)
            ->assignZoneToEvent(new EventEvent(null, $event))
        ;
    }

    public function testOnlineMilitantEventFallsBackToAdherentCityZone(): void
    {
        $adherentCityZone = new Zone(Zone::CITY, '69123', 'Lyon');

        $event = $this->createMock(Event::class);
        $event->method('getZones')->willReturn(new ZoneCollection());
        $event->method('getCommittee')->willReturn(null);
        $event->method('isOnline')->willReturn(true);
        $event->method('getPostAddress')->willReturn(null);
        $event->expects(self::once())->method('setZones')->with([$adherentCityZone]);

        $this
            ->subscriber($this->militantScope([$adherentCityZone]), $this->createStub(ZoneMatcher::class))
            ->assignZoneToEvent(new EventEvent(null, $event))
        ;
    }

    public function testNonMilitantScopeKeepsExistingBehaviour(): void
    {
        $deputyZone = new Zone(Zone::DEPARTMENT, '75', 'Paris');

        $event = $this->createMock(Event::class);
        $event->method('getZones')->willReturn(new ZoneCollection());
        $event->method('getCommittee')->willReturn(null);
        $event->expects(self::once())->method('setZones')->with([$deputyZone]);

        $this
            ->subscriber($this->scope('deputy', [$deputyZone]), $this->createStub(ZoneMatcher::class))
            ->assignZoneToEvent(new EventEvent(null, $event))
        ;
    }

    private function subscriber(Scope $scope, ZoneMatcher $zoneMatcher): ZoneAssignerSubscriber
    {
        $resolver = $this->createStub(ScopeGeneratorResolver::class);
        $resolver->method('generate')->willReturn($scope);

        return new ZoneAssignerSubscriber(
            $this->createStub(EntityManagerInterface::class),
            $zoneMatcher,
            $resolver,
            $this->createStub(ZoneRepository::class),
        );
    }

    private function militantScope(array $adherentCityZones): Scope
    {
        // The militant scope is zone-less; the online fallback reads the city from the adherent.
        $adherent = $this->createStub(Adherent::class);
        $adherent
            ->method('getZonesOfType')
            ->willReturnCallback(static fn (string $type) => Zone::CITY === $type ? $adherentCityZones : [])
        ;

        return new Scope('militant', 'Militant', 'Militant', [], [], [], $adherent);
    }

    private function scope(string $code, array $zones): Scope
    {
        return new Scope($code, $code, $code, $zones, [], [], $this->createStub(Adherent::class));
    }
}
