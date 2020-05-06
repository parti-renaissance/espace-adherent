<?php

namespace App\Repository;

use App\Entity\ReferentArea;
use Doctrine\ORM\EntityRepository;

class ReferentAreaRepository extends EntityRepository
{
    public function findReferentArea(string $areaCode): ?ReferentArea
    {
        return $this->findOneBy(['areaCode' => $areaCode]);
    }

    /**
     * @return ReferentArea[]
     */
    public function findAllGrouped(): array
    {
        $zones = $this
            ->createQueryBuilder('dz')
            ->orderBy('dz.areaCode', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        /** @var $zone ReferentArea */
        foreach ($zones as $zone) {
            $groupedZones[$zone->getAreaTypeLabel()][] = $zone;
        }

        return $groupedZones ?? [];
    }
}
