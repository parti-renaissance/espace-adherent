<?php

declare(strict_types=1);

namespace App\Pap;

use App\Entity\Pap\BuildingBlock;
use App\Entity\Pap\CampaignHistory;
use App\Entity\Pap\Floor;
use Doctrine\ORM\EntityManagerInterface;

class CampaignHistoryManager
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function createBuildingParts(CampaignHistory $campaignHistory): void
    {
        $createdBy = $campaignHistory->getQuestioner();
        $building = $campaignHistory->getBuilding();
        if (!$buildingBlockName = $campaignHistory->getBuildingBlock()) {
            return;
        }

        if (!$buildingBlock = $building->getBuildingBlockByName($buildingBlockName)) {
            $buildingBlock = new BuildingBlock($buildingBlockName, $building);
            $buildingBlock->setCreatedByAdherent($createdBy);
            $building->addBuildingBlock($buildingBlock);

            $this->em->persist($buildingBlock);
        }

        $floorNumber = $campaignHistory->getFloor();
        if (null !== $floorNumber) {
            if (!$buildingBlock->getFloorByNumber($floorNumber)) {
                $floor = new Floor($floorNumber, $buildingBlock);
                $floor->setCreatedByAdherent($createdBy);
                $buildingBlock->addFloor($floor);

                $this->em->persist($floor);
            }
        }

        $this->em->flush();
    }
}
