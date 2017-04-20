<?php

namespace AppBundle\Repository;

use AppBundle\Entity\LegislativeCandidate;
use Doctrine\ORM\EntityRepository;

class LegislativeCandidateRepository extends EntityRepository
{
    /**
     * @return LegislativeCandidate[]
     */
    public function findAllForDirectory(): array
    {
        return $this
            ->createQueryBuilder('lc')
            ->addSelect('dz, md')
            ->leftJoin('lc.districtZone', 'dz')
            ->leftJoin('lc.media', 'md')
            ->orderBy('dz.areaCode', 'ASC')
            ->addOrderBy('lc.districtNumber', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
