<?php

namespace App\Repository;

use App\Entity\LegislativeDistrictZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LegislativeDistrictZoneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LegislativeDistrictZone::class);
    }

    public function findDistrictZone(string $areaCode): ?LegislativeDistrictZone
    {
        return $this->findOneBy(['areaCode' => $areaCode]);
    }

    /**
     * @return LegislativeDistrictZone[]
     */
    public function findAllGrouped(): array
    {
        $zones = $this
            ->createQueryBuilder('dz')
            ->orderBy('dz.areaCode', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        /** @var LegislativeDistrictZone $zone */
        foreach ($zones as $zone) {
            $groupedZones[$zone->getAreaTypeLabel()][] = $zone;
        }

        return $groupedZones ?? [];
    }
}
