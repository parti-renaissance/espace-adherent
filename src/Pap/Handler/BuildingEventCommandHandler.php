<?php

namespace App\Pap\Handler;

use App\Entity\Pap\Building;
use App\Entity\Pap\BuildingBlock;
use App\Entity\Pap\BuildingBlockStatistics;
use App\Entity\Pap\BuildingEvent;
use App\Entity\Pap\BuildingStatistics;
use App\Entity\Pap\CampaignStatisticsInterface;
use App\Entity\Pap\CampaignStatisticsOwnerInterface;
use App\Entity\Pap\Floor;
use App\Entity\Pap\FloorStatistics;
use App\Pap\BuildingEventActionEnum;
use App\Pap\BuildingEventTypeEnum;
use App\Pap\BuildingStatusEnum;
use App\Pap\Command\BuildingEventCommand;
use App\Repository\Pap\BuildingEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class BuildingEventCommandHandler implements MessageHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private BuildingEventRepository $buildingEventRepository;

    public function __construct(EntityManagerInterface $entityManager, BuildingEventRepository $buildingEventRepository)
    {
        $this->entityManager = $entityManager;
        $this->buildingEventRepository = $buildingEventRepository;
    }

    public function __invoke(BuildingEventCommand $command): void
    {
        foreach (BuildingEventTypeEnum::toArray() as $type) {
            $buildingEvent = $this->buildingEventRepository->findLastByType($type, $command->getBuildingUuid(), $command->getCampaignUuid());

            if (!$buildingEvent) {
                continue;
            }

            $building = $buildingEvent->getBuilding();
            switch ($type) {
                case BuildingEventTypeEnum::BUILDING:
                    $objectWithStats = $building;

                    break;
                case BuildingEventTypeEnum::BUILDING_BLOCK:
                    $objectWithStats = $building->getBuildingBlockByName($buildingEvent->getIdentifier());
                    if (!$objectWithStats) {
                        throw new \RuntimeException(sprintf('BuildingBlock with name "%s" is not found in the Building with uuid "%s"', $buildingEvent->getIdentifier(), $building->getUuid()));
                    }

                    break;
                case BuildingEventTypeEnum::FLOOR:
                    list($name, $number) = explode('-', $buildingEvent->getIdentifier());
                    $buildingBlock = $building->getBuildingBlockByName($name);
                    if (!$buildingBlock) {
                        throw new \RuntimeException(sprintf('BuildingBlock with name "%s" is not found in the Building with uuid "%s"', $name, $building->getUuid()));
                    }
                    $objectWithStats = $buildingBlock->getFloorByNumber($number);
                    if (!$objectWithStats) {
                        throw new \RuntimeException(sprintf('Floor with number "%s" is not found in the BuildingBlock named "%s" in the Building with uuid "%s"', $number, $name, $building->getUuid()));
                    }

                    break;
                default:
                    throw new \InvalidArgumentException(sprintf('Type %s is not supported for creation a building statistics', $type));
            }

            $this->createStatistics($objectWithStats, $buildingEvent);
        }

        $this->entityManager->flush();
    }

    private function createStatistics(CampaignStatisticsOwnerInterface $object, BuildingEvent $buildingEvent): void
    {
        $status = BuildingEventActionEnum::CLOSE === $buildingEvent->getAction() ? BuildingStatusEnum::COMPLETED : BuildingStatusEnum::ONGOING;
        $campaign = $buildingEvent->getCampaign();
        /** @var CampaignStatisticsInterface $stats */
        if (!$stats = $object->findStatisticsForCampaign($campaign)) {
            switch (\get_class($object)) {
                case Building::class:
                    $stats = new BuildingStatistics($object, $campaign, $status);

                    break;
                case BuildingBlock::class:
                    $stats = new BuildingBlockStatistics($object, $campaign, $status);

                    break;
                case Floor::class:
                    $stats = new FloorStatistics($object, $campaign, $status);

                    break;
            }

            $this->entityManager->persist($stats);
        }

        $stats->setClosedAt(BuildingStatusEnum::COMPLETED === $status ? $buildingEvent->getCreatedAt() : null);
        $stats->setClosedBy(BuildingStatusEnum::COMPLETED === $status ? $buildingEvent->getAuthor() : null);
    }
}
