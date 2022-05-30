<?php

namespace Tests\App\Repository\Geo;

use App\Entity\Geo\Zone;
use App\Repository\Geo\ZoneRepository;
use Tests\App\AbstractKernelTestCase;

/**
 * @group functional
 */
class ZoneRepositoryTest extends AbstractKernelTestCase
{
    private ?ZoneRepository $zoneRepository = null;

    public function testFindZonesWithoutLocalPapCampaign(): void
    {
        self::assertSame(567, $this->zoneRepository->count(['type' => Zone::DISTRICT]));

        // DataFixtures have only 1 local campaign for a district: 75-1
        self::assertCount(566, $this->zoneRepository->findZonesWithoutLocalPapCampaign(Zone::DISTRICT));
        self::assertCount(1, $this->zoneRepository->findZonesWithoutLocalPapCampaign(Zone::DISTRICT, '92-2'));
        self::assertCount(0, $this->zoneRepository->findZonesWithoutLocalPapCampaign(Zone::DISTRICT, '75-1'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->zoneRepository = $this->getRepository(Zone::class);
    }

    protected function tearDown(): void
    {
        $this->zoneRepository = null;

        parent::tearDown();
    }
}
