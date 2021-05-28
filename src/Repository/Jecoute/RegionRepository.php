<?php

namespace App\Repository\Jecoute;

use App\Entity\Geo\Zone;
use App\Entity\Jecoute\Region;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RegionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Region::class);
    }

    public function findOneCampaignByZone(Zone $region, Zone $department, string $postalCode): ?Region
    {
        $qb = $this->createQueryBuilder('campaign');

        return $qb
            ->select('campaign')
            ->addSelect('
            CASE 
                WHEN zone.type = :zone_region THEN 1
                WHEN zone.type = :zone_department THEN 2
                ELSE 3
            END AS HIDDEN priority 
            ')
            ->leftJoin('campaign.zone', 'zone')
            ->where($qb->expr()->orX(
                'zone.type = :zone_region AND zone = :region',
                'zone.type = :zone_department AND zone = :department',
                'zone.type = :zone_borough AND zone.postalCode = :postal_code',
            ))
            ->addOrderBy('priority', 'asc')
            ->setParameters([
                'region' => $region,
                'department' => $department,
                'postal_code' => $postalCode,
                'zone_region' => Zone::REGION,
                'zone_department' => Zone::DEPARTMENT,
                'zone_borough' => Zone::BOROUGH,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
