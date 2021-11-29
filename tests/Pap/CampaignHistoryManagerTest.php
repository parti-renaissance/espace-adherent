<?php

namespace Tests\App\Pap;

use App\DataFixtures\ORM\LoadPapCampaignHistoryData;
use App\Entity\Pap\BuildingBlock;
use App\Entity\Pap\CampaignHistory;
use App\Entity\Pap\Floor;
use App\Pap\BuildingStatusEnum;
use App\Pap\CampaignHistoryManager;
use App\Repository\Pap\CampaignHistoryRepository;
use Doctrine\Persistence\ObjectRepository;
use Tests\App\AbstractKernelTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group pap
 */
class CampaignHistoryManagerTest extends AbstractKernelTestCase
{
    use ControllerTestTrait;

    private ?CampaignHistoryManager $campaignHistoryManager;
    private ?CampaignHistoryRepository $campaignHistoryRepository;
    private ?ObjectRepository $buildingBlockRepository;
    private ?ObjectRepository $floorRepository;

    public function testCreateBuildingPartsWhenAllExist(): void
    {
        $nbBb = $this->countBuildingBlocks();
        $nbFloors = $this->countFloors();

        /** @var CampaignHistory $campaignHistory */
        $campaignHistory = $this->campaignHistoryRepository->findOneBy(['uuid' => LoadPapCampaignHistoryData::HISTORY_1_UUID]);

        $this->campaignHistoryManager->createBuildingParts($campaignHistory);

        $this->manager->clear();

        $this->assertSame($nbBb, $this->countBuildingBlocks());
        $this->assertSame($nbFloors, $this->countFloors());
    }

    public function testCreateBuildingPartsWhenNothingExist(): void
    {
        $buildingBlockName = 'Test';
        $floorNumber = 30;
        $nbBb = $this->countBuildingBlocks();
        $nbFloors = $this->countFloors();

        /** @var CampaignHistory $campaignHistory */
        $campaignHistory = $this->campaignHistoryRepository->findOneBy(['uuid' => LoadPapCampaignHistoryData::HISTORY_1_UUID]);
        $campaignHistory->setBuildingBlock($buildingBlockName);
        $campaignHistory->setFloor($floorNumber);
        $campaign = $campaignHistory->getCampaign();

        $this->campaignHistoryManager->createBuildingParts($campaignHistory);

        $this->manager->clear();

        $building = $campaignHistory->getBuilding();
        $this->assertSame(++$nbBb, $this->countBuildingBlocks());
        $this->assertSame(++$nbFloors, $this->countFloors());

        $this->assertNotNull($buildingStats = $building->findStatisticsForCampaign($campaign));
        $this->assertSame(BuildingStatusEnum::ONGOING, $buildingStats->getStatus());

        $this->assertNotNull($buildingBlock = $building->getBuildingBlockByName($buildingBlockName));
        $this->assertNotNull($buildingBlockStats = $buildingBlock->findStatisticsForCampaign($campaign));
        $this->assertSame(BuildingStatusEnum::ONGOING, $buildingBlockStats->getStatus());

        $this->assertNotNull($floor = $buildingBlock->getFloorByNumber($floorNumber));
        $this->assertNotNull($floorStats = $floor->findStatisticsForCampaign($campaign));
        $this->assertSame(BuildingStatusEnum::ONGOING, $floorStats->getStatus());
    }

    private function countFloors(): int
    {
        return (int) $this->floorRepository->createQueryBuilder('f')
            ->select('COUNT(1)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function countBuildingBlocks(): int
    {
        return (int) $this->buildingBlockRepository->createQueryBuilder('bb')
            ->select('COUNT(1)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->campaignHistoryManager = $this->get(CampaignHistoryManager::class);
        $this->campaignHistoryRepository = $this->getPapCampaignHistoryRepository();
        $this->buildingBlockRepository = $this->getRepository(BuildingBlock::class);
        $this->floorRepository = $this->getRepository(Floor::class);
    }

    protected function tearDown(): void
    {
        $this->campaignHistoryManager = null;
        $this->campaignHistoryRepository = null;
        $this->buildingBlockRepository = null;
        $this->floorRepository = null;

        parent::tearDown();
    }
}
