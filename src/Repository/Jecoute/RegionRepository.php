<?php

declare(strict_types=1);

namespace App\Repository\Jecoute;

use App\Address\AddressInterface;
use App\Entity\Geo\Zone;
use App\Entity\Jecoute\Region;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\Jecoute\Region>
 */
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
                WHEN zone.type = :zone_country THEN 1
                WHEN zone.type = :zone_region THEN 2
                WHEN zone.type = :zone_department THEN 3
                ELSE 4
            END AS HIDDEN priority
            ')
            ->leftJoin('campaign.zone', 'zone')
            ->where($qb->expr()->orX(
                'zone.type = :zone_country AND zone.code = :code_france',
                'zone.type = :zone_region AND zone = :region',
                'zone.type = :zone_department AND zone = :department',
                'zone.type = :zone_borough AND zone.postalCode = :postal_code',
            ))
            ->andWhere('campaign.enabled = :enabled')
            ->addOrderBy('priority', 'asc')
            ->setParameters(new ArrayCollection([new Parameter('code_france', AddressInterface::FRANCE), new Parameter('region', $region), new Parameter('department', $department), new Parameter('postal_code', $postalCode), new Parameter('zone_country', Zone::COUNTRY), new Parameter('zone_region', Zone::REGION), new Parameter('zone_department', Zone::DEPARTMENT), new Parameter('zone_borough', Zone::BOROUGH), new Parameter('enabled', true)]))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function hasNationalCampaign(): bool
    {
        return 0 !== $this
            ->createQueryBuilder('campaign')
            ->select('COUNT(DISTINCT(campaign.id))')
            ->innerJoin('campaign.zone', 'zone')
            ->where('zone.type = :zone_country')
            ->andWhere('zone.code = :code_france')
            ->setParameters(new ArrayCollection([new Parameter('zone_country', Zone::COUNTRY), new Parameter('code_france', AddressInterface::FRANCE)]))
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
