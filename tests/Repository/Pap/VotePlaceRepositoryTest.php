<?php

namespace Tests\App\Repository\Pap;

use App\Entity\Geo\Zone;
use App\Entity\Pap\VotePlace;
use App\Repository\Geo\ZoneRepository;
use App\Repository\Pap\VotePlaceRepository;
use Tests\App\AbstractKernelTestCase;

/**
 * @group functional
 */
class VotePlaceRepositoryTest extends AbstractKernelTestCase
{
    private ?VotePlaceRepository $votePlaceRepository = null;
    private ?ZoneRepository $zoneRepository = null;

    /**
     * @dataProvider provideFindForZone
     */
    public function testFindForZone(string $zoneType, string $zoneCode, int $expectedVotePlaceCount)
    {
        $zone = $this->zoneRepository->findOneBy([
            'type' => $zoneType,
            'code' => $zoneCode,
        ]);

        $votePlaces = $this->votePlaceRepository->findForZone($zone);

        self::assertCount($expectedVotePlaceCount, $votePlaces);

        foreach ($votePlaces as $votePlace) {
            self::assertSame($zoneCode, $votePlace->zone->getCode());
        }
    }

    public function provideFindForZone(): iterable
    {
        yield [Zone::DISTRICT, '75-1', 5];
        yield [Zone::DISTRICT, '92-2', 3];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->votePlaceRepository = $this->getRepository(VotePlace::class);
        $this->zoneRepository = $this->getRepository(Zone::class);
    }

    protected function tearDown(): void
    {
        $this->votePlaceRepository = null;
        $this->zoneRepository = null;

        parent::tearDown();
    }
}
