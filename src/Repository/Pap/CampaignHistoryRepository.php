<?php

namespace App\Repository\Pap;

use App\Entity\Pap\Building;
use App\Entity\Pap\CampaignHistory;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CampaignHistoryRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CampaignHistory::class);
    }

    public function findDoorsForFloor(Building $building, string $buildingBlock, int $floor): array
    {
        return array_filter(array_column(
            $this
                ->createQueryBuilder('campaignHistory')
                ->select('campaignHistory.door')
                ->where('campaignHistory.building = :building')
                ->andWhere('campaignHistory.buildingBlock = :buildingBlock AND campaignHistory.floor = :floor')
                ->setParameters([
                    'building' => $building,
                    'buildingBlock' => $buildingBlock,
                    'floor' => $floor,
                ])
                ->getQuery()
                ->getArrayResult(),
            'door'))
        ;
    }
}
