<?php

namespace App\Pap\Handler;

use App\Entity\Pap\Building;
use App\Entity\Pap\BuildingEvent;
use App\Entity\Pap\BuildingStatistics;
use App\Entity\Pap\CampaignStatisticsInterface;
use App\Entity\Pap\CampaignStatisticsOwnerInterface;
use App\Pap\BuildingEventActionEnum;
use App\Pap\BuildingEventTypeEnum;
use App\Pap\BuildingStatusEnum;
use App\Pap\Command\BuildingEventCommandInterface;
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

    public function __invoke(BuildingEventCommandInterface $command): void
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
                    [$name, $number] = explode('-', $buildingEvent->getIdentifier());
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

            $this->updateStatisticsCloseInfo($objectWithStats, $buildingEvent);
        }

        $this->entityManager->flush();
    }

    private function updateStatisticsCloseInfo(
        CampaignStatisticsOwnerInterface $object,
        BuildingEvent $buildingEvent
    ): void {
        $status = BuildingEventActionEnum::CLOSE === $buildingEvent->getAction() ? BuildingStatusEnum::COMPLETED : BuildingStatusEnum::ONGOING;
        $campaign = $buildingEvent->getCampaign();
        /** @var CampaignStatisticsInterface $stats */
        if (!$stats = $object->findStatisticsForCampaign($campaign)) {
            throw new \RuntimeException(sprintf('Statistics not found for entity "%s" with id "%s" for PAP campaign with id "%s"', $object::class, $object->getId(), $campaign->getId()));
        }

        $statusDetail = null;
        if (
            $object instanceof Building
            && BuildingStatusEnum::COMPLETED === $status
        ) {
            $statusDetail = BuildingStatusEnum::COMPLETED_PAP;

            if ('boitage' === $buildingEvent->closeType) {
                $statusDetail = BuildingStatusEnum::COMPLETED_BOITAGE;
                /** @var BuildingStatistics $stats */
                if ($stats->getNbVisitedDoors() > 0) {
                    $statusDetail = BuildingStatusEnum::COMPLETED_HYBRID;
                }
            }

            $stats->setNbDistributedPrograms($buildingEvent->programs ?? 0);
        }

        $stats->setStatus($status);
        $stats->setStatusDetail($statusDetail);

        $stats->setClosedAt(BuildingStatusEnum::COMPLETED === $status ? $buildingEvent->getCreatedAt() : null);
        $stats->setClosedBy(BuildingStatusEnum::COMPLETED === $status ? $buildingEvent->getAuthor() : null);
    }
}
