<?php

namespace AppBundle\Repository;

use AppBundle\Entity\LegislativeDistrictZone;
use Doctrine\ORM\EntityRepository;

class LegislativeDistrictZoneRepository extends EntityRepository
{
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
            $code = (int) $zone->getAreaCode();

            if ($code < 100) {
                $group = LegislativeDistrictZone::ZONE_FRANCE;
            } elseif ($code >= 971 && $code <= 989) {
                $group = LegislativeDistrictZone::ZONE_DOM_TOM;
            } elseif ($code > 1000) {
                $group = LegislativeDistrictZone::ZONE_FOREIGN;
            } else {
                throw new \RuntimeException(sprintf('Unexpected code "%s" for zone "%s"', $code, $zone));
            }

            $groupedZones[$group][] = $zone;
        }

        return $groupedZones ?? [];
    }
}
