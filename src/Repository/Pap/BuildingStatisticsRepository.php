<?php

namespace App\Repository\Pap;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Entity\Pap\BuildingStatistics;
use App\Entity\Pap\Campaign;
use App\Pap\BuildingStatusEnum;
use App\Repository\PaginatorTrait;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BuildingStatisticsRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;
    use PaginatorTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BuildingStatistics::class);
    }

    public function countByStatus(Campaign $campaign): array
    {
        $kpis = $this->createQueryBuilder('buildingStatistics')
            ->select('COUNT(IF(buildingStatistics.status = :status_todo, buildingStatistics.id, null)) AS nb_addresses_todo')
            ->addSelect('COUNT(IF(buildingStatistics.status = :status_ongoing, buildingStatistics.id, null)) AS nb_addresses_ongoing')
            ->addSelect('COUNT(IF(buildingStatistics.status = :status_completed, buildingStatistics.id, null)) AS nb_addresses_completed')
            ->innerJoin('buildingStatistics.campaign', 'campaign')
            ->andWhere('campaign = :campaign')
            ->setParameters([
                'campaign' => $campaign,
                'status_todo' => BuildingStatusEnum::TODO,
                'status_ongoing' => BuildingStatusEnum::ONGOING,
                'status_completed' => BuildingStatusEnum::COMPLETED,
            ])
            ->getQuery()
            ->getSingleResult()
        ;

        foreach ($kpis as $key => $kpi) {
            $kpis[$key] = \intval($kpi);
        }

        return $kpis;
    }

    public function findByCampaign(Campaign $campaign, int $page, int $limit): PaginatorInterface
    {
        return $this->configurePaginator(
            $this->createQueryBuilder('stats')
                ->addSelect('building', 'campaign', 'address')
                ->innerJoin('stats.building', 'building')
                ->innerJoin('stats.campaign', 'campaign')
                ->innerJoin('building.address', 'address')
                ->where('stats.campaign = :campaign')
                ->setParameter('campaign', $campaign),
            $page,
            $limit
        );
    }
}
