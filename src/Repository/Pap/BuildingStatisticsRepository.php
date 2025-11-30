<?php

declare(strict_types=1);

namespace App\Repository\Pap;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Entity\Pap\BuildingStatistics;
use App\Entity\Pap\Campaign;
use App\Pap\BuildingStatusEnum;
use App\Repository\PaginatorTrait;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\String\UnicodeString;

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
            ->select('COUNT(IF(buildingStatistics.status = :status_ongoing, buildingStatistics.id, null)) AS nb_addresses_ongoing')
            ->addSelect('COUNT(IF(buildingStatistics.status = :status_completed, buildingStatistics.id, null)) AS nb_addresses_completed')
            ->innerJoin('buildingStatistics.campaign', 'campaign')
            ->andWhere('campaign = :campaign')
            ->andWhere('buildingStatistics.status != :status_todo')
            ->setParameters([
                'campaign' => $campaign,
                'status_todo' => BuildingStatusEnum::TODO,
                'status_ongoing' => BuildingStatusEnum::ONGOING,
                'status_completed' => BuildingStatusEnum::COMPLETED,
            ])
            ->getQuery()
            ->getSingleResult()
        ;

        $kpis = array_map('intval', $kpis);

        $kpis['nb_addresses_todo'] = max(
            $campaign->getNbAddresses() - $kpis['nb_addresses_ongoing'] - $kpis['nb_addresses_completed'],
            0
        );

        return $kpis;
    }

    public function findByCampaign(Campaign $campaign, int $page, int $limit, array $order = []): PaginatorInterface
    {
        $queryBuilder = $this->createQueryBuilder('stats')
            ->addSelect('building', 'campaign', 'address')
            ->innerJoin('stats.building', 'building')
            ->innerJoin('stats.campaign', 'campaign')
            ->innerJoin('building.address', 'address')
            ->where('stats.campaign = :campaign')
            ->setParameter('campaign', $campaign)
        ;

        foreach ($order as $key => $value) {
            $key = implode('.', array_map(function (string $part) {
                return new UnicodeString($part)->camel();
            }, explode('.', $key)));

            $queryBuilder->addOrderBy(!str_contains($key, '.') ? 'stats.'.$key : $key, $value);
        }

        return $this->configurePaginator($queryBuilder, $page, $limit);
    }
}
