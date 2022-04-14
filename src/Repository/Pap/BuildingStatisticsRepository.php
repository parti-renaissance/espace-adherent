<?php

namespace App\Repository\Pap;

use App\Entity\Pap\BuildingStatistics;
use App\Entity\Pap\Campaign;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BuildingStatisticsRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BuildingStatistics::class);
    }

    public function countByStatus(Campaign $campaign, string $status): int
    {
        return $this->createQueryBuilder('buildingStatistics')
            ->select('COUNT(DISTINCT(buildingStatistics.id)) AS nb_building_statistics')
            ->innerJoin('buildingStatistics.campaign', 'campaign')
            ->andWhere('campaign = :campaign')
            ->andWhere('buildingStatistics.status = :status')
            ->setParameters([
                'campaign' => $campaign,
                'status' => $status,
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
