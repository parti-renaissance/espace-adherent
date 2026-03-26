<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api\AdherentList;

use App\Controller\Api\AdherentList\AdherentListController;
use App\Entity\Geo\Zone;
use App\Exporter\ManagedUsersExporter;
use App\ManagedUsers\ManagedUsersFilter;
use App\Repository\Geo\ZoneRepository;
use App\Repository\Projection\ManagedUserRepository;
use App\Scope\AuthorizationChecker;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class AdherentListControllerTest extends TestCase
{
    private ZoneRepository&MockObject $zoneRepository;
    private AdherentListController $controller;

    protected function setUp(): void
    {
        $this->zoneRepository = $this->createMock(ZoneRepository::class);

        $this->controller = new AdherentListController(
            $this->createMock(AuthorizationChecker::class),
            $this->createMock(ManagedUserRepository::class),
            $this->createMock(DenormalizerInterface::class),
            $this->createMock(ManagedUsersExporter::class),
            $this->zoneRepository,
        );
    }

    #[DataProvider('provideZonesQueryParams')]
    public function testExtractZoneUuidsFromQueryParams(array $zonesData, array $expectedUuids): void
    {
        $reflection = new \ReflectionMethod($this->controller, 'extractZoneUuids');

        $result = $reflection->invoke($this->controller, $zonesData);

        self::assertSame($expectedUuids, array_values($result));
    }

    public static function provideZonesQueryParams(): iterable
    {
        $validUuid1 = '949774ae-872a-11eb-9419-42010a840019';
        $validUuid2 = 'e3efe6fd-906e-11eb-a875-42010a840012';

        yield 'uuid_strings' => [
            [$validUuid1, $validUuid2],
            [$validUuid1, $validUuid2],
        ];

        yield 'objects_with_uuid_and_name' => [
            [
                ['uuid' => $validUuid1, 'name' => 'Paris 5ème'],
                ['uuid' => $validUuid2, 'name' => 'Paris 6ème'],
            ],
            [$validUuid1, $validUuid2],
        ];

        yield 'invalid_uuid_strings_skipped' => [
            ['not-a-uuid', 'also-not-valid', $validUuid1],
            [$validUuid1],
        ];

        yield 'objects_without_uuid_key_skipped' => [
            [
                ['name' => 'Paris 5ème'],
                ['uuid' => $validUuid1, 'name' => 'Paris 6ème'],
            ],
            [$validUuid1],
        ];

        yield 'mixed_valid_and_invalid' => [
            [
                $validUuid1,
                ['uuid' => 'bad-uuid', 'name' => 'Invalid'],
                ['uuid' => $validUuid2, 'name' => 'Valid'],
                42,
                null,
            ],
            [$validUuid1, $validUuid2],
        ];

        yield 'empty_array' => [
            [],
            [],
        ];

        yield 'all_invalid' => [
            [
                ['name' => 'no uuid'],
                'not-uuid',
                123,
            ],
            [],
        ];
    }

    public function testResolveZonesCallsRepositoryWithValidUuids(): void
    {
        $uuid1 = '949774ae-872a-11eb-9419-42010a840019';
        $uuid2 = 'e3efe6fd-906e-11eb-a875-42010a840012';
        $zone1 = $this->createMock(Zone::class);
        $zone2 = $this->createMock(Zone::class);

        $this->zoneRepository
            ->expects(self::once())
            ->method('findByUuid')
            ->with([$uuid1, $uuid2])
            ->willReturn([$zone1, $zone2])
        ;

        $reflection = new \ReflectionMethod($this->controller, 'resolveZones');

        $result = $reflection->invoke($this->controller, [
            ['uuid' => $uuid1, 'name' => 'Paris 5ème'],
            ['uuid' => $uuid2, 'name' => 'Paris 6ème'],
        ]);

        self::assertSame([$zone1, $zone2], $result);
    }

    public function testResolveZonesReturnsEmptyArrayWhenNoValidUuids(): void
    {
        $this->zoneRepository
            ->expects(self::never())
            ->method('findByUuid')
        ;

        $reflection = new \ReflectionMethod($this->controller, 'resolveZones');

        $result = $reflection->invoke($this->controller, [
            ['name' => 'no uuid'],
            'not-a-valid-uuid',
        ]);

        self::assertSame([], $result);
    }

    public function testManagedUsersFilterZonesPropertyIsNotInFilterWriteGroup(): void
    {
        $property = new \ReflectionProperty(ManagedUsersFilter::class, 'zones');
        $groupsAttributes = $property->getAttributes(Groups::class);

        if (empty($groupsAttributes)) {
            self::assertTrue(true);

            return;
        }

        /** @var Groups $groups */
        $groups = $groupsAttributes[0]->newInstance();

        self::assertNotContains(
            'filter_write',
            $groups->getGroups(),
            'The "zones" property must NOT be in the "filter_write" group to prevent the serializer from trying to denormalize Zone objects from query params.'
        );
    }
}
