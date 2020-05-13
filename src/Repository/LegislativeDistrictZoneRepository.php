<?php

namespace App\Repository;

use App\Entity\LegislativeDistrictZone;
use Doctrine\ORM\EntityRepository;

class LegislativeDistrictZoneRepository extends EntityRepository
{
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

        /** @var $zone LegislativeDistrictZone */
        foreach ($zones as $zone) {
            $groupedZones[$zone->getAreaTypeLabel()][] = $zone;
        }

        return $groupedZones ?? [];
    }
}
