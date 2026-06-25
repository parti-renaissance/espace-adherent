<?php

declare(strict_types=1);

namespace Tests\App\Unit\JeMengage\Timeline\CandidateSelection;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\PostAddress;
use App\JeMengage\Timeline\CandidateSelection\RequestFilterCondition;
use App\JeMengage\Timeline\CandidateSelection\TimelineRequestFilterFactory;
use App\Repository\Geo\ZoneRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

final class TimelineRequestFilterFactoryTest extends TestCase
{
    private const string ZONE_UUID = 'e3efe563-906e-11eb-a875-0242ac150002';
    private const string COMMITTEE_UUID = '515a56c0-bde8-56ef-b90c-4745b1c93818';

    public function testCumulatedParamsYieldAndedConditionsInOrder(): void
    {
        $repository = $this->createMock(ZoneRepository::class);
        $repository
            ->expects(self::once())
            ->method('findOneByUuid')
            ->with(self::ZONE_UUID)
            ->willReturn(new Zone(Zone::DEPARTMENT, '75', 'Paris', Uuid::fromString(self::ZONE_UUID)));

        // French resident: the circonscription instance reads the DISTRICT zones.
        $user = self::frenchAdherent();
        $user->addZone(new Zone(Zone::DISTRICT, '75-1', 'Paris (1)'));

        $request = Request::create('/', 'GET', [
            'zone' => self::ZONE_UUID,
            'committee' => self::COMMITTEE_UUID,
            'instance' => 'circonscription',
        ]);

        $filter = new TimelineRequestFilterFactory($repository)->createFromRequest($request, $user);

        self::assertNotNull($filter);
        self::assertSame(
            [
                [RequestFilterCondition::ZONE, 'department:75'],
                [RequestFilterCondition::COMMITTEE, self::COMMITTEE_UUID],
                [RequestFilterCondition::ZONE, 'district:75-1'],
            ],
            array_map(static function (RequestFilterCondition $condition): array {
                return [$condition->kind, $condition->value];
            }, $filter->conditions)
        );
    }

    public function testInvalidZoneUuidDegradesToNationalCondition(): void
    {
        $repository = $this->createMock(ZoneRepository::class);
        $repository->expects(self::never())->method('findOneByUuid');

        $filter = new TimelineRequestFilterFactory($repository)
            ->createFromRequest(Request::create('/', 'GET', ['zone' => 'not-a-uuid']), new Adherent());

        self::assertSame(RequestFilterCondition::NATIONAL, $filter->conditions[0]->kind);
    }

    public function testUnknownZoneUuidDegradesToNationalCondition(): void
    {
        $repository = $this->createMock(ZoneRepository::class);
        $repository
            ->expects(self::once())
            ->method('findOneByUuid')
            ->with(self::ZONE_UUID)
            ->willReturn(null);

        $filter = new TimelineRequestFilterFactory($repository)
            ->createFromRequest(Request::create('/', 'GET', ['zone' => self::ZONE_UUID]), new Adherent());

        self::assertSame(RequestFilterCondition::NATIONAL, $filter->conditions[0]->kind);
    }

    public function testInvalidCommitteeUuidYieldsNoCondition(): void
    {
        $filter = new TimelineRequestFilterFactory($this->createStub(ZoneRepository::class))
            ->createFromRequest(Request::create('/', 'GET', ['committee' => 'nope']), new Adherent());

        self::assertNull($filter);
    }

    public function testCirconscriptionWithNonContiguousZoneKeysStillResolves(): void
    {
        // getZonesOfType() is an array_filter with preserved keys: with a city zone first, the
        // district sits at key 1 — the legacy [0] access dropped it (hardened bug).
        $user = self::frenchAdherent();
        $user->addZone(new Zone(Zone::CITY, '75056', 'Paris'));
        $user->addZone(new Zone(Zone::DISTRICT, '75-2', 'Paris (2)'));

        $filter = new TimelineRequestFilterFactory($this->createStub(ZoneRepository::class))
            ->createFromRequest(Request::create('/', 'GET', ['instance' => 'circonscription']), $user);

        self::assertNotNull($filter);
        self::assertSame([RequestFilterCondition::ZONE], array_column($filter->conditions, 'kind'));
        self::assertSame('district:75-2', $filter->conditions[0]->value);
    }

    public function testCirconscriptionWithoutDistrictZoneYieldsNoFilter(): void
    {
        $filter = new TimelineRequestFilterFactory($this->createStub(ZoneRepository::class))
            ->createFromRequest(Request::create('/', 'GET', ['instance' => 'circonscription']), self::frenchAdherent());

        self::assertNull($filter);
    }

    public function testAgoraInstanceWithoutMembershipYieldsNoFilter(): void
    {
        $filter = new TimelineRequestFilterFactory($this->createStub(ZoneRepository::class))
            ->createFromRequest(Request::create('/', 'GET', ['instance' => 'agora']), new Adherent());

        self::assertNull($filter);
    }

    public function testNoParamsYieldNullFilter(): void
    {
        $filter = new TimelineRequestFilterFactory($this->createStub(ZoneRepository::class))
            ->createFromRequest(Request::create('/', 'GET'), new Adherent());

        self::assertNull($filter);
    }

    private static function frenchAdherent(): Adherent
    {
        $adherent = new Adherent();
        // postAddress is protected; isForeignResident() needs an address to read the country from.
        new \ReflectionProperty(Adherent::class, 'postAddress')->setValue($adherent, PostAddress::createFrenchAddress('1 rue de Test', '75001-75101', 'Paris'));

        return $adherent;
    }
}
